# Hướng dẫn sử dụng Relay

Mở http://localhost:8080, chọn Alice/Bob/Charlie để điền tài khoản mẫu rồi bấm **Đăng nhập vào không gian chung**. Nút Hiện/Ẩn giúp kiểm tra mật khẩu. Để thử chat hai người, dùng hai hồ sơ trình duyệt hoặc cửa sổ ẩn danh; hai thẻ thông thường dùng chung tài khoản.

## Làm việc trong phòng

Chọn Trò chuyện chung để trao đổi chung, Kỹ thuật để thảo luận mã nguồn, Nhóm học tập để ôn bài. Tên phòng, mô tả, danh sách thành viên và tin nhắn cập nhật khi chuyển phòng. Lần mở tiếp theo nhớ phòng gần nhất của tài khoản.

Gõ nội dung rồi nhấn Enter hoặc nút Gửi. Shift + Enter xuống dòng. Bộ đếm bao gồm cả phần trích dẫn nếu đang trả lời. Tối đa 500 mã ký tự Unicode; biểu tượng ghép có thể gồm nhiều mã ký tự. Giới hạn tài khoản là 20 tin/phút trên mọi thẻ.

Rê chuột lên tin nhắn (hoặc chạm tin trên điện thoại) để hiện **Trả lời** và **Sao chép**. Người dùng bàn phím có thể chuyển tiêu điểm tới tin nhắn để mở các thao tác. Bấm **Trả lời** để chèn trích dẫn. Bấm × để hủy. Đây là trích dẫn văn bản gửi vào phòng, không phải hội thoại riêng hoặc liên kết cố định đến tin gốc. Bấm **Sao chép** để lấy nội dung; nếu trình duyệt không cho quyền bộ nhớ tạm, chọn văn bản và sao chép thủ công.

Nút mặt cười mở tám biểu tượng thông dụng và chèn tại vị trí con trỏ. Khi cuộn lên đọc tin cũ, bấm **Về tin nhắn mới nhất** để trở lại cuối hội thoại.

## Tìm và xuất tin

Bấm biểu tượng kính lúp ở đầu phòng hoặc nhấn Ctrl/⌘ + K để mở ô tìm kiếm. Tìm theo tên người gửi và nội dung, không cần gõ dấu: `ky thuat` tìm được `kỹ thuật`. Số kết quả hiện phía trên danh sách. Esc xóa bộ lọc và đóng ô tìm kiếm.

Mở menu **⋯** ở góc phải, chọn **Xuất hội thoại** để tải tệp TXT UTF-8 của các tin đã tải ở phòng hiện tại, theo thứ tự ID. Bộ lọc tìm kiếm không giới hạn tệp xuất. Tệp này chứa tên người gửi và thời gian; cân nhắc nội dung trước khi chia sẻ.

Tìm kiếm/xuất không truy vấn toàn bộ lịch sử máy chủ. Khi vào hoặc nối lại phòng, API trả tối đa 50 tin mới nhất; các tin trực tiếp tiếp tục được thêm trong phiên.

## Bản nháp và kết nối

Bản nháp tách theo tài khoản, phòng và thẻ trình duyệt bằng sessionStorage. Đổi phòng hoặc tải lại cùng thẻ có thể khôi phục nội dung. Đóng thẻ/cửa sổ, xóa bộ nhớ hoặc chế độ chặn lưu có thể làm mất bản nháp; khi lưu bị chặn ứng dụng vẫn giữ tạm trong RAM. Đăng xuất qua nút Đăng xuất xóa bản nháp của tài khoản ở thẻ hiện tại.

Khi mất mạng, ô soạn vẫn dùng được; nút Gửi bị khóa. Ứng dụng tự thử lại sau 1/2/4/8/10 giây, tham gia lại phòng và gộp tin theo ID. Nếu mất kết nối lúc gửi, tin có thể đã được lưu. Nội dung được giữ để bạn kiểm tra trước khi gửi lại; không có thao tác tự gửi lại.

## Giao diện và thành viên

Menu **⋯** ở góc phải tập trung các công cụ ít dùng: **Thành viên**, **Xuất hội thoại**, **Giao diện sáng/tối**, **Thu gọn tin nhắn** và **Hướng dẫn và phím tắt**. Chọn công cụ xong, bấm ngoài hoặc nhấn Esc để đóng menu. Lựa chọn giao diện được nhớ trên trình duyệt; Thu gọn giảm khoảng cách giữa các tin.

Danh sách thành viên mở trong hộp thoại khi cần, trên cả máy tính và điện thoại. Một tài khoản có nhiều thẻ trong cùng phòng vẫn chỉ hiện một lần. Giao diện mặc định tập trung vào chọn phòng, đọc và gửi tin; ô tìm, danh sách thành viên và thao tác từng tin chỉ hiện lúc dùng.

Bộ đếm ký tự xuất hiện khi nội dung đạt 400 mã ký tự để nhắc giới hạn 500. Biểu tượng mặt cười nằm bên trái ô soạn; Gửi nằm bên phải. Shift + Enter xuống dòng. Trên điện thoại các phòng nằm ngang phía trên và vùng tin nhắn cuộn riêng.
