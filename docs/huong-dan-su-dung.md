# Hướng dẫn sử dụng Relay

Mở http://localhost:8080, chọn Alice/Bob/Charlie để điền tài khoản mẫu rồi bấm **Đăng nhập vào không gian chung**. Để tạo tài khoản cá nhân, chọn **Tạo tài khoản** hoặc mở http://localhost:8080/register. Tên đăng nhập dài 3–32 ký tự (chữ thường, số, dấu chấm, gạch dưới hoặc gạch ngang); tên hiển thị dài 2–60 ký tự; mật khẩu dài ít nhất 12 ký tự. Nút Hiện/Ẩn giúp kiểm tra mật khẩu. Để thử chat hai người, dùng hai hồ sơ trình duyệt hoặc cửa sổ ẩn danh; hai thẻ thông thường dùng chung tài khoản.

## Làm việc trong phòng

Chọn Trò chuyện chung để trao đổi chung, Kỹ thuật để thảo luận mã nguồn, Nhóm học tập để ôn bài. Tên phòng, mô tả, danh sách thành viên và tin nhắn cập nhật khi chuyển phòng. Lần mở tiếp theo nhớ phòng gần nhất của tài khoản.

Trong **Bạn bè**, bấm **Nhắn tin** cạnh người đã kết bạn để mở cuộc trò chuyện riêng. Người khác không thể xem tin nhắn đó. Mở **Tạo nhóm**, nhập tên và chọn tối đa 30 người bạn. Chủ nhóm mở **⋯ → Quản lý nhóm** để đổi tên, thêm/xóa thành viên hoặc xóa nhóm. Thành viên có thể rời nhóm. Người bị xóa hoặc đã rời nhóm không còn truy cập được lịch sử.

Trong hộp thoại **Bạn bè**, mục **Lời mời** hiển thị lời mời đến và lời mời đã gửi để chấp nhận, từ chối hoặc hủy. Relay đã gỡ mục **Lân cận** và **Ngẫu nhiên**, đồng thời không yêu cầu quyền truy cập vị trí.

Gõ nội dung rồi nhấn Enter hoặc nút Gửi. Shift + Enter xuống dòng. Bộ đếm bao gồm cả phần trích dẫn nếu đang trả lời. Tối đa 500 mã ký tự Unicode; biểu tượng ghép có thể gồm nhiều mã ký tự. Giới hạn tài khoản là 20 tin/phút trên mọi thẻ.

Rê chuột lên tin nhắn (hoặc chạm tin trên điện thoại) để hiện **Trả lời** và **Sao chép**. Người dùng bàn phím có thể chuyển tiêu điểm tới tin nhắn để mở các thao tác. Bấm **Trả lời** để chèn trích dẫn. Bấm × để hủy. Đây là trích dẫn văn bản gửi vào phòng, không phải hội thoại riêng hoặc liên kết cố định đến tin gốc. Bấm **Sao chép** để lấy nội dung; nếu trình duyệt không cho quyền bộ nhớ tạm, chọn văn bản và sao chép thủ công.

Nút mặt cười mở tám biểu tượng thông dụng và chèn tại vị trí con trỏ. Khi cuộn lên đọc tin cũ, bấm **Về tin nhắn mới nhất** để trở lại cuối hội thoại.

## Tìm và xuất tin

Bấm biểu tượng kính lúp ở đầu phòng hoặc nhấn Ctrl/⌘ + K để mở ô tìm kiếm. Tìm theo tên người gửi và nội dung trong toàn bộ lịch sử phòng, không cần gõ dấu: `ky thuat` tìm được `kỹ thuật`. Kết quả tải theo trang; bấm **Tải thêm kết quả** khi còn kết quả khác. Esc xóa bộ lọc và đóng ô tìm kiếm.

Mở menu **⋯** ở góc phải, chọn **Xuất hội thoại** để tải tệp TXT UTF-8 của toàn bộ lịch sử phòng hiện tại, theo thứ tự ID. Ứng dụng tải từng trang cũ trước khi tạo tệp. Bộ lọc tìm kiếm không giới hạn tệp xuất. Tệp này chứa tên người gửi và thời gian; cân nhắc nội dung trước khi chia sẻ.

Khi vào hoặc nối lại phòng, API trả tối đa 50 tin mới nhất. Bấm **Tải tin nhắn cũ hơn** để xem tiếp các tin trước đó; việc tải thêm không thay đổi tin mới đã có trên màn hình.

## Bản nháp và kết nối

Bản nháp tách theo tài khoản và phòng bằng localStorage, nên vẫn có thể khôi phục sau khi tải lại trang hoặc đóng/mở lại thẻ trong cùng hồ sơ trình duyệt. Xóa dữ liệu trang, dùng chế độ riêng tư hoặc chặn lưu có thể làm mất bản nháp; khi lưu bị chặn ứng dụng vẫn giữ tạm trong RAM. Đăng xuất xóa bản nháp của tài khoản trong trình duyệt.

Khi mất mạng, ô soạn vẫn dùng được; nút Gửi bị khóa. Ứng dụng tự thử lại sau 1/2/4/8/10 giây, tham gia lại phòng và gộp tin theo ID. Nếu mất kết nối lúc gửi, tin có thể đã được lưu. Nội dung được giữ để bạn kiểm tra trước khi gửi lại; không có thao tác tự gửi lại.

## Giao diện và thành viên

Menu **⋯** ở góc phải tập trung các công cụ ít dùng: **Thành viên**, **Xuất hội thoại**, **Giao diện sáng/tối**, **Thu gọn tin nhắn** và **Hướng dẫn và phím tắt**. Chọn công cụ xong, bấm ngoài hoặc nhấn Esc để đóng menu. Lựa chọn giao diện được nhớ trên trình duyệt; Thu gọn giảm khoảng cách giữa các tin.

Danh sách thành viên mở trong hộp thoại khi cần, trên cả máy tính và điện thoại. Một tài khoản có nhiều thẻ trong cùng phòng vẫn chỉ hiện một lần. Giao diện mặc định tập trung vào chọn phòng, đọc và gửi tin; ô tìm, danh sách thành viên và thao tác từng tin chỉ hiện lúc dùng.

Bộ đếm ký tự xuất hiện khi nội dung đạt 400 mã ký tự để nhắc giới hạn 500. Biểu tượng mặt cười nằm bên trái ô soạn; Gửi nằm bên phải. Shift + Enter xuống dòng. Trên điện thoại các phòng nằm ngang phía trên và vùng tin nhắn cuộn riêng.
