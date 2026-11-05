# Lời thuyết trình Relay bằng tiếng Việt



Bản chuẩn bị ngày 28/10/2026; điền danh tính thật và đối chiếu ngôn ngữ theo đề gốc khi ghi video nộp. Chưa có xác nhận thành viên đã trình bày.



## Slide 1 — WebSocket và giao tiếp thời gian thực

Người trình bày dự kiến: A

Chúng tôi giới thiệu Relay, đồ án minh họa WebSocket và giao tiếp thời gian thực bằng PHP. HTTP phục vụ đăng nhập và lịch sử, còn kết nối liên tục xử lý chat, đang nhập và trực tuyến. Chúng tôi giải thích mô hình, kiến trúc, thao tác thật và kết quả đo kèm giới hạn. Thông tin thành viên được để trống để điền đúng người thật. Bản tài liệu tiếng Việt hỗ trợ chuẩn bị theo yêu cầu; cần đối chiếu quy định ngôn ngữ trong đề gốc khi nộp.



## Slide 2 — HTTP và kết nối liên tục

Người trình bày dự kiến: A

HTTP có yêu cầu/phản hồi hữu hạn, phù hợp lấy dữ liệu và xác thực. Phòng chat cần cập nhật mà người nhận không liên tục hỏi lại. WebSocket cho hai bên chủ động gửi trên kết nối mở. Điều này không bỏ HTTP hoặc tự giải quyết mọi lỗi. Proxy có thể timeout, máy ngủ hoặc tiến trình restart; ứng dụng cần quản lý danh tính, phòng và phục hồi. Sơ đồ cho thấy một Nginx chuyển tới hai tiến trình PHP dùng chung SQLite. Nguồn nguyên lý: RFC 6455 và MDN; chi tiết báo cáo mục 2.1–2.2.



## Slide 3 — Bắt tay danh tính và thông điệp

Người trình bày dự kiến: A

Mở Network để chỉ yêu cầu /ws và phản hồi 101, che cookie và bí mật. Workerman xử lý bắt tay và khung theo RFC. Gateway kiểm cookie ký và bản ghi phiên, Origin chính xác; sau đó tiếp tục kiểm phiên và quyền từng sự kiện. 101 không xác thực vĩnh viễn. Khung có opcode/độ dài/masking, còn chat:send là tên JSON do ứng dụng định nghĩa. Masking không bảo mật nội dung; triển khai công khai cần HTTPS/WSS. Bản demo chỉ dùng loopback, chưa chứng minh TLS công khai.



## Slide 4 — Các phương án truyền dữ liệu

Người trình bày dự kiến: B

Polling ngắn dễ dùng nhưng có thời gian chờ chu kỳ và yêu cầu lúc nhàn rỗi. Polling dài giữ yêu cầu rồi mở lại, cần xử lý timeout. SSE có luồng một chiều và cơ chế EventSource nối lại, phù hợp thông báo/tiến độ; gửi lên dùng HTTP hoặc kênh khác. WebSocket thuận tiện khi chat/đang nhập độc lập hai chiều. Không có lựa chọn luôn nhanh nhất cho mọi tải. Phép đo của nhóm chỉ đổi đường nhận, giữ cùng người gửi WS, phòng, DB và máy chủ.



## Slide 5 — Một Origin hai tiến trình PHP

Người trình bày dự kiến: C

Trình duyệt chỉ nối Nginx ở cổng 8080, nội bộ Docker giữ cổng WS và FPM. PHP-FPM xử lý yêu cầu ngắn, Workerman giữ socket và vòng lặp. Mỗi tiến trình mở PDO riêng tới cùng volume SQLite; biến RAM PHP không phải nguồn phiên chung. Worker mở DB sau khi khởi động. Phòng, typing và giới hạn nằm RAM một worker, còn user/tin/phiên nằm DB. Không tăng worker nếu chưa có trạng thái chung và pub/sub.



## Slide 6 — Dữ liệu và luồng lưu tin

Người trình bày dự kiến: C

Repository dùng SQL có tham số; khóa ngoại và chỉ mục room_id,id hỗ trợ lịch sử. Lấy mới nhất bằng giảm dần có giới hạn rồi đảo thứ tự. ChatService kiểm quyền, schema và giới hạn tài khoản trước khi ghi. Chỉ khi INSERT thành công mới ghi lượt và phát chat:new. Test gây lỗi ghi chứng minh không phát/không trừ lượt gửi thành công. ID giúp gộp cùng bản ghi trên HTTP/WS, không bảo đảm lưu đúng một lần nếu người gửi tự thử lại sau disconnect.



