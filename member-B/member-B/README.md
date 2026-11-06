# Thành viên B — Giao dien, client va thuyet trinh

Dùng một repo chung cho ba người. Đọc HUONG_DAN_CHUNG.md trước khi nhập. Không git init trong thư mục gói này.

## Phần việc theo đợt

### Đợt 1 — 7 file

Đọc layout/login/chat và CSS responsive. Rà label, trạng thái và cách escape dữ liệu PHP. Bản ghép chưa chạy đầy đủ ở đợt này.

Commit nhập gợi ý: `chore: import PHP views and stylesheet baseline`

File:

- `public/assets/css/app.css`
- `public/assets/css/fonts.css`
- `public/assets/css/readability.css`
- `public/assets/favicon.svg`
- `resources/views/chat.php`
- `resources/views/layout.php`
- `resources/views/login.php`

### Đợt 2 — 5 file

Đọc api.js/socket.js/chat.js/app.js. Sau lượt 6 kiểm tra hai profile, đổi phòng, reconnect, history merge và desktop/mobile; ghi lỗi và sửa thật nếu phát hiện.

Commit nhập gợi ý: `chore: import browser chat and reconnect client`

File:

- `public/assets/js/api.js`
- `public/assets/js/app.js`
- `public/assets/js/chat.js`
- `public/assets/js/socket.js`
- `tests/manual-acceptance.md`

### Đợt 3 — 31 file

Mở slide/ghi chú, rà thông tin với ứng dụng; tự tập thuyết trình, chụp minh chứng mới và ghi kết quả vào docs/member-reviews/B.md. Giữ phân biệt ảnh/log cũ với lần kiểm tra mới.

Commit nhập gợi ý: `docs: import slides and presentation material`

File:

- `docs/comparison.md`
- `docs/demo-script.md`
- `docs/member-reviews/B.md`
- `docs/qa-questions.md`
- `docs/report-review.md`
- `docs/slides.json`
- `evidence/logs/browser-acceptance.json`
- `evidence/logs/login-initialization.json`
- `evidence/logs/protocol-capture.json`
- `evidence/logs/reference-video.json`
- `evidence/logs/scene-alice.json`
- `evidence/logs/ui-capture.json`
- `evidence/logs/walkthrough.json`
- `evidence/screenshots/EV-00-login.png`
- `evidence/screenshots/EV-02-protocol-chat.png`
- `evidence/screenshots/EV-06-chat-desktop.png`
- `evidence/screenshots/EV-07-chat-mobile.png`
- `submission/recording-kit/recording-guide.md`
- `submission/recording-kit/rehearsal-questions.md`
- `submission/recording-kit/script.md`
- `submission/reference-walkthrough.webm`
- `submission/slides.pdf`
- `submission/slides.pptx`
- `tools/browser-acceptance.js`
- `tools/build-slides.ps1`
- `tools/capture-protocol.js`
- `tools/capture-ui.js`
- `tools/login-initialization.js`
- `tools/run-browser-check.py`
- `tools/verify-video.js`
- `tools/walkthrough.js`

