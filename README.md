# Relay PHP WebSocket chat

A working multi-room chat demonstration for **503073 Web Programming and Applications**, Topic 9 **WebSockets and Real-Time Communication**. PHP handles HTTP and WebSocket server logic. Browser JavaScript uses the native WebSocket API; no frontend build or Node.js server is required.

## Start on Windows

Install/start Docker Desktop with **Linux containers** and Docker Compose. From PowerShell in this directory:

```powershell
powershell -ExecutionPolicy Bypass -File tools/setup.ps1
```

The script creates `.env` with a cryptographically random session secret, builds the PHP image, installs the locked dependencies, creates the SQLite schema/demo data and starts all three services. It stops if a command fails. First setup requires internet access to download container images and Composer dependencies. Visit **http://localhost:8080**.

| Username | Password | Display name |
| --- | --- | --- |
| `alice` | `ChatDemo!2026` | Alice Nguyen |
| `bob` | `ChatDemo!2026` | Bob Tran |
| `charlie` | `ChatDemo!2026` | Charlie Le |

These are fictional demo accounts, with passwords hashed in SQLite. The application has no admin role. Use separate browsers/profiles or an incognito window to sign in as two different users. Ordinary tabs in one profile share the same cookie/session and identity.

## Manual setup on Linux or macOS

Docker Engine/Desktop and the Compose plugin must be available. Windows with Linux containers is the environment tested during implementation; repeat the clean-setup check on another OS before claiming it is verified there.

```sh
cp .env.example .env
docker compose build
docker compose run --rm app php -r 'echo bin2hex(random_bytes(32)), PHP_EOL;'
```

Copy the generated value into `SESSION_SECRET` in `.env`, then:

```sh
docker compose run --rm app composer install --no-interaction --prefer-dist
docker compose run --rm app php database/seed.php
docker compose up -d
```

Keep `APP_URL=http://localhost:8080` and `ORIGIN_ALLOWLIST=http://localhost:8080` for the default setup. Open that exact address. `http://127.0.0.1:8080` is a different Origin and is intentionally rejected unless explicitly allowed.

## Architecture and configuration

```text
Browser -> Nginx localhost:8080
           /api, pages -> PHP-FPM -> SQLite
           /ws        -> PHP CLI Workerman (1 worker) -> same SQLite
```

Only Nginx is published, bound to `127.0.0.1`. WebSocket port 8081 and PHP-FPM port 9000 remain inside Docker. The named `chat-data` volume contains SQLite and its WAL/SHM files; source code is bind-mounted for development. A native Docker volume avoids Windows bind-mount permission problems for concurrent PHP-FPM/CLI access.

| Setting | Meaning |
| --- | --- |
| `APP_ENV` | `local` by default; used to identify the environment |
| `APP_URL` | Browser origin and cookie security policy; use HTTPS for a TLS deployment |
| `DATABASE_PATH` | `/app/storage/chat.sqlite` inside the shared named volume |
| `SESSION_SECRET` | At least 32 characters, same for both PHP processes; generate randomly |
| `ORIGIN_ALLOWLIST` | Exact comma-separated allowed origins, without a trailing slash |
| `WS_HOST`, `WS_PORT` | Internal listener, defaults `0.0.0.0:8081`; Nginx targets port 8081 |
| `BENCHMARK_ENABLED` | `0` normally; `1` enables the authenticated polling comparison endpoint |

The image contains PHP 8.3, PDO SQLite, mbstring, pcntl/posix and PHPUnit-required XML extensions. Composer locks Workerman and PHPUnit versions in `composer.lock`. The tested runtime/version information is recorded with the test evidence.

## Features and limits

- Login/logout, rotated signed HttpOnly session cookies, CSRF/Origin checks and shared SQLite session revocation.
- Three rooms: General, Engineering and Study group. Each socket joins one room. Messages, presence and typing stay within that room.
- Persist-before-broadcast chat, sender echo and Unicode validation. Content is limited to 500 Unicode **code points**, not grapheme clusters. JSON application messages are limited to 8192 bytes. Workerman bounds fragmented frames; oversized frames can close the socket before a JSON error is possible.
- Account-wide sliding-window limit of 20 accepted chat messages per 60 seconds, shared by all tabs. Failed database writes do not consume a successful-message slot.
- Presence deduplicated by account; typing expires after 3 seconds. Ping/pong removes an unresponsive connection within about 55-56 seconds. Session validity is checked per event, before delivery and every second.
- Automatic reconnect with 1/2/4/8/10-second retry delays, room rejoin and history/event merge by message ID. Buttons show connection state. Uncertain sends are not automatically retried.
- Only the newest 50 messages are restored through the history API. Offline intervals with more than 50 missing messages are **not** fully recovered. WebSocket plus ID deduplication does not provide exactly-once delivery.
- One Workerman process owns in-memory room, typing and rate-limit state. Do not increase its worker count without shared state/pub-sub. SQLite writes are synchronous; this is a teaching demo, not a production capacity claim.
- Text rendering uses `textContent`; SQL input uses bound parameters. No account registration, file upload, private chat or horizontal scaling is implemented.