## Slide 7 — Trò chuyện bằng tiếng Việt

Người trình bày dự kiến: B

Mở Alice và Bob ở hai hồ sơ riêng vì các thẻ thường chia sẻ cookie. Chọn Trò chuyện chung, gửi dấu riêng từ Alice rồi Bob trả lời; chỉ ra cập nhật không tải lại. Chuyển Bob sang Kỹ thuật, Alice gửi/nhập ở phòng cũ; Bob không thấy sự kiện phòng khác. Tiếp theo tìm ky thuat để thấy tìm không dấu, đổi phòng rồi quay lại bản nháp, thử trích dẫn/sao chép và xuất TXT. Các thao tác chỉ trên tin đã tải; không khẳng định tìm hoặc xuất toàn bộ DB. Ảnh lấy từ trình duyệt bản tiếng Việt.



## Slide 8 — Trực tuyến và khôi phục kết nối

Người trình bày dự kiến: B

Mở thẻ thứ hai cùng tài khoản để chỉ danh sách không trùng. Dừng gõ để trạng thái hết hạn ba giây ở máy chủ. Giả lập ngoại tuyến, chỉ trạng thái và nút Gửi bị khóa nhưng vẫn soạn/lưu nháp. Khôi phục mạng, client nối lại, vào phòng và gộp ID. Tin chưa rõ kết quả không tự gửi lại vì có thể đã lưu. Lịch sử API chỉ 50 tin mới nhất, không cam kết mọi tin khi vắng lâu. Mở điện thoại và hộp thoại Thành viên, thử sáng/tối và thu gọn; lựa chọn được nhớ.



## Slide 9 — Bền vững và xử lý đầu vào

Người trình bày dự kiến: C

Bảng là kết quả lịch sử có log thật, không tự đổi ngày. PHPUnit 28 bài/78 xác nhận, bảy nhóm tích hợp, restart giữ dấu tin, peer không pong đóng khoảng 55,828 giây. Kiểm tra input gồm JSON/schema, binary phân mảnh, 500/501 mã Unicode, giới hạn tài khoản và lỗi ghi. Bộ UI tiếng Việt kiểm nháp, tìm, quote, sao chép/xuất, offline, preferences và nhiều chiều rộng. Xem log vietnamese-ui và vietnamese-validation để biết kết quả mới. Test chức năng không thay video/đóng góp thật.



## Slide 10 — Kết quả polling và WebSocket local

Người trình bày dự kiến: C

Mỗi kênh nhận ba lượt xen kẽ, người gửi WS chung, gửi cách sáu giây, polling một giây. Dùng perf_counter cùng tiến trình Python để đo gửi đến nhận, có kiểm/lưu/mạng/lịch điều phối và chờ polling. Trung vị polling 452,95–504,52 ms, WS 17,63–23,73 ms. Đây là quan sát trên máy local với mẫu nhỏ và hoạt động phát triển chung. Byte chỉ JSON, không header/khung/TLS; WS có trạng thái phòng nên không so băng thông cùng nội dung. Không suy thành công suất hoặc độ trễ mạng tổng quát.



## Slide 11 — Đánh giá trong phạm vi đã kiểm tra

Người trình bày dự kiến: B

Kết quả đủ 20/100/200 bản giao là kiểm fanout ở mức đó. Nhiều socket có thể chung tài khoản, không phải sức chứa người dùng độc lập. RAM phòng/typing/limit mất khi restart; DB giữ dữ liệu. SQLite đồng bộ có thể chặn vòng lặp. Chưa có cursor phát lại dài hoặc ID thao tác để retry an toàn. Chưa có registration/upload/private chat hoặc audit trợ năng đầy đủ. UI nhiều kích thước hữu ích nhưng không chứng minh mọi thiết bị. Phạm vi rõ giúp người đọc dùng kết quả đúng.



## Slide 12 — Độ tin cậy vượt ra ngoài socket

Người trình bày dự kiến: A

Relay cho thấy thời gian thực là hành vi toàn hệ thống. Bắt tay đúng chưa đủ; cần phiên, quyền phòng, dữ liệu chuẩn, lưu trước phát, UI và kiểm tra. Bản tiếng Việt giúp thao tác dễ hơn, giữ nguyên hợp đồng kỹ thuật. Hướng tiếp là khóa duy nhất theo ID thao tác, cursor phân trang, trạng thái chung/pub-sub và vận hành TLS/sao lưu/giám sát. Mã, README, tài liệu và log được bàn giao cùng nhau. Nhóm cần điền danh tính thật và thu video theo yêu cầu môn; không có xác nhận nộp chính thức từ bộ tài liệu.

