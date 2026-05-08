# Chatbot (sell-shop-SPU)

Lightweight chatbot proxy that connects the frontend chat bubble to a generative model (Google Gemini / Generative Language API). Designed as a small service to keep API keys off the browser and to provide product context (product list) to the model.

## Goals
- Provide product-aware recommendations and short conversational help for shoppers.
- Keep the provider API key on the server (no exposure in client JS).
- Keep integration simple so it can be extended (history, structured replies, analytics).

## Architecture

- Client (browser): `template/chatBubble.php` — UI, sends user message to local Node proxy `/api/chat` and renders replies. UI improvements: avatars, typing indicator, basic rendering of lists and links.
- Node proxy: `chatbot/server.js` — Express server that:
  - receives POST `/api/chat` with `{ message }` JSON
  - fetches product context from `chatbot/get_products.php`
  - builds a prompt (system + product list + user message)
  - calls the configured generative model via the `@google/genai` SDK (or provider REST fallback)
  - returns `{ reply }` JSON to the client
- PHP endpoint: `chatbot/get_products.php` — returns a JSON array of product summaries (MaSP, TenSP, MoTa) for prompt context.

This keeps responsibilities separated: PHP accesses DB, Node handles model calls and secrets.

## Files
- `server.js` — main Node proxy
- `.env` — environment variables (not committed; a template is included)
- `get_products.php` — product-list endpoint
- `template/chatBubble.php` — UI included into `template/footer.php`

## Theory / Prompting
- System prompt includes concise instructions: be a product recommendation assistant, use supplied product list, answer concisely and mention product names and reasons.
- Product context is limited (controlled by `MAX_PRODUCTS_CONTEXT`) to avoid exceeding token limits.
- Server is authoritative for model choice, temperature and other generation settings — do not trust client-provided settings.

## Dependencies
- Node (recommended v18+)
- npm packages (in `package.json`): `@google/genai`, `express`, `axios`, `cors`, `dotenv`

## Environment variables (.env)
- `GEMINI_API_KEY` — API key for the provider (required)
- `GEMINI_MODEL` — model id to use (e.g. `models/gemini-2.5-flash`)
- `PORT` — Node server port (default `3000`)
- `PRODUCTS_API_URL` — full URL to `get_products.php`
- `NODE_ENV` — `development` or `production`
- `MAX_PRODUCTS_CONTEXT` — max number of products to include in prompt (default 30)

There is a `.env` file in the folder with placeholders — set `GEMINI_API_KEY` and `GEMINI_MODEL`.

## Install & Run (development)

1. From the `chatbot/` folder:
```bash
npm install
```
2. Create or update `.env` with your key and model. Example:
```text
GEMINI_API_KEY=YOUR_KEY_HERE
GEMINI_MODEL=models/gemini-2.5-flash
PORT=3000
PRODUCTS_API_URL=http://localhost/sell-shop-SPU/chatbot/get_products.php
NODE_ENV=development
# Optional: limit prompt size or product count
MAX_PRODUCTS_CONTEXT=30
```
3. Start:
```bash
node server.js
```
4. Open your PHP site normally (e.g., http://localhost/sell-shop-SPU). The chat bubble is included in the footer.

## Quick tests
- List models (REST fallback): `curl http://localhost:3000/api/list-models`
- Send a test chat: `curl -X POST http://localhost:3000/api/chat -H "Content-Type: application/json" -d '{"message":"gợi ý áo thun nam"}'`

## Security & Operational Notes
- Do NOT commit `.env` or API keys to source control.
- Restrict CORS to your site in production (the current server allows all origins for dev convenience).
- Rate-limit requests, add authentication if exposing beyond localhost.
- Consider using a service account or server-side IAM credentials rather than API keys where supported.
- Monitor usage and cost on your model provider dashboard.

## Future improvements (suggestions)
- Add session/history per user (store in DB or Redis) to provide context-aware replies.
- Structure model output (JSON) so client can render product cards, prices, and direct links.
- Add streaming responses to the UI for faster perceived responses.
- Add server-side caching of product lists and prompt templates.
- Improve prompt engineering (few-shot examples, dynamic temperature per task).
- Add authentication for the `/api/chat` endpoint and rate limiting.

## Troubleshooting
- If `generateContent` returns model-not-found: call `/api/list-models` to see supported model ids, then set `GEMINI_MODEL` accordingly.
- If model calls fail with 401/403: check `GEMINI_API_KEY` and provider permissions.
- If responses are cut off: reduce product count or use a model with larger token limit.

---
If you want, I can: add structured JSON output from the model for richer UI, persist chat history, or add rate-limiting and auth. Which would you like next?
