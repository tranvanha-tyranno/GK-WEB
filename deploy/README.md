# Hướng dẫn triển khai

Cấu hình đã kiểm chứng dùng Windows Docker Desktop, Linux containers, HTTP/WS loopback. Cấu hình Docker có thể chuyển môi trường; hệ điều hành khác và HTTPS công khai cần kiểm tra tại môi trường thực.

Trên máy chủ công khai, dùng Docker Linux, chỉ mở cổng HTTP/HTTPS cần thiết, đặt tên miền/chứng chỉ thật tại proxy TLS và giữ PHP-FPM/Workerman trong mạng nội bộ. Đặt `APP_URL` HTTPS thật và `ORIGIN_ALLOWLIST` chính xác. Không đưa khóa phiên vào kho mã. Đổi mật khẩu mẫu hoặc giới hạn truy cập demo trước khi mở ra ngoài lớp.

Proxy phải chuyển `/ws` với HTTP/1.1, `Upgrade`, `Connection`, Cookie và Origin. Timeout nhàn rỗi phải dài hơn chu kỳ heartbeat. Cập nhật CSP cho địa chỉ `wss://` thật. Kiểm tra 101 trong trình duyệt, các khung truyền, chứng chỉ hợp lệ và cookie `chat_session` có Secure/HttpOnly/SameSite=Lax. Không phục vụ dữ liệu được bảo vệ qua HTTP công khai.

Volume `chat-data` giữ SQLite, WAL, SHM cùng nhau. Sao lưu bằng cơ chế backup SQLite hoặc dừng cả hai tiến trình ghi trước khi chép. Chỉ chép tệp DB chính lúc đang chạy có thể bỏ sót giao dịch WAL. Thử phục hồi vào volume test riêng trước. Theo dõi 5xx, WS khởi động lại, ổ đĩa và lỗi khóa DB; tránh ghi bí mật hoặc toàn bộ hội thoại vào log không cần thiết.

Trạng thái phòng, nhập và bộ đếm nằm trong một worker và mất khi khởi động lại. Muốn nhiều worker cần trạng thái chung, pub/sub và lớp dữ liệu phù hợp tải ghi. Kiểm tra 20 kết nối không phải bảo đảm thông lượng hoặc chịu lỗi production.
