# Deployment notes

The verified configuration is a single Windows Docker Desktop host using Linux containers and loopback HTTP/WS. The source is portable Docker configuration, but other platforms and public HTTPS remain deployment tasks to verify in their real environment.

For a public server, use a Linux Docker host, restrict inbound access to the intended HTTP/HTTPS ports, configure an actual domain/certificate at a TLS reverse proxy, and keep PHP-FPM and Workerman unexposed. Set the actual HTTPS `APP_URL` and exact `ORIGIN_ALLOWLIST`. Keep the session secret out of source control. Change demo credentials or disable public demo access before exposing the site outside the classroom.

The reverse proxy must forward `/ws` with HTTP/1.1, `Upgrade`, `Connection`, Cookie and Origin. Its idle timeout must exceed the heartbeat period. Update the Nginx CSP for the real `wss://` endpoint. Verify the browser receives 101, frames appear, the certificate is valid, HTTP does not serve protected traffic and `chat_session` is Secure/HttpOnly/SameSite=Lax.

The `chat-data` named volume persists SQLite, WAL and SHM together. Back up with SQLite's backup mechanism or stop both writers before copying; copying a live main database file alone can miss WAL transactions. Restore into a separate test volume first. Monitor HTTP 5xx, WS restarts, disk space and database lock errors without logging secrets or message contents unnecessarily.

The one-worker hub, typing state and rate limiter reset on process restart. Multiple workers require shared presence/rate-limit state, pub/sub fanout and a data layer suited to write concurrency. No production throughput or fault-tolerance guarantee is implied by the 20-connection functional test.
