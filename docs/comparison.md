# Real-time transport comparison

| Attribute | Short polling | Long polling | Server-Sent Events | WebSocket |
| --- | --- | --- | --- | --- |
| Model | Client repeats ordinary HTTP requests | Server holds a request until data or timeout, then client reconnects | Persistent HTTP response carrying server events | Persistent framed channel after negotiation |
| Direction | HTTP request/response; updates found at poll time | Primarily server updates through held responses | Server to browser through EventSource; upstream needs another request/channel | Both endpoints can send independently |
| Update delay | Includes wait until next poll | Usually no fixed polling wait while a request is pending | Event delivered on open response | Event delivered on open connection |
| Idle behavior | Requests continue without changes | Held request consumes a connection; renew on timeout | Open stream, often keepalives | Open socket and optional heartbeat |
| Data | HTTP response formats | HTTP response formats | UTF-8 event stream | Text/binary messages |
| Reconnect | Explicit polling loop retries | Explicit reissue after response/error | EventSource supports reconnect and event IDs when application uses them | Application must implement reconnect/resume semantics |
| Operational concerns | Simple infrastructure; unnecessary repeated traffic at high frequency | Timeouts, held connections and renewal races | Proxy buffering/timeouts and connection limits | Upgrade-aware proxy, stateful connections, application flow control |
| Authentication | Usual HTTP cookie/token rules | Usual HTTP cookie/token rules | Cookie-based origin policy; browser EventSource API constraints | Browser handshake cookie and Origin validation; per-event authorization |
| Suitable examples | Rare status changes, simple dashboards | Fallback when streaming is unavailable | News feeds, progress updates, one-way notifications | Interactive chat, games, bidirectional collaboration |

Relay uses HTTP for login, room discovery and bounded historical queries because they have natural request/response boundaries. Chat, typing and presence benefit from a bidirectional event channel, so WebSocket is a good match. A server-only feed could use SSE with less application reconnect work; a page updated infrequently may reasonably use polling. WebSocket is not automatically the cheapest or fastest choice under every workload.

The included experiment isolates polling versus WS **reception**, keeping the sender, stored messages, authentication and server machine common. Measured values are inserted only after the six trials finish, using `evidence/raw/benchmark-summary.json`. A short-polling interval bounds how often the client checks but does not prove any absolute WebSocket network latency. Local measurements do not establish production scalability.

Primary references: RFC 6455; WHATWG HTML Server-sent events section; MDN WebSocket API and Using server-sent events. Full verified URLs and reading notes are maintained in `references.md`.
