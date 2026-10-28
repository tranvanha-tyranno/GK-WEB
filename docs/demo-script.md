# English presentation and recording kit

The editable 12-slide deck includes English speaker notes for A/B/C. A clean transcript is generated in `submission/recording-kit/script.md`. Names/IDs/class/instructor remain blank by the user's instruction. Each real member must speak in the final video; no synthetic attendance, student voice or completed recording is claimed.

## Target recording structure

| Time target | Speaker role | Action |
| --- | --- | --- |
| 00:00-01:00 | A | Introduce topic, scope and actual team |
| 01:00-03:00 | A | Explain HTTP/WS, 101, identity/security and run command |
| 03:00-04:30 | C | Architecture, durable/transient state and persist-before-broadcast |
| 04:30-07:30 | B | Two separate profiles, bidirectional General chat, room separation, presence and typing |
| 07:30-09:00 | B | Offline/online recovery, phone layout, literal script text |
| 09:00-11:30 | C | Persisted marker after restart, validation/rate limit, code explanation and test results |
| 11:30-12:30 | C | Six-run comparison, method and meaningful limitations |
| 12:30-13:00 | B | User experience and limits, not a production capacity claim |
| 13:00-14:00 | A | Future work, source/README/evidence locations and conclusion |

These are rehearsal targets. Replace them with actual video timestamps after recording. Slide notes are a spoken-text base; allow pauses for actual operations. Do not rush every paragraph to force an exact duration. The submitted video must be no more than 20 minutes.

## Before recording

1. Start the app with `powershell -ExecutionPolicy Bypass -File tools/setup.ps1` and verify `http://localhost:8080/api/health`.
2. Use one browser profile for Alice and another for Bob. Credentials for both are in the README. Ordinary tabs share cookies and demonstrate one-account/multiple-socket behavior instead.
3. If a clean classroom dataset is needed, deliberately reset **only the local demo** using the commands below. This deletes demo conversations and sessions. It is not a normal startup step.
4. Increase terminal/browser font, use a readable resolution, hide real notifications and secret files. Record a 30-second audio sample from every speaker.
5. Open the deck, application, source path, test summaries and the six-run chart. Show short code paths that explain validation/persistence, rather than scrolling through every file.

```powershell
docker compose run --rm app php bin/reset-demo.php --confirm=RESET_DEMO
docker compose restart ws
```

## Live actions and explanatory wording

For handshake, open browser DevTools Network and the WS request. Point to the HTTP/1.1 101 response and then to JSON event names. Hide cookies/request secrets. Say: “The connection upgrades once, but authorization continues for each event and delivery.” A sanitized CDP capture is supplied at `evidence/logs/protocol-capture.json` if a static support artifact is useful; show the actual browser when practical.

For chat, send a distinct marker from Alice, observe Bob's copy and reply from Bob. Say: “The server stores the record first. Both clients receive the same database message ID.” Move Bob to Engineering and send a General message from Alice. Explain that room-scoped chat, presence and typing must not leak.

For multi-tab presence, open a second ordinary tab in Alice's profile. One online account should remain one person. Close one tab, leaving the other. For typing, type then stop; explain the three-second server TTL. Demonstrate a network interruption and the disabled composer, restore network, then show rejoin/history without duplicated IDs. Explain that only 50 recent messages are restored and uncertain sends are not repeated automatically.

For persistence, send a marker, run `docker compose restart ws app`, reload/rejoin if necessary and locate the marker again. Use the recorded runtime check to explain the result rather than deleting the volume. The no-pong check takes about 56 seconds; show the recorded log and method in a concise section instead of making the audience wait silently.

For validation and rate limiting, show the test output and one controlled invalid payload or account-wide rejection. Do not flood the main demo unintentionally before other scenes. The integration suite runs separately on port 8088; restarting its WS process clears its test limiter. For the comparison, quote the actual local ranges and explain the one-second polling interval, small sample, host interference and excluded wire overhead.

## Recording and export checklist

- Record with Zoom/Meet/OBS or another tool that captures each actual member's English explanation and clear audio.
- Keep slide transitions and live actions legible. Both main app interactions and explanatory diagrams should be visible.
- Export MP4 using a widely supported codec and open the **final exported file** from beginning to end. Verify each person's voice and any screen-sharing changes.
- Set the final file at `submission/demo.mp4` only when it meets the course's sound/participation requirements. A silent browser reference is supplementary and must not be renamed as a compliant final video.
- Update `docs/rubric-evidence.md` with real timestamps and `submission/manifest.md` with file/duration/check results. Record the real presentation work in the contribution log.
- Check the deadline and upload fields on the course system yourself; no official submission has been performed by Codex.
