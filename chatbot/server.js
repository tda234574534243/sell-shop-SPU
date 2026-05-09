// Simple Express server that proxies chat requests to Google Gemini via @google/genai
const express = require('express');
const cors = require('cors');
const axios = require('axios');
const dotenv = require('dotenv');
const { GoogleGenAI } = require('@google/genai');

dotenv.config();

const PORT = process.env.PORT || 3000;
const PRODUCTS_API = process.env.PRODUCTS_API_URL || 'http://localhost/sell-shop-SPU/chatbot/get_products.php';
const GEMINI_KEY = process.env.GEMINI_API_KEY;

if (!GEMINI_KEY) {
  console.warn('Warning: GEMINI_API_KEY not set. Set GEMINI_API_KEY in environment.');
}

const ai = new GoogleGenAI({ apiKey: GEMINI_KEY });

const app = express();
app.use(cors());
app.use(express.json());

// MongoDB setup (optional). Set MONGODB_URI in environment or .env
const { MongoClient } = require('mongodb');
const MONGODB_URI = process.env.MONGODB_URI || process.env.MONGO_URI;
let mongoClient = null;
let chatCollection = null;
async function initMongo() {
  if (!MONGODB_URI) return;
  try {
    // Newer mongodb drivers ignore these legacy options; pass URI only.
    mongoClient = new MongoClient(MONGODB_URI);
    await mongoClient.connect();
    const dbName = process.env.MONGODB_DB || 'chatbot_db';
    const db = mongoClient.db(dbName);
    chatCollection = db.collection('chat_history');
    // create indexes
    await chatCollection.createIndex({ session_id: 1 });
    await chatCollection.createIndex({ user_id: 1 });
    await chatCollection.createIndex({ created_at: -1 });
    console.log('Connected to MongoDB and ready to save chat history.');
  } catch (e) {
    console.warn('Could not initialize MongoDB:', (e && e.message) || e);
    mongoClient = null;
    chatCollection = null;
  }
}

// helper to save messages
async function saveChatMessage({ session_id, user_id = null, role, message, metadata = {} }) {
  if (!chatCollection) return;
  try {
    const doc = {
      session_id: session_id || require('crypto').randomBytes(16).toString('hex'),
      user_id: user_id || null,
      role: role || 'user',
      message: message || '',
      metadata: metadata || null,
      created_at: new Date()
    };
    await chatCollection.insertOne(doc);
    return doc;
  } catch (e) {
    console.warn('Failed to save chat message:', (e && e.message) || e);
  }
}

app.get('/', (req, res) => res.json({ status: 'ok' }));

// List available models (useful when a configured model isn't supported)
// Provide a REST-based fallback to list models using the public Generative Language API.
// This attempts to call the provider's models list endpoint with an API key.
app.get('/api/list-models', async (req, res) => {
  if (!GEMINI_KEY) return res.status(400).json({ error: 'api_key_missing', message: 'Set GEMINI_API_KEY in environment or .env' });
  try {
    // Try v1 endpoint first, then v1beta
    const endpoints = [
      `https://generativelanguage.googleapis.com/v1/models?key=${encodeURIComponent(GEMINI_KEY)}`,
      `https://generativelanguage.googleapis.com/v1beta/models?key=${encodeURIComponent(GEMINI_KEY)}`
    ];
    for (const url of endpoints) {
      try {
        const r = await axios.get(url, { timeout: 5000 });
        if (r && r.data) return res.json(r.data);
      } catch (e) {
        // continue to next endpoint
        console.warn('model-list attempt failed for', url, (e && e.response && e.response.status) || (e && e.message) || e);
      }
    }
    return res.status(502).json({ error: 'could_not_list_models', message: 'All remote model-list endpoints failed. Check API key and network.' });
  } catch (e) {
    console.error('list-models fallback error:', (e && e.message) || e);
    return res.status(500).json({ error: 'internal_error', message: String((e && e.message) || e) });
  }
});

app.post('/api/chat', async (req, res) => {
  try {
    const { message } = req.body;
    if (!message || message.trim().length === 0) return res.status(400).json({ error: 'message required' });

    // fetch product list from PHP endpoint
    let products = [];
    try {
      const r = await axios.get(PRODUCTS_API);
      products = Array.isArray(r.data) ? r.data : [];
    } catch (e) {
      console.warn('Could not fetch products list:', e.message);
    }

    // Build context with product name + description (limit length)
    const examples = products.slice(0, 50).map(p => `- ${p.TenSP}: ${p.MoTa || ''}`).join('\n');

    const system = `You are a helpful product recommendation assistant. Use the provided product list to recommend suitable items based on the user's request. Reply concisely and include product names and reasons.`;
    const prompt = `SYSTEM:\n${system}\n\nPRODUCTS:\n${examples}\n\nUSER:\n${message}\n\nRESPONSE:`;

    const modelName = process.env.GEMINI_MODEL || 'gemini-3.5-mini';

    try {
      const response = await ai.models.generateContent({ model: modelName, contents: prompt });
      const text = (response && response.text) || '';

      // Save user message and bot reply to MongoDB if available
      try {
        const session_id = req.body.session_id || null;
        const user_id = req.body.user_id || null;
        await saveChatMessage({ session_id, user_id, role: 'user', message, metadata: { source: 'http' } });
        await saveChatMessage({ session_id, user_id, role: 'bot', message: text, metadata: { model: modelName } });
      } catch (saveErr) {
        console.warn('chat save error:', (saveErr && saveErr.message) || saveErr);
      }

      return res.json({ reply: text });
    } catch (genErr) {
      console.error('generateContent error:', (genErr && genErr.message) || genErr);
      // Some SDKs return a descriptive message when the model is not found.
      if ((genErr && genErr.code === 404) || /not found/i.test(String((genErr && genErr.message) || genErr))) {
        return res.status(400).json({ error: 'model_not_found', message: String((genErr && genErr.message) || genErr) });
      }
      return res.status(500).json({ error: 'generate_failed', message: String((genErr && genErr.message) || genErr) });
    }
  } catch (err) {
    console.error((err && err.message) || err);
    res.status(500).json({ error: 'internal_error' });
  }
});

// History endpoint: fetch messages by user_id or session_id
app.get('/api/history', async (req, res) => {
  try {
    if (!chatCollection) return res.status(503).json({ error: 'mongo_unavailable' });
    const { user_id, session_id, limit } = req.query;
    const q = {};
    if (user_id) q.user_id = isNaN(user_id) ? user_id : parseInt(user_id);
    if (session_id) q.session_id = session_id;
    if (!user_id && !session_id) return res.status(400).json({ error: 'missing_param', message: 'Provide user_id or session_id' });
    const lim = Math.min(parseInt(limit) || 200, 2000);
    const docs = await chatCollection.find(q).sort({ created_at: 1 }).limit(lim).toArray();
    return res.json({ messages: docs });
  } catch (e) {
    console.error('history fetch error:', (e && e.message) || e);
    return res.status(500).json({ error: 'internal_error', message: String((e && e.message) || e) });
  }
});

app.listen(PORT, () => {
  // initialize mongo after server starts
  initMongo().catch(e => console.warn('initMongo failed:', (e && e.message) || e));
  console.log(`Chatbot server listening on port ${PORT}`);
});
