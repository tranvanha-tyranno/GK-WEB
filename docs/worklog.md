# Nhật ký triển khai và kiểm chứng

Nhật ký ghi công việc Codex thực hiện trong phạm vi A/B/C được giao. Không gán số giờ, chữ ký, tham dự hoặc rà soát cho sinh viên. Thành viên cần bổ sung đóng góp nghiên cứu/kiểm tra/thuyết trình thực tế. Số giờ trong phân công là ước lượng.

| Ngày | Công việc | Minh chứng | Trạng thái |
| --- | --- | --- | --- |
| 22/10/2026 | Khung Docker/PHP/Composer; schema/seed/repository và phiên chung | Dockerfile, Compose, src, database, khóa thư viện | Đã triển khai và kiểm tra |
| 22/10/2026 | Workerman, phòng, đang nhập, lưu tin, kiểm tra dữ liệu, heartbeat, giới hạn | src/Realtime, src/Services, src/Protocols | 7/7 nhóm tích hợp đạt |
| 22/10/2026 | Giao diện thích ứng, WebSocket gốc, gộp ID, nối lại, textContent | public, resources, ảnh trình duyệt | Đã triển khai, kết quả trình duyệt lưu riêng |
| 22/10/2026 | Kiểm thử đơn vị | evidence/logs/phpunit.xml | 28 bài, 78 xác nhận đạt |
| 22/10/2026 | Kiểm thử hộp đen HTTP/WS | evidence/logs/integration.json | 7/7 nhóm, có 2/10/20 socket |
| 22/10/2026 | Cú pháp PHP và Composer | tools/lint.php, log | Lần đầu 40 tệp, sau thêm reset là 41 |
| 22/10/2026 | Sửa quyền SQLite trên Windows | Volume chung, entrypoint ownership/umask | Kiểm tra lại ghi HTTP/WS |
| 22/10/2026 | Sửa bộ chạy giao thức | IPv4 loopback, nhận diện từng tin chính xác | Chạy lại tuần tự 7/7 đạt |
| 22/10/2026 | Word/PDF 34 trang và 12 slide bản gốc; lời trình bày; tham chiếu im lặng 34,52 giây | submission, dữ liệu sáu lần đo và hai lần nhàn rỗi | Bản bàn giao gốc; chưa có video người thật |
| 28/10/2026 | Việt hóa giao diện, lỗi HTTP/WS, dữ liệu mẫu mới, tài liệu và nguồn xuất | resources/views, public/assets, src, docs | Kết quả kiểm tra bản cập nhật được lưu riêng |
| 28/10/2026 | Thêm tìm không dấu, bản nháp, trích dẫn, sao chép, xuất TXT, biểu tượng, sáng/tối, thu gọn, thành viên trên điện thoại | chat.js, preferences.js, workspace.css | Xem log vietnamese-ui và vietnamese-validation |

## Các phần cần thông tin hoặc người thật

Họ tên, mã sinh viên, lớp, giảng viên; hạn/kênh nộp; mẫu chính thức; đăng ký trình bày; video âm thanh thật ba người; xác nhận đóng góp; rà soát cuối và biên nhận nộp vẫn cần thực hiện. Người dùng đã chọn bố cục tự chỉnh và để trống thông tin cá nhân.

## Quy ước

`[x]` là sản phẩm đã tạo và đã qua phép kiểm tra được mô tả. `[ ]` là phần còn thiếu, kể cả khi phần kỹ thuật trong cùng gói đã xong. Kiểm tra tự động không thay chữ ký hoặc việc trình bày của thành viên. Log cũ giữ ngày gốc; không coi là lần kiểm tra mới sau khi sửa mã. Các PDF/slide và ảnh mới cần được dựng lại từ nội dung tiếng Việt.

## Hoàn tất bản tiếng Việt ngày 29/10/2026

Kiểm tra mới đạt: 42 tệp PHP đúng cú pháp, 28 bài PHPUnit/78 xác nhận, Composer nghiêm ngặt, 7/7 nhóm tích hợp, 11 nhóm thao tác giao diện, đăng nhập chậm và hồi quy trình duyệt. Báo cáo Word/PDF và 12 slide đã dựng lại; các gói A/B/C được cập nhật. Số trang và SHA256 lấy từ sản phẩm cuối trong manifest, không dùng số bản cũ.

## Dọn giao diện theo phản hồi ngày 29/10/2026

Bỏ lời chào/cột thông tin/hàng công cụ thường trực khỏi phòng chat. Đưa thành viên, xuất TXT, sáng/tối, thu gọn và trợ giúp vào menu ⋯. Tìm kiếm chỉ mở khi bấm kính lúp hoặc Ctrl K. Trả lời/Sao chép chỉ hiện khi hover, focus bàn phím hoặc chạm tin. Ô soạn gom emoji/nội dung/Gửi trên một hàng, bộ đếm chỉ hiện từ 400 ký tự. Kiểm tra thao tác và kích thước/cảm ứng ghi tại `evidence/logs/ui-cleanup.json`; log bản trước giữ nguyên.
