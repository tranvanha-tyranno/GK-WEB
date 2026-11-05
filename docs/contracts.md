# Hợp đồng dữ liệu dùng chung

Mọi ID là số nguyên JSON. Phản hồi HTTP có dạng `{ "data": ... }` hoặc `{ "error": { "code": "...", "message": "..." } }`. API được bảo vệ trả 401 khi chưa đăng nhập. Yêu cầu thay đổi dữ liệu phải có Origin chính xác trong danh sách cho phép và `X-CSRF-Token` lấy từ `GET /api/auth/me`.

`User = {id, username, displayName}`. `Message = {id, roomId, user: User, content, createdAt}`. Thời gian là UTC ISO 8601, hiển thị theo định dạng `vi-VN` và múi giờ trình duyệt. JSON không chứa khóa bí mật, mật khẩu băm hoặc giá trị cookie đăng nhập.

| Phương thức | Đường dẫn | Dữ liệu gửi | Nội dung `data` |
| --- | --- | --- | --- |
| GET | `/api/health` | Không cần | `{status:"ok"}` |
| GET | `/api/auth/me` | Cookie nếu có | `{user:User|null,csrfToken:string}`; tạo phiên khách khi cần |
| POST | `/api/auth/login` | JSON `{username,password}` + Origin/CSRF | `{user:User,csrfToken:string}`; cookie mới, thu hồi phiên cũ |
| POST | `/api/auth/logout` | Origin/CSRF, đã đăng nhập | `{ok:true}`; thu hồi phiên trước khi xóa cookie |
| GET | `/api/rooms` | Đã đăng nhập | `[{id,name}]` |
| GET | `/api/rooms/{id}/messages?limit=50` | Đã đăng nhập; limit 1..50 | Các tin mới nhất, trả theo ID tăng dần |
| GET | `/api/benchmark/rooms/{id}/messages?afterId=N` | Đã đăng nhập, bật chế độ đo | Sau mốc ID, tối đa 1000 tin/lần; chỉ phục vụ công cụ so sánh |

Mã HTTP: 400 cho dữ liệu sai; 401 chưa đăng nhập; 403 sai Origin/CSRF; 404 không có phòng/đường dẫn; 413 thân yêu cầu quá lớn; 503 dịch vụ không sẵn sàng. Sau Upgrade, lỗi WebSocket dùng `system:error`, không phải phản hồi HTTP. Đăng nhập sai luôn trả thông báo chung. API polling mặc định tắt ở ứng dụng thường.

WebSocket `/ws` kiểm tra cùng cookie có chữ ký và Origin trước Upgrade. Mỗi socket thuộc một phòng. Cấu trúc chung: `{type:string,payload:object}`. Từ chối trường lạ, dữ liệu không phải đối tượng và tin nhị phân.

| Chiều | Sự kiện | Dữ liệu |
| --- | --- | --- |
| Máy khách → máy chủ | `room:join` | `{roomId:int}` |
| Máy chủ → máy khách | `room:joined` | `{roomId:int}` |
| Máy khách → máy chủ | `chat:send` | `{roomId:int,content:string}` |
| Máy chủ → máy khách | `chat:new` | Message, gửi cả người gửi sau khi lưu |
| Máy khách → máy chủ | `typing:set` | `{roomId:int,isTyping:bool}` |
| Máy chủ → máy khách | `room:presence` | `{roomId:int,users:User[]}`; ảnh chụp trạng thái đã gộp theo tài khoản |
| Máy chủ → máy khách | `room:typing` | `{roomId:int,users:User[]}`; ảnh chụp trạng thái, hạn 3 giây |
| Máy chủ → máy khách | `system:error` | `{code:string,message:string}` |

Các mã lỗi giữ nguyên để máy khách xử lý: `INVALID_PAYLOAD`, `ROOM_NOT_FOUND`, `NOT_IN_ROOM`, `MESSAGE_TOO_LONG`, `PAYLOAD_TOO_LARGE`, `RATE_LIMITED`, `INTERNAL_ERROR`. Nội dung thông báo cho người dùng là tiếng Việt. Phiên sai/hết hạn/bị thu hồi ngăn Upgrade hoặc đóng kết nối hiện có. Vi phạm ở lớp khung có thể đóng kết nối trước khi trả lỗi JSON. Mỗi tin chat/nhập đều kiểm tra phòng, danh tính luôn lấy từ phiên.

Nội dung được cắt khoảng trắng Unicode, không rỗng, tối đa 500 mã ký tự. JSON tối đa 8192 byte UTF-8; thư viện chừa tối đa 14 byte tiêu đề khung và ứng dụng kiểm tra lại tin đã ghép. Giới hạn 20 tin được chấp nhận/tài khoản/cửa sổ trượt 60 giây, dùng chung mọi socket. Ping mỗi 20 giây, không nhận pong khoảng 55–56 giây thì đóng. Kiểm tra phiên trước sự kiện, trước giao tin và mỗi giây.

Khi tham gia, máy chủ cập nhật thành viên rồi gửi `room:joined`. Trình duyệt nhận sự kiện trực tiếp vào Map theo ID trong lúc tải lịch sử, gộp kết quả và bỏ phản hồi cũ sau đổi phòng. Lịch sử chỉ 50 tin; không bảo đảm lấy đủ mọi tin khi ngoại tuyến lâu. Không tự gửi lại tin chưa rõ kết quả.

Các repository PDO chịu trách nhiệm SQL có tham số: `latest(roomId,limit=50)`, `create(userId,roomId,content)`, `after(roomId,afterId)` trả đúng cấu trúc Message. `SessionService` ký cookie, chỉ lưu băm token, xử lý phiên khách/đăng nhập, xoay phiên và thu hồi dùng chung. Mỗi tiến trình mở PDO riêng sau khi khởi động worker.

Tên phòng lưu trong API vẫn là `General`, `Engineering`, `Study group`; trình duyệt ánh xạ thành Trò chuyện chung, Kỹ thuật, Nhóm học tập. Tìm kiếm, bản nháp, giao diện và xuất tệp chạy ở máy khách; không mở thêm API hoặc thay quy tắc phân quyền.
