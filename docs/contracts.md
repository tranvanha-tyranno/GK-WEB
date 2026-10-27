# Shared application contract

All IDs are JSON integers. All API responses are `{ "data": ... }` or `{ "error": { "code": "...", "message": "..." } }`. Protected HTTP endpoints return 401 without a logged-in session. Mutating requests require exact allowed Origin and `X-CSRF-Token` from `GET /api/auth/me`.

`User = {id, username, displayName}`. `Message = {id, roomId, user: User, content, createdAt}`. Timestamps are UTC ISO 8601 and localized by the browser. Secrets, password hashes and login cookie values never appear in API JSON.

| Method | Path | Request | `data` |
| --- | --- | --- | --- |
| GET | `/api/health` | none | `{status:"ok"}` |
| GET | `/api/auth/me` | optional cookie | `{user:User|null,csrfToken:string}`; creates anonymous session when needed |
| POST | `/api/auth/login` | JSON `{username,password}` + Origin/CSRF | `{user:User,csrfToken:string}`; new signed cookie; old session revoked |
| POST | `/api/auth/logout` | Origin/CSRF, active login | `{ok:true}`; revoke before cookie removal |
| GET | `/api/rooms` | login | `[{id,name}]` |
| GET | `/api/rooms/{id}/messages?limit=50` | login; limit integer 1..50 | newest messages, returned in ascending ID order |
| GET | `/api/benchmark/rooms/{id}/messages?afterId=N` | login; benchmark mode enabled | messages after cursor, ascending; up to 1000 per poll; comparison harness only |

Bad input uses 400, unauthenticated access 401, failed Origin/CSRF 403, unknown room/endpoint 404, oversized HTTP body 413 and unavailable service 503. WebSocket rate limiting uses `system:error`, because a post-upgrade message is not an HTTP response. Unknown login always receives a generic credential error. The polling API is disabled in the normal environment by default.

WebSocket `/ws` requires the same signed cookie and allowed Origin before upgrade. Each socket belongs to one room. Envelope: `{type:string,payload:object}`. Unknown fields and non-object/binary messages are rejected.

| Direction | Type | Payload |
| --- | --- | --- |
| C -> S | `room:join` | `{roomId:int}` |
| S -> C | `room:joined` | `{roomId:int}` |
| C -> S | `chat:send` | `{roomId:int,content:string}` |
| S -> C | `chat:new` | Message; includes sender echo after persistence |
| C -> S | `typing:set` | `{roomId:int,isTyping:bool}` |
| S -> C | `room:presence` | `{roomId:int,users:User[]}` full account-deduplicated snapshot |
| S -> C | `room:typing` | `{roomId:int,users:User[]}` full snapshot, TTL 3s |
| S -> C | `system:error` | `{code:string,message:string}` |

Errors include `INVALID_PAYLOAD`, `ROOM_NOT_FOUND`, `NOT_IN_ROOM`, `MESSAGE_TOO_LONG`, `PAYLOAD_TOO_LARGE`, `RATE_LIMITED`, `INTERNAL_ERROR`. Invalid/expired/revoked session prevents upgrade or destroys an established connection. Frame-level violations may close the connection without an application error event. Room membership is checked for every chat/typing event, and sender identity always comes from the session.

Content is Unicode-trimmed, non-empty, at most 500 code points; JSON is at most 8192 UTF-8 bytes. The library frame limit allows up to 14 bytes of frame header on top of that, and reassembled JSON is checked again. 20 accepted messages per account in a sliding 60-second window, across all sockets. Heartbeat pings every 20s, closes a missing pong after roughly 55-56s. Session checks run before events/delivery and every second.

On join, the server sets membership then sends `room:joined`. The browser buffers incoming messages in an ID-keyed map while loading history, merges the snapshot and ignores stale responses after switching rooms. History is limited to 50; this does not guarantee recovery of every offline message. A pending send is never automatically resent.

PDO repositories own bound SQL. `latest(roomId,limit=50)`, `create(userId,roomId,content)` and comparison-only `after(roomId,afterId)` return the exact Message shape. `SessionService` signs cookies, stores only token hashes, resolves anonymous/authenticated sessions, rotates after login and supports revocation shared across PHP-FPM and Workerman. Each process opens its own PDO connection after worker startup.
