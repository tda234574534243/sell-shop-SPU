Instructions to enable MongoDB chat history

1) Install dependencies for the chatbot server (in chatbot/):

   npm install mongodb axios express cors dotenv @google/genai

2) Create a `.env` file in `chatbot/` with these variables (replace with your URI):
```
   MONGODB_URI=mongodb+srv********
   MONGODB_DB=chatbot_db
   GEMINI_API_KEY=your_gemini_key_here
   PORT=3000
```
3) Start the server from `chatbot/`:

   node server.js

4) The server will save chat messages into the `chat_history` collection in the configured database. Each incoming `/api/chat` request will save a `user` record and the `bot` reply record. You can pass `session_id` and `user_id` in the POST body to associate messages with sessions/users.

Notes:
- Make sure your MongoDB user has write permissions for the database specified by `MONGODB_DB`.
- If you prefer PHP-side saving, I can add `chatbot/save_message.php` and a PHP model `model/m_chat.php` instead.
