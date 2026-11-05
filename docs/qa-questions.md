# Câu hỏi luyện thuyết trình

## Vai trò A

**Vì sao vẫn dùng HTTP?** Đăng nhập và lấy lịch sử có yêu cầu/phản hồi hữu hạn. WebSocket xử lý sự kiện trực tiếp độc lập; hai cơ chế bổ sung cho nhau.

**HTTP 101 có xác thực mãi mãi không?** Không. Đây là xác nhận nâng cấp giao thức HTTP/1.1. Gateway kiểm tra phiên riêng; kết nối đang mở vẫn bị thu hồi và kiểm tra quyền từng sự kiện/phòng.

**Masking có phải mã hóa không?** Không. Che mặt nạ khung phía máy khách là yêu cầu giao thức; cần TLS cho WSS được mã hóa.

**PHP-FPM và Workerman dùng chung đăng nhập thế nào?** Cookie ký chung và dịch vụ phiên tra SQLite chung; mỗi tiến trình mở kết nối PDO riêng.

## Vai trò B

**Vì sao không chọn SSE?** SSE phù hợp luồng từ máy chủ. Relay có chat và đang nhập hai chiều độc lập nên WS thuận tiện. Gửi HTTP kết hợp nhận SSE vẫn hợp lệ.

**Vì sao lịch sử và sự kiện trực tiếp bị trùng?** Tin có thể được lưu trong lúc tải lịch sử. Hai đường có cùng ID DB, Map gộp thành một. Bộ đếm thế hệ/phòng bỏ kết quả phòng cũ đến muộn.

**Kết nối lại có lấy đủ mọi tin không?** Không, chỉ 50 tin mới nhất. Chưa có con trỏ phát lại bền vững hoặc giao thức bảo đảm đúng một lần.

**Vì sao không tự gửi lại?** Có thể đã lưu nhưng mất kết nối trước khi người gửi thấy phản hồi. Thử lại khi chưa có khóa chống trùng có thể tạo bản ghi thứ hai.

**Tìm kiếm và bản nháp hoạt động ở đâu?** Tìm không dấu trên tin đã tải ở trình duyệt. Bản nháp dùng sessionStorage tách theo tài khoản/phòng/thẻ; lựa chọn màu và mật độ dùng localStorage. Lỗi bộ nhớ không làm hỏng chat.

**Trả lời có phải tin riêng không?** Không, chỉ thêm trích dẫn văn bản vào tin gửi chung trong phòng; tính cả phần đó vào giới hạn 500 mã ký tự.

## Vai trò C

**Vì sao lưu trước khi phát?** Tránh người nhận thấy tin chưa lưu được. Bài kiểm tra gây lỗi ghi chứng minh không phát và không trừ lượt gửi thành công khi INSERT thất bại.

**Vì sao một worker?** Phòng và bộ đếm nằm trong RAM của worker. Tăng worker cần trạng thái chung và pub/sub; chỉ đổi con số sẽ chia rời phòng và giới hạn.

**Kiểm tra đồng thời chứng minh gì?** Mười tin tới đủ socket ở mức 2/10/20 kết nối. Đây là tính đúng của phát tin, không phải thông lượng production hoặc số người độc lập tối đa.

**Số đo độ trễ gồm gì?** Đồng hồ đơn điệu trong Python đo từ gửi đến nhận, bao gồm kiểm tra, lưu, truyền và lịch điều phối; polling thêm chờ lượt hỏi. Mẫu local nhỏ và máy chạy chung không đại diện hiệu năng tổng quát.

**SQLite ảnh hưởng vòng lặp sự kiện thế nào?** Ghi đồng bộ có thể chặn callback khi tranh chấp. WAL giúp đọc/ghi cùng tồn tại, không bỏ giới hạn một bên ghi.

## Mọi thành viên

Chọn một yêu cầu và chỉ ra mã nguồn, bài kiểm tra, mục báo cáo và thao tác demo tương ứng. Giải thích một lỗi thực sự đã phát hiện, cách sửa và cách kiểm tra lại. Nêu một giới hạn cùng hướng mở rộng cụ thể, phân biệt rõ với phần đã có.
