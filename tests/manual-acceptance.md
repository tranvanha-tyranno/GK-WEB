# Kiểm tra chấp nhận trên trình duyệt

Dùng hai hồ sơ riêng cho Alice/Bob. Kết hợp trình duyệt, giao thức và PHPUnit; ảnh không tự chứng minh phân quyền, giới hạn hoặc lưu dữ liệu.

## Kết quả gốc ngày 22/10/2026

- [x] Đăng nhập đúng/sai, lỗi bảo mật không lộ thông tin.
- [x] Hai tài khoản trao đổi dấu riêng không tải lại, mỗi ID hiện một lần.
- [x] Chuyển phòng không lọt chat/đang nhập.
- [ ] Kiểm tra thủ công nhiều thẻ gộp một tài khoản; máy chủ có test riêng.
- [ ] Kiểm tra thủ công gõ/dừng/đổi phòng; TTL máy chủ có test riêng.
- [x] Offline/online khóa gửi, nối lại và gộp không trùng.
- [x] Phản hồi lịch sử cũ không thay phòng mới.
- [x] Chuỗi `<script>alert(1)</script>` hiển thị văn bản, không mở hộp thoại.
- [x] 390×844 và 1440×900 không tràn ngang.
- [x] Đăng xuất thẻ chung phiên thu hồi socket.
- [x] Restart không xóa volume vẫn còn tin.

## Kiểm tra bản cập nhật tiếng Việt

Chạy `tools/vietnamese-ui-check.js`, `tools/browser-acceptance.js` và `tools/login-initialization.js` bằng `tools/run-browser-check.py`. Kết quả mới lưu riêng trong `evidence/logs/vietnamese-*.json`. Đối chiếu dấu tiếng Việt, bản nháp từng phòng/tải lại, tìm không dấu, kết quả rỗng, trích dẫn, emoji, Unicode, sáng/tối/thu gọn sau tải lại, xuất TXT, sao chép, soạn ngoại tuyến, Ctrl K/Esc, thành viên và chiều rộng 320/390/768/1024/1440. Không đánh dấu phần chưa chạy chỉ vì có kịch bản.
