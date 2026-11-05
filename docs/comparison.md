# So sánh các cách giao tiếp thời gian thực

| Thuộc tính | Polling ngắn | Polling dài | Server-Sent Events (SSE) | WebSocket |
| --- | --- | --- | --- | --- |
| Mô hình | Lặp yêu cầu HTTP theo chu kỳ | Giữ yêu cầu đến khi có dữ liệu hoặc hết hạn rồi mở lại | Phản hồi HTTP liên tục mang sự kiện | Kênh chia khung liên tục sau thương lượng |
| Chiều truyền | Yêu cầu/phản hồi, tìm cập nhật lúc hỏi | Chủ yếu nhận cập nhật qua yêu cầu đang giữ | Máy chủ → trình duyệt; gửi lên cần kênh khác | Hai đầu có thể chủ động gửi |
| Độ trễ cập nhật | Có thời gian chờ lượt hỏi kế tiếp | Không có chu kỳ chờ cố định khi đang giữ yêu cầu | Đẩy trên luồng đang mở | Đẩy trên kết nối đang mở |
| Khi nhàn rỗi | Vẫn gửi yêu cầu dù không đổi | Giữ kết nối, mở lại khi hết hạn | Luồng mở, thường có nhịp giữ kết nối | Socket mở, có thể có heartbeat |
| Dữ liệu | Các định dạng HTTP | Các định dạng HTTP | Luồng sự kiện UTF-8 | Tin văn bản hoặc nhị phân |
| Kết nối lại | Vòng lặp tự thử lại | Gửi lại sau phản hồi/lỗi | EventSource hỗ trợ nối lại và ID nếu ứng dụng dùng | Ứng dụng tự xây dựng nối lại/khôi phục |
| Vận hành | Đơn giản, nhiều yêu cầu khi tần suất cao | Timeout, nhiều kết nối chờ, tranh chấp lúc mở lại | Buffer của proxy, timeout và giới hạn kết nối | Proxy hỗ trợ Upgrade, trạng thái kết nối, kiểm soát luồng |
| Xác thực | Quy tắc cookie/token HTTP | Quy tắc cookie/token HTTP | Chính sách cookie/Origin, giới hạn API EventSource | Cookie ở handshake, kiểm tra Origin và quyền từng sự kiện |
| Phù hợp | Trạng thái đổi ít, bảng tin đơn giản | Dự phòng khi không có streaming | Thông báo một chiều, tiến độ, bảng tin | Chat, trò chơi, cộng tác hai chiều |

Relay giữ HTTP cho đăng nhập, danh sách phòng và lịch sử giới hạn vì các tác vụ có ranh giới yêu cầu/phản hồi rõ. Chat, đang nhập và trực tuyến dùng kênh hai chiều. SSE cũng phù hợp nếu chỉ nhận cập nhật; gửi HTTP kết hợp nhận SSE vẫn là thiết kế hợp lệ. Không có phương thức luôn nhanh nhất hoặc ít tốn nhất cho mọi tải.

Thí nghiệm so sánh **đường nhận tin** polling và WS, giữ chung người gửi, bản ghi, xác thực và máy chủ. Sáu lần chạy hoàn chỉnh nằm tại `evidence/raw/benchmark-summary.json`. Chu kỳ hỏi một giây ảnh hưởng thời gian chờ; kết quả local không phải độ trễ mạng thuần hoặc khả năng phục vụ production.

Nguồn nguyên lý: RFC 6455, WHATWG SSE, MDN WebSocket. Đường dẫn và phạm vi dùng tại `references.md`; số đo của ứng dụng phải dẫn từ log thực nghiệm.
