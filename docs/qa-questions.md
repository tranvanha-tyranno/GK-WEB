# English rehearsal questions

## Role A

**Why keep HTTP when WebSocket exists?** Login and historical queries have bounded request/response semantics. WebSocket handles independent live events; choosing it does not invalidate HTTP.

**Does 101 authenticate a user forever?** No. The HTTP/1.1 status confirms protocol upgrade. The gateway separately checks the session, and active connections remain subject to revocation and event/room authorization.

**Is masking encryption?** No. Client frame masking is a protocol requirement; TLS is needed for encrypted WSS communication.

**How do PHP-FPM and Workerman share a login?** A common signed cookie and session service resolve records in shared SQLite; each process opens its own PDO connection.

## Role B

**Why not SSE?** SSE is useful for server-only streams. Relay has independent upstream/downstream chat and typing events, making a bidirectional channel convenient. HTTP sending plus SSE receiving is still a valid alternative design.

**Why can history and live events overlap?** A message may be stored while a snapshot request is in progress. Both paths use a stored ID, so a map merges the duplicate representation. A generation/room guard rejects an old room's delayed response.

**Does reconnect guarantee every missed message returns?** No. Only the newest 50 are restored, and the application has no durable replay cursor or exactly-once protocol.

**Why not resend a message automatically?** A disconnect can occur after storage but before the sender sees the echo. Retrying without an idempotency key could create another stored record.

## Role C

**Why save before broadcast?** Otherwise browsers can see a message that fails to persist. The failure-injection unit test verifies no broadcast and no successful-message budget consumption after a failed insert.

**Why exactly one worker?** Room and rate state live in that worker's memory. More workers need shared state and pub/sub; changing a number alone would split the room view and limits.

**What does the concurrency test prove?** Ten messages reached all joined receivers at 2/10/20 sockets. It is functional fanout, not a production throughput or unique-user capacity benchmark.

**What do the latency values measure?** One Python monotonic clock observes send to receive across validation/storage/transport/scheduling; polling also includes its next-request wait. The small local runs and shared host do not establish general network latency or production performance.

**How does SQLite affect the event loop?** Synchronous writes can block callbacks under contention. WAL helps read/write coexistence but does not remove the single-writer tradeoff or justify large-scale claims.

## Every member

Trace one requirement to its source module, test result, report section and live demonstration. Explain one defect or harness problem that was actually found and how it was retested. Name one limitation and a concrete extension without pretending it is already implemented.
