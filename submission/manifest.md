# Danh mục bàn giao tiếng Việt

Bản cập nhật bắt đầu 28/10 và hoàn tất 29/10/2026 (Asia/Saigon). Tài liệu và ứng dụng đã cập nhật; nộp chính thức và video ba thành viên thật vẫn cần người dùng thực hiện.

| Sản phẩm | Trạng thái | Nội dung |
| --- | --- | --- |
| report.docx / report.pdf | ĐÃ TẠO | 22 trang tiếng Việt, 5 chương, tài liệu tham khảo và phụ lục; bìa để trống thông tin thật |
| slides.pptx / slides.pdf | ĐÃ TẠO | 12 trang, văn bản/bảng chỉnh sửa được, ghi chú tiếng Việt, ảnh giao diện mới |
| source.zip | ĐÃ TẠO | Mã, schema/seed, khóa thư viện, Docker, README tiếng Việt, test và minh chứng; không có .env, vendor hoặc DB thực |
| recording-kit | ĐÃ TẠO | Lời nói, thao tác và câu hỏi tiếng Việt; đối chiếu ngôn ngữ theo đề gốc khi nộp |
| reference-walkthrough.webm | TƯ LIỆU CŨ BỔ TRỢ | 34,52 giây, im lặng; giao diện bản cũ, không thay video nhóm cuối |
| demo.mp4 có ba thành viên thật | CHƯA GHI HÌNH | Kiểm tra âm thanh, quy định ngôn ngữ và thời lượng tối đa 20 phút |
| Họ tên / mã sinh viên / lớp / giảng viên | NGƯỜI DÙNG ĐIỀN | Để trống có chủ đích, chỉnh theo mẫu thật khi cần |
| Nộp chính thức / biên nhận / xác nhận đóng góp | CHƯA THỰC HIỆN | Không tự tạo sự tham gia hoặc xác nhận |

## Kiểm chứng

Xem `evidence/logs/vietnamese-validation.json`: kết quả PHP, tích hợp HTTP/WS, giao diện, đăng nhập, hồi quy trình duyệt và kiểm tra tài liệu. Log ngày 22/10/2026 được giữ là lịch sử, không gắn thành phép chạy mới. Sáu lượt đo và dữ liệu thô cũ giữ nguyên; không chạy lại chỉ để đổi ngôn ngữ.

Giới hạn: một worker, SQLite đồng bộ, khôi phục 50 tin, tìm/xuất chỉ tin đã tải, chưa kiểm chứng TLS công khai. Bản nháp trong thẻ trình duyệt không phải lưu trữ máy chủ.

## Toàn vẹn tệp

| Tệp | Byte | SHA256 |
| --- | --- | --- |
| `report.docx` | 531170 | `91d5a31f38a844801277329de6e7cd8e7191821a5adb0ab93b2d38a4b253207b` |
| `report.pdf` | 794959 | `4b82c2027e46490f7ec178f38368633427dbe205baf5ac5ea08b4bddc08e55b9` |
| `slides.pptx` | 401948 | `0004cfa3411da13113c1103ab76c849d17b00a4caf9bc6cc0a510cca9e1ef53e` |
| `slides.pdf` | 330793 | `b55f2ed606931202980ada4bdd61082d0863b45854108e164e400cf46cb75701` |
| `source.zip` | 1160165 | `9438c6b63cb8a2bcca7b9a228ef31566d6e2c5b05ccfc0d2becc97fb8880432b` |
| `reference-walkthrough.webm` | 1289226 | `5d6d13874ae3e951f4984366afe098862c80fd4f41aba875086865c69e0f29cb` |
