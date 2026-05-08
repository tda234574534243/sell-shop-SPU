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
        if (r?.data) return res.json(r.data);
      } catch (e) {
        // continue to next endpoint
        console.warn('model-list attempt failed for', url, e?.response?.status || e?.message || e);
      }
    }
    return res.status(502).json({ error: 'could_not_list_models', message: 'All remote model-list endpoints failed. Check API key and network.' });
  } catch (e) {
    console.error('list-models fallback error:', e?.message || e);
    return res.status(500).json({ error: 'internal_error', message: String(e?.message || e) });
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
      const text = response?.text || '';
      return res.json({ reply: text });
    } catch (genErr) {
      console.error('generateContent error:', genErr?.message || genErr);
      // Some SDKs return a descriptive message when the model is not found.
      if ((genErr?.code === 404) || /not found/i.test(String(genErr?.message))) {
        return res.status(400).json({ error: 'model_not_found', message: String(genErr?.message) });
      }
      return res.status(500).json({ error: 'generate_failed', message: String(genErr?.message || genErr) });
    }
  } catch (err) {
    console.error(err?.message || err);
    res.status(500).json({ error: 'internal_error' });
  }
});

app.listen(PORT, () => {
  console.log(`Chatbot server listening on port ${PORT}`);
});
