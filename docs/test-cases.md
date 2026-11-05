# Kết quả kiểm thử đã thực hiện

Kết quả gốc ngày 22/10/2026 (Asia/Saigon): Windows, Docker Linux containers, PHP 8.3.36, Workerman 5.2.3, PHPUnit 11.5.57, một worker select-loop. Unit dùng DB trong RAM; tích hợp dùng Compose riêng 8088, không reset demo chính.

28 bài PHPUnit/78 xác nhận đạt; 7/7 nhóm tích hợp đạt; cú pháp PHP lần đầu 40 tệp và 41 sau thêm reset; Composer kiểm tra nghiêm ngặt đạt. JUnit tại `evidence/logs/phpunit.xml`, tích hợp tại `evidence/logs/integration.json`.

| Ca | Kết quả và phạm vi gốc | Minh chứng |
| --- | --- | --- |
| TC-01 | ĐẠT: Đăng nhập đúng/sai, chặn API khách, xoay cookie, cấu trúc user | Tích hợp xác thực; unit phiên |
| TC-02 | ĐẠT: Chặn cookie khách/sửa/thu hồi trước Upgrade; hết hạn trong unit | Tích hợp xác thực; unit hết hạn |
| TC-03 | ĐẠT: Từ chối CSRF/Origin sai cho HTTP và Origin sai cho WS | Tích hợp xác thực |
| TC-04 | ĐẠT: Người gửi/nhận cùng ID đã lưu, không lặp echo | Tích hợp chat; trình duyệt bổ trợ |
| TC-05 | ĐẠT: Cô lập phòng, chặn chưa tham gia, phòng không tồn tại | Tích hợp phòng; unit Hub |
| TC-06 | ĐẠT: Hai socket một tài khoản hiện một người, đóng một socket vẫn còn | Tích hợp presence; unit Hub |
| TC-07 | ĐẠT: Tham gia lặp, đổi phòng máy chủ, bỏ phản hồi lịch sử cũ tới muộn | Tích hợp phòng; unit Hub; trình duyệt |
| TC-08 | ĐẠT: Offline khóa gửi; online nối lại/gộp ID duy nhất, có ghi phương pháp giả lập | Log trình duyệt |
| TC-09 | ĐẠT: Biên 500/501 mã Unicode, cắt khoảng trắng và chặn rỗng | Tích hợp payload; unit Validator |
| TC-10 | ĐẠT: Schema sai/lạ, nhị phân/phân mảnh bị chặn; văn bản phân mảnh và quá cỡ | Tích hợp payload |
| TC-11 | ĐẠT: Giới hạn tài khoản qua hai socket, không lưu tin bị chặn; kiểm biên bằng đồng hồ unit | Tích hợp limit; unit limiter |
| TC-12 | ĐẠT: TTL đang nhập/dọn đóng bình thường; peer thật không pong đóng sau 55,828 giây | Tích hợp presence; runtime-verification.json |
| TC-13 | ĐẠT: Chuỗi giống SQL lưu như dữ liệu; chuỗi giống script hiện nguyên, không dialog | Unit repository; trình duyệt |
| TC-14 | ĐẠT: Đăng xuất đóng socket cùng phiên, chặn HTTP, có độ trễ đo trong JSON | Tích hợp logout |
| TC-15 | MỘT PHẦN: Kiểm cờ cookie/cấu trúc API/cổng; TLS công khai chưa triển khai | Tích hợp auth; Compose |
| TC-16 | ĐẠT: Restart HTTP/WS thật giữ tin/phiên; thứ tự 50 tin mới nhất trong unit | runtime-verification.json; unit repository |
| TC-17 | ĐẠT: SQL cố ý lỗi không phát/không trừ lượt gửi thành công; phục hồi thành công | PHPUnit ChatTest |
| TC-18 | ĐẠT: Nguồn giải nén riêng cài khóa thư viện mới, secret/volume/project mới; test/login/chat đạt | clean-install.json |
| TC-19 | ĐẠT: Sáu lượt 120 giây, hai lượt nhàn rỗi 60 giây, nhận đủ 120/120; giới hạn local ghi rõ | evidence/raw |
| TC-20 | ĐẠT: Mười tin tới 2/10/20 socket nhận đủ 20/100/200 bản | concurrent.json; tích hợp đồng thời |

Hai mươi kết nối là kiểm chức năng phát tin, không phải thông lượng production. Kết quả unit không thay tiêu chí video/định dạng. Bộ bootstrap chậm cũng đạt: các nút chờ phiên/CSRF, sai mật khẩu trả lỗi chung, đúng thì kết nối. Kiểm tra thư viện lúc đó không báo lỗ hổng đã biết; đây là ảnh chụp dependency theo thời điểm, không chứng nhận an toàn toàn hệ thống.

## Kiểm tra bản tiếng Việt ngày 28/10/2026

Bản sửa chạy lại PHPUnit/cú pháp và các kịch bản trình duyệt liên quan. Kết quả mới ở `evidence/logs/vietnamese-ui.json`, `vietnamese-login.json`, `vietnamese-browser.json` và `vietnamese-validation.json`. Bộ giao diện mới kiểm riêng nháp hai phòng/tải lại, xóa nháp sau echo, tìm không dấu/rỗng, trích dẫn/emoji/501 Unicode, nhớ sáng/tối/thu gọn, xuất TXT, bộ nhớ tạm, soạn ngoại tuyến, phím tắt/trợ giúp, thành viên ở 320/390/768/1024/1440 px và lỗi JS. Xem log để biết các lần chạy thực sự, không dùng log gốc thay kiểm tra mới.
