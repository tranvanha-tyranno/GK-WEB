# Bản đồ ôn tập lý thuyết

Chương 2 của `report.md` giải thích các nguyên lý. Dùng bảng này nối lý thuyết với mã khi chuẩn bị trình bày.

| Khái niệm | Mục báo cáo | Liên hệ mã nguồn | Nguồn chính |
| --- | --- | --- | --- |
| Yêu cầu/phản hồi và sự kiện độc lập | 2.1 | HTTP controller và WS gateway | RFC 6455, MDN |
| Upgrade HTTP/1.1, trạng thái 101 | 2.2 | Nginx `/ws`, phiên, Origin | RFC 6455, Nginx |
| Khung, JSON, phân mảnh, khung điều khiển | 2.3 | Workerman và bộ tiếp hợp văn bản | RFC 6455, Workerman 5.2.3 |
| Masking và mã hóa | 2.3, 2.6 | ws local, hướng dẫn WSS | RFC 6455, OWASP |
| Sự kiện, đẩy tin, phát theo phòng | 2.4 | EventHandler, ChatService, RoomHub | RFC 6455, contracts.md |
| Vòng đời và khôi phục có giới hạn | 2.5 | socket.js, chat.js, gộp ID | MDN, kiểm tra ứng dụng |
| Xác thực, Origin, CSRF, thu hồi | 2.6 | Dịch vụ phiên, gateway, middleware | OWASP, PHP |
| Kiểm tra dữ liệu, SQL/XSS, giới hạn | 2.6 | Validator, repository, textContent | PHP PDO, OWASP |
| Vòng lặp, ghi đồng bộ, một worker | 2.7 | Workerman, RAM, SQLite WAL | Workerman, SQLite |
| Polling, polling dài, SSE, WS | 2.8, 4.8–4.9 | Thí nghiệm đường nhận | WHATWG, RFC, dữ liệu đo |

Phân biệt handshake với quyền vĩnh viễn, masking với TLS, một tài khoản với một socket, gộp ID với bảo đảm đúng một lần, phát tin đúng với thông lượng tối đa. Các câu hỏi tại `qa-questions.md` giúp luyện giải thích những khác biệt này.
