# Hướng dẫn thuyết trình và ghi hình

Bộ slide 12 trang và ghi chú A/B/C đã được chuyển sang tiếng Việt theo yêu cầu ngày 28/10/2026. Bản lời nói ở `submission/recording-kit/script.md`. Họ tên, mã sinh viên, lớp và giảng viên để trống để điền thông tin thật. Tài liệu yêu cầu môn học gốc có quy định trình bày tiếng Anh; bản tiếng Việt này hỗ trợ chuẩn bị, cần đối chiếu quy định khi ghi video nộp chính thức.

## Khung thời gian luyện tập

| Mốc dự kiến | Người trình bày | Nội dung |
| --- | --- | --- |
| 00:00–01:00 | A | Giới thiệu đề tài, phạm vi, thành viên thật |
| 01:00–03:00 | A | HTTP/WS, 101, phiên/bảo mật, lệnh chạy |
| 03:00–04:30 | C | Kiến trúc, trạng thái bền vững/tạm thời, lưu trước khi phát |
| 04:30–07:30 | B | Hai hồ sơ, chat hai chiều, cô lập phòng, trực tuyến/đang nhập |
| 07:30–09:00 | B | Mất/kết nối lại, bản nháp, giao diện điện thoại, hiển thị văn bản an toàn |
| 09:00–11:30 | C | Tin tồn tại sau restart, kiểm tra dữ liệu/giới hạn, giải thích mã và test |
| 11:30–12:30 | C | Sáu lần đo, phương pháp và giới hạn |
| 12:30–13:00 | B | Tìm kiếm, trả lời, xuất hội thoại, sáng/tối và trải nghiệm |
| 13:00–14:00 | A | Hướng phát triển, vị trí nguồn/minh chứng, kết thúc |

Thay các mốc trên bằng thời gian video thật sau khi ghi. Cho phép thời gian thao tác và ngừng nghỉ; video nộp không quá 20 phút. Chưa có xác nhận cả ba thành viên đã ghi hình.

## Chuẩn bị

1. Chạy `powershell -ExecutionPolicy Bypass -File tools/setup.ps1`, kiểm tra `http://localhost:8080/api/health`.
2. Dùng một hồ sơ cho Alice, một hồ sơ cho Bob; mật khẩu trong README. Thẻ thông thường dùng chung phiên, chỉ phù hợp minh họa nhiều socket một tài khoản.
3. Nếu thực sự cần dữ liệu demo sạch, xuất nội dung cần giữ trước rồi chạy lệnh reset bên dưới. Lệnh xóa hội thoại/phiên demo, không phải bước khởi động thường ngày.
4. Tăng cỡ chữ trình duyệt/terminal, ẩn thông báo cá nhân và tệp bí mật; thu thử âm thanh 30 giây mỗi người.
5. Mở slide, ứng dụng, mã liên quan, kết quả test và biểu đồ sáu lần đo. Chỉ chọn đoạn mã giải thích được luồng kiểm tra/lưu/phát.

```powershell
docker compose run --rm app php bin/reset-demo.php --confirm=RESET_DEMO
docker compose restart ws
```

## Thao tác và cách giải thích

Mở DevTools → Network → WS, chỉ phản hồi 101 và tên sự kiện JSON; che cookie/bí mật. Giải thích: kết nối nâng cấp một lần nhưng quyền vẫn được kiểm tra từng sự kiện và trước khi giao tin. Có bản chụp đã lược bí mật ở `evidence/logs/protocol-capture.json`.

Alice gửi dấu nhận diện riêng, Bob nhận rồi trả lời. Máy chủ lưu trước, hai bên thấy cùng ID DB. Chuyển Bob sang Kỹ thuật; tin và trạng thái nhập của Alice ở Trò chuyện chung không được xuất hiện bên Bob.

Mở thêm một thẻ Alice để minh họa một tài khoản vẫn hiện một người. Đóng một thẻ và giữ thẻ kia. Gõ rồi dừng để giải thích trạng thái nhập hết hạn 3 giây. Ngắt mạng: trạng thái đổi, vẫn soạn bản nháp được, nút Gửi bị khóa. Khôi phục mạng, chỉ ra tham gia lại và gộp ID; tối đa 50 tin mới nhất, không tự gửi lại tin chưa rõ kết quả.

Tìm không dấu, đổi phòng rồi quay lại bản nháp, trả lời bằng trích dẫn và đổi màu sáng/tối. Dùng xuất hội thoại để giải thích phạm vi chỉ tin đã tải. Mở danh sách thành viên và giao diện điện thoại.

Gửi một dấu nhận diện, chạy `docker compose restart ws app`, mở lại và tìm tin để minh họa bền vững. Không xóa volume. Phép đo không pong mất khoảng 56 giây; dùng log và giải thích ngắn thay vì chờ toàn bộ thời gian.

Với dữ liệu sai/giới hạn, dùng test và một ví dụ kiểm soát, tránh vô tình làm tài khoản demo chạm giới hạn trước các cảnh khác. Test dùng 8088 riêng. Khi trình bày kết quả đo, nêu chu kỳ polling một giây, mẫu nhỏ, máy chạy chung và byte không gồm tiêu đề/khung.

## Hoàn tất video

Thu bằng công cụ có âm thanh rõ của từng người thật. Đối chiếu yêu cầu ngôn ngữ gốc trước khi nộp. Xuất MP4 thông dụng, xem lại từ đầu đến cuối, kiểm tra âm thanh, độ rõ và chuyển màn hình.

Chỉ đặt `submission/demo.mp4` khi đã đáp ứng yêu cầu. Video tham chiếu im lặng là tư liệu phụ, không đổi tên để coi như bản trình bày nhóm. Điền thời gian thật trong `rubric-evidence.md`, thời lượng/tệp trong manifest và đóng góp thực tế trong `contributions.md`. Xác nhận hạn/kênh nộp và biên nhận bằng thao tác thật; Codex chưa nộp bài.