## Commands and tests

```powershell
docker compose ps
docker compose logs --tail 50 app ws nginx
docker compose run --rm app composer lint
docker compose run --rm app composer test -- --log-junit evidence/logs/phpunit.xml
docker compose run --rm app composer validate --strict
```

PHPUnit uses an isolated in-memory database. It does not modify demo messages. For black-box HTTP/WebSocket tests, use the separate port-8088 stack and its own named volume:

```powershell
python -m pip install -r tools/requirements.txt
docker compose -p relay-test -f tests/compose.yml run --rm app php database/seed.php
docker compose -p relay-test -f tests/compose.yml up -d
docker compose -p relay-test -f tests/compose.yml restart ws
python tools/integration.py
```

Run one integration/benchmark suite at a time. Restarting the **test** WS service clears its in-memory rate limit before repeat runs, without deleting messages. Do not run the benchmark concurrently with integration tests, since they deliberately use the same account-wide limiter. Results are written to `evidence/logs/integration.json`. The runner uses IPv4 loopback for sockets to avoid slow IPv6 fallback on Windows while retaining the configured Origin header.

The test stack enables the authenticated comparison-only API `GET /api/benchmark/rooms/{id}/messages?afterId=N`. It is disabled in the normal application by default. This cursor endpoint belongs to the benchmark harness; it does not change the product's 50-message recovery guarantee.

See `tests/manual-acceptance.md` for browser checks, `docs/test-cases.md` for executed results and `docs/experiment-plan.md` for measurement methodology. Do not label a planned test as passed.

## Stop, restart and preserve data

```powershell
docker compose stop
docker compose start
docker compose restart ws app
docker compose down
```

These commands preserve the named database volume. `docker compose up -d` starts again after `down`. Re-running seed preserves existing accounts/messages and never creates duplicate welcome messages.

To deliberately reset **only the demo data**, first export anything you need, then use the reset command described in `docs/demo-script.md`. Never use `docker compose down -v` casually: it destroys the project's data volume. Test data lives in the separate `relay-test` project's volume.

## Troubleshooting

| Symptom | Check |
| --- | --- |
| Docker pipe/daemon error | Start Docker Desktop, choose Linux containers and wait until `docker info` works |
| Port 8080 in use | Stop the conflicting service or change Nginx's published port **and** `APP_URL`/allowed origins together |
| Missing autoload/dependency | Run `docker compose run --rm app composer install`; do not copy a partial vendor directory |
| 503 response | Read app logs; check secret length, seed/schema and shared volume permissions |
| WS rejected with 403 | Open the exact configured origin, login, check cookie and allowlist; do not put session tokens in the WS URL |
| WS reconnects repeatedly | Read WS/Nginx logs; verify `/ws` Upgrade proxy, Workerman health and session expiration |
| SQLite read-only | Use the named volume; rebuild/recreate after entrypoint changes. Both processes must share the writable directory, including WAL/SHM files |
| No presence after closing a crashed client | Graceful close is immediate; a network loss relies on heartbeat cleanup |
| Rate limit during demonstrations | Wait 60 seconds or restart WS **in the demo/test environment**, explaining that this resets in-memory state |

## HTTPS deployment

The local demo intentionally uses unencrypted HTTP/WS on loopback. Production TLS/WSS has not been claimed as tested. A TLS reverse proxy must present a valid certificate, forward `/ws` with HTTP/1.1 Upgrade headers, allow long-lived connections, set the real `APP_URL=https://...` and exact HTTPS origin, and adapt `connect-src` in the CSP. The client automatically chooses `wss:` on HTTPS pages, and cookies become Secure when `APP_URL` is HTTPS. See `deploy/README.md` for the deployment checklist.

## Project map and deliverables

For the three-member ownership split and staged GitHub handoff, see [docs/github-handoff.md](docs/github-handoff.md). Generate the local member packages with `python tools/build-github-handoff.py`.

`src/Http` implements APIs, `src/Auth` sessions, `src/Repositories` SQL, `src/Realtime` room/event/connection handling, `src/Protocols` a small Workerman adapter for text-only application messages, and `public/assets/js` the browser client. `docs/` holds contracts, report sources, diagrams, experiments, scripts and progress. `evidence/` contains actual execution evidence. `submission/` holds assembled artifacts and their manifest.

Team identities, instructor, class details, official Faculty template and submission deadline must be supplied before a final cover/submission can be certified. The status tracker records these separately from completed implementation. Real team members must record their own English presentation; generated narration cannot substitute for required member participation.
