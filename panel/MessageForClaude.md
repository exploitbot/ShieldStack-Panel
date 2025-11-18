# Message for Claude / API Usage Notes

Purpose: quick guidance so Claude knows how to call the Clove API correctly (Claude-native and OpenAI-compatible) and avoid “can’t use as OpenAI API” confusion.

## Base Connection
- Endpoint (Claude-native): `https://clove.shieldstack.dev/v1/messages`
- Endpoint (OpenAI-compatible): `https://clove.shieldstack.dev/v1/chat/completions`
- Auth: `X-API-Key: eric` (you can also use `Authorization: Bearer eric`)
- Content-Type: `application/json`

## Minimal Requests
### Claude-native (/v1/messages)
```bash
curl -X POST https://clove.shieldstack.dev/v1/messages \
  -H "X-API-Key: eric" \
  -H "Content-Type: application/json" \
  -d '{
    "model":"claude-sonnet-4-5-20250929",
    "messages":[{"role":"user","content":"Hello"}],
    "max_tokens":128
  }'
```

### OpenAI-compatible (/v1/chat/completions)
```bash
curl -X POST https://clove.shieldstack.dev/v1/chat/completions \
  -H "Authorization: Bearer eric" \
  -H "Content-Type: application/json" \
  -d '{
    "model":"claude-sonnet-4-5-20250929",
    "messages":[{"role":"user","content":"Hello"}],
    "stream": false
  }'
```

## Streaming
Add `"stream": true` to either endpoint. Responses come as Server-Sent Events (`data: {json}\n\n ... data: [DONE]`). Example:
```bash
curl -N -X POST https://clove.shieldstack.dev/v1/messages \
  -H "X-API-Key: eric" \
  -H "Content-Type: application/json" \
  -d '{
    "model":"claude-sonnet-4-5-20250929",
    "messages":[{"role":"user","content":"Stream a short poem"}],
    "stream": true
  }'
```

## Models (active)
- `claude-sonnet-4-5-20250929` (recommended)
- `claude-opus-4-1-20250805`
- `claude-haiku-4-5-20251001`

## Admin vs User Keys
- Use the admin key only for admin endpoints (`/api/admin/*`).
- Use `eric` for inference endpoints above. Both worked in live tests; prefer `eric` to keep admin key separate.

## Recent Verifications (Nov 18, 2025)
- `X-API-Key: eric` → `/v1/messages` (Claude-native) returned HTTP 200 with valid response.
- `X-API-Key: eric` → `/v1/chat/completions` is also functional (OpenAI-compatible adapter).

## Common pitfalls
- 401/000 errors: usually bad/missing key or network block. The key `eric` is active server-side.
- 405 on `/v1/chat/completions`: happens if request is GET/incorrect headers—ensure POST + JSON body.
- Don’t send admin key to clients; keep it server-side.

## If errors persist
- Check connectivity: `curl -v` from the client environment to see if TLS/proxy is blocking.
- Confirm headers are exactly `X-API-Key: eric` or `Authorization: Bearer eric`, and `Content-Type: application/json`.
