# Tài liệu tham khảo gốc đã đối chiếu



Các nguồn được lấy từ đơn vị xuất bản ngày 22/10/2026 (Asia/Saigon). Trạng thái tại `evidence/logs/reference-check.json`; bản HTML lưu tạm phục vụ nghiên cứu. Trích dẫn [1]–[9] có thể chỉnh theo mẫu Khoa. Nguồn nguyên lý không chứng minh số đo của ứng dụng.



1. IETF.**RFC 6455 The WebSocket Protocol**. https://www.rfc-editor.org/rfc/rfc6455.html . Bắt tay (§4), khung/masking/điều khiển (§5), đóng (§7), an toàn (§10); dùng cho nguyên lý, không tự viết bộ phân tích khung.

2. WHATWG.**HTML Living Standard Server-sent events**. https://html.spec.whatwg.org/multipage/server-sent-events.html . EventSource, luồng sự kiện, kết nối lại; dùng so sánh kênh một chiều SSE.

3. Mozilla MDN.**WebSocket API**. https://developer.mozilla.org/en-US/docs/Web/API/WebSocket . Trạng thái, gửi/nhận, hạn chế API; phân biệt truyền và tin cậy ứng dụng.

4. OWASP.**WebSocket Security Cheat Sheet**. https://cheatsheetseries.owasp.org/cheatsheets/WebSocket_Security_Cheat_Sheet.html . Xác thực, Origin, kiểm dữ liệu, giới hạn, log; không phải chứng nhận demo an toàn production.

5. Workerman.**About WebSocket**. https://manual.workerman.net/doc/en/appendices/about-websocket.html . Callback/cách dùng thư viện; đã đối chiếu Workerman 5.2.3 trong vendor, khóa bằng Composer.

6. PHP Manual.**PDO prepare**. https://www.php.net/manual/en/pdo.prepare.php . Prepared statement/tham số ràng buộc; dùng giải thích repository và SQL.

7. PHP Manual.**password_hash**. https://www.php.net/manual/en/function.password-hash.php . Băm một chiều; dùng giải thích seed và đăng nhập.

8. SQLite.**Write-Ahead Logging**. https://sqlite.org/wal.html . WAL/SHM, đọc/ghi và hệ tệp; dùng cho volume chung và đồng thời.

9. Nginx.**WebSocket proxying**. https://nginx.org/en/docs/http/websocket.html . Chuyển Upgrade và timeout; dùng cấu hình proxy.



Yêu cầu môn học lấy từ PDF người dùng cung cấp: 11 trang, chủ đề 9 trang 5–6, yêu cầu 6–9, thang điểm 9–10, chính sách nhóm trang 11. Kế hoạch DOCX là ngữ cảnh phụ, không thay PDF gốc. Bản tiếng Việt được làm theo yêu cầu ngày 28/10/2026; quy định ngôn ngữ thuyết trình trong đề gốc cần đối chiếu trước khi nộp.
