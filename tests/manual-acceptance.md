# Browser acceptance record

Use two separate profiles: Alice in one browser, Bob in another. Browser automation and direct protocol checks complement the PHP unit tests. Check actual results against `docs/test-cases.md`, and keep failures visible until retested.

- [x] Sign in wrong/valid; validate security failures without leaking details.
- [x] Alice and Bob exchange distinct markers in General without reload; each appears once.
- [x] Bob changes to Engineering; Alice's General messages/typing remain isolated.
- [ ] One account opens a second tab; presence remains account-deduplicated.
- [ ] Type, stop, switch room; typing clears. Observe server TTL independently in integration tests.
- [x] Go offline then online; UI disables send, reconnects/rejoins and merges history without duplicates.
- [x] Select rooms quickly; an old HTTP response cannot replace the current room.
- [x] Send `<script>alert(1)</script>`; it appears as text and no dialog opens.
- [ ] Test Shift+Enter, Enter, Unicode count and over-limit warning.
- [x] At 390x844 and 1440x900, inspect readable controls and no horizontal overflow.
- [x] Logout from a same-session tab; every socket is revoked, the client stops retrying.
- [x] Restart app/WS without deleting the volume; persisted markers remain.
- [ ] Review browser console errors and failed network responses after a fresh session.

Execution evidence must name the browser/viewport and artifact. Screenshots document visible UI; they do not alone prove unauthorized access, rate limiting or database behavior. Use protocol logs for those claims.

Executed browser evidence: `evidence/logs/browser-acceptance.json`, `login-initialization.json`, `ui-capture.json` and `walkthrough.json`; real profile-to-profile chat was also observed. Server-only multi-socket/typing checks are in integration evidence. Unchecked browser-only items are not silently claimed by those server tests.
