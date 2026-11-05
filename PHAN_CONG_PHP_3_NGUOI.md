# Phân công xây dựng website chat WebSocket bằng PHP cho 3 người

**Bản cập nhật ngày 22/10/2026**, đối chiếu trực tiếp đủ 11 trang của `503073-Midterm.pdf` tại `C:\Users\tyranno\Documents\output_submisson\503073-Midterm.pdf` và kế hoạch `ProjectPlan-WebSockets-Redesigned.docx`. Đề tài: **Topic 9 - WebSockets and Real-Time Communication**, sản phẩm minh họa: **PHP Real-Time Multi-Room Chat**.

Tài liệu chia việc cho **3 thành viên A, B, C**; thay ký hiệu bằng họ tên và MSSV thật trước khi dùng. Các đường dẫn kỹ thuật là **file dự kiến cần tạo**, không phải xác nhận đã có code hoặc đã kiểm thử. Tại thời điểm rà soát, thư mục dự án chỉ có bản phân công và kế hoạch DOCX; chưa có mã nguồn ứng dụng.

**Thứ tự đối chiếu:** PDF đề bài là nguồn yêu cầu và thang điểm; DOCX là kế hoạch tham khảo; các mục tiêu chi tiết dưới đây là cam kết nội bộ của nhóm. **Cập nhật theo yêu cầu ngày 28/10/2026, hoàn tất ngày 29/10/2026:** giao diện, README, báo cáo, slide và lời chuẩn bị trình bày được chuyển sang tiếng Việt. Các dòng mô tả quy định tiếng Anh của PDF bên dưới vẫn là quy định gốc để đối chiếu trước khi nộp; không phải ngôn ngữ của bản bàn giao hiện tại.

**Cách dùng:** xem mục 0 để biết điểm và quy định; mục 1-6 để triển khai PHP; mục 7 và 10 để nhận việc theo ngày; mục 11-12 để viết báo cáo và so sánh; mục 13 để nghiệm thu; mục 14-16 để quay video, đóng gói và theo dõi đóng góp.

## 0. Quy định gốc và ma trận bám thang điểm

### 0.1 Yêu cầu chính thức đã xác minh từ PDF

| Nội dung | Quy định trong đề | Việc nhóm phải làm | Người giữ việc |
| --- | --- | --- | --- |
| Chủ đề và demo | Topic 9 ở trang 5-6: HTTP request-response và kết nối WebSocket lâu dài; use case realtime; event, broadcasting, server push; authentication, rate limiting, concurrent connections | Mỗi ý đều có phần lý thuyết, vị trí trong kiến trúc hoặc giải thích giới hạn, và minh chứng demo tương ứng | A + C; B quay minh chứng |
| Thời gian học phần | Hoàn thành trong 8 tuần; đăng ký trình bày đầu học kỳ; mọi thành viên thuyết trình trong tuần 9-10 của lớp lý thuyết (trang 1) | A kiểm tra lịch lớp/LMS và tình trạng đăng ký; lịch 7 ngày ở mục 7 chỉ là kế hoạch nước rút nội bộ | A |
| Quy mô | Nhóm 2-3 người; nhóm 1 người bị trừ 0,5 điểm; trường hợp thành viên không thể tiếp tục cần báo giảng viên (trang 11) | Điền đủ 3 người, chia cả code/nghiên cứu/thuyết trình, lưu bằng chứng đóng góp | C theo dõi; cả nhóm xác nhận |
| Công nghệ | Tự chọn ngôn ngữ/framework/công cụ, miễn minh họa tốt chủ đề (trang 1) | Dùng PHP cho server và JS cho trình duyệt; không xem PHP hay Docker là bắt buộc của đề | A |
| Báo cáo | Word **và** PDF, theo template của Khoa; **20-60 trang**, không tính bìa và phụ lục; có trích dẫn, hình/bảng/sơ đồ thích hợp (trang 7-8) | Mục tiêu nội bộ 26-30 trang phần nội dung chính; kiểm tra số trang trên bản PDF cuối | A tổng hợp; B rà hình thức |
| Ngôn ngữ | Báo cáo hoặc video không bằng tiếng Anh nằm trong nhóm lỗi có thể trừ **0,5-1,0 điểm mỗi trường hợp** (trang 11) | Bản chuẩn bị hiện tại bằng tiếng Việt theo yêu cầu; đối chiếu chính sách ngôn ngữ trước khi ghi/nộp chính thức | Cả nhóm |
| Video | Tối đa **20 phút**; có lý thuyết, kiến trúc, demo chạy thật, giải thích code/thiết kế; mỗi người đều tham gia (trang 8) | Mục tiêu nội bộ 14 phút, có âm thanh rõ, đủ A/B/C trực tiếp trình bày | B dựng; A + C tự trình bày |
| Source và README | Đủ source, dependency file, thư mục rõ; README có yêu cầu hệ thống, lệnh cài/chạy/test đã được thử (trang 8-9) | Nộp source chạy được, `composer.json`, `composer.lock`, schema/seed, Compose, README và tài khoản demo | A đóng gói; C chạy thử độc lập |
| Hạn nộp | Trừ **1 điểm mỗi ngày trễ**, trễ một giây cũng tính một ngày (trang 11) | Điền hạn nộp thực từ LMS; chốt gói trước hạn 24 giờ theo mục 15, giữ xác nhận nộp thành công | A nộp; B kiểm tra |
| Template | PDF dẫn tới `Report templates and guidelines.zip` (trang 10); DOCX nhắc `Template_VN.dotx` | A lấy đúng bộ template của lớp. Chưa thấy bộ template trong hai thư mục đã kiểm tra; chưa chốt font/lề/style trích dẫn khi chưa có mẫu | A lấy mẫu; B kiểm tra |

**Chưa có thông tin để tự điền:** họ tên/MSSV, mã nhóm/lớp, giảng viên, template chính thức, ngày giờ và kênh nộp, lịch thuyết trình cụ thể. Ghi `CHƯA XÁC NHẬN` trong bảng theo dõi nội bộ; phải thay bằng dữ liệu thật trước khi xuất bản nộp. Không suy ra hạn nộp từ ngày cập nhật tài liệu này.

### 0.2 Bảy tiêu chí chấm điểm và minh chứng phải tạo

Các điểm dưới đây giữ nguyên từ rubric trang 9-10. Mã **R1-R4/D1-D3** do nhóm đặt để truy vết. Checklist chi tiết là cách nhóm thực hiện tiêu chí, **không phải các điểm thành phần do giảng viên công bố**. Đủ checklist không bảo đảm được điểm tuyệt đối.

| Mã | Tiêu chí chính thức | Điểm tối đa | Nội dung cụ thể hướng tới mức Full Score | Minh chứng cần bàn giao | Chủ trì / người kiểm tra |
| --- | --- | --- | --- | --- | --- |
| **R1** | Hiểu lý thuyết | **2,0** | Giải thích sâu nguyên lý, lịch sử, HTTP/WS, opening handshake, frame/control frame, event, broadcast, server push, vòng đời kết nối, bảo mật và đồng thời; gắn ví dụ với app | Chương 2; sơ đồ HTTP Upgrade, vòng đời; bảng khái niệm -> code -> test; nguồn kỹ thuật | A chủ trì; C viết vận hành/đồng thời; B kiểm tra tính dễ hiểu |
| **R2** | Phân tích so sánh | **1,0** | So sánh đúng và có nhận xét ưu/nhược giữa short polling, long polling, SSE, WebSocket; giải thích khi nào không nên dùng WS; dùng kết quả đo thật để thảo luận lựa chọn | Bảng so sánh nhiều tiêu chí; phép đo polling/WS có phương pháp, raw data, biểu đồ và hạn chế ở chương 2/5 | B chủ trì phân tích/trình bày; C đo; A rà tính đúng |
| **R3** | Ứng dụng và triển khai | **1,0** | Mô tả chi tiết cách áp dụng lý thuyết vào cấu trúc, thành phần và kết quả demo; giải thích các quyết định PHP/WS/DB và lỗi tích hợp đã xử lý | Chương 3-5; sơ đồ kiến trúc, ERD, sequence; contract API/event; bảng kết quả test | C chủ trì thiết kế; A viết HTTP/auth; B viết client |
| **R4** | Cấu trúc và trình bày báo cáo | **2,0** | Bố cục logic theo template; bìa và thông tin chính xác; tiếng Anh rõ; hình/bảng có caption và được phân tích; tham khảo và Word/PDF đồng nhất | `report.docx`, `report.pdf`; checklist bìa/format, danh mục hình/bảng, trích dẫn và phụ lục | A tổng hợp; B rà trình bày; C rà nội dung/nguồn |
| **D1** | Phạm vi và chi tiết demo | **2,0** | Demo đủ chiều sâu: chat hai chiều nhiều phòng, xác thực, lưu lịch sử, presence/typing, reconnect/cleanup, giới hạn và kiểm thử đồng thời | Source chạy được; TC-01..TC-20; thao tác nhiều client trong video; test log | C chủ trì realtime; A auth/hạ tầng; B giao diện |
| **D2** | Trình diễn rõ ràng | **1,0** | Demo theo một luồng rõ, thao tác đi cùng giải thích; thấy dữ liệu/khung WS/kiến trúc thay vì chỉ liệt kê tính năng | Kịch bản timestamp, sơ đồ, ảnh DevTools, kết quả trước/sau và demo chạy thật | B chủ trì kịch bản; A + C giải thích phần mình |
| **D3** | Kỹ năng video và thuyết trình | **1,0** | Cả ba nói tiếng Anh, giải thích cơ chế và phần đã làm; video có tiếng rõ, đọc được UI/code, có kết quả và kết luận | `demo.mp4` đã mở lại kiểm tra toàn bộ; slide tiếng Việt; biên bản diễn tập và kiểm tra âm thanh | B quay/dựng; A + C kiểm tra bản cuối |
| | **Tổng REPORT** | **6,0** | R1 + R2 + R3 + R4 | | |
| | **Tổng DEMO** | **4,0** | D1 + D2 + D3 | | |
| | **TỔNG CỘNG** | **10,0** | Điểm nhóm do giảng viên đánh giá | | |

### 0.3 Hiểu các mức điểm để tự rà

| Mức trong rubric | Ý nghĩa cần kiểm tra | Cách khắc phục trước khi nộp |
| --- | --- | --- |
| 0 | Thiếu nội dung/sản phẩm tương ứng, demo không chạy, hoặc mắc lỗi nghiêm trọng được nêu trong tiêu chí | Kiểm tra điều kiện ở mục 0.4 trước mọi chỉnh sửa thẩm mỹ |
| 25% | Lý thuyết nông, so sánh mơ hồ/sai, mô tả demo sơ sài hoặc thuyết trình khó hiểu | Thêm giải thích cơ chế, ví dụ đúng, kiến trúc và demo hoạt động |
| 50%-75% | Có các ý chính nhưng còn thiếu chiều sâu, phân tích, minh chứng hoặc trình bày nhất quán | Bổ sung số liệu/đối chiếu, lý do chọn giải pháp, ảnh/sơ đồ được diễn giải và kết quả test |
| Full Score | Sâu, chính xác, đầy đủ, có phân tích và sản phẩm rõ ràng theo từng tiêu chí | Người kiểm tra mở được minh chứng và giải thích được kết luận, không chỉ đánh dấu “đã viết” |

Nhóm chỉ tự đánh dấu `CHƯA CÓ / CẦN BỔ SUNG / ĐỦ MINH CHỨNG`; không tự ghi điểm dự kiến như thể đã được giảng viên chấm.

### 0.4 Các điều kiện mất điểm phải chặn trước

| Rủi ro được đề nêu | Hệ quả theo PDF | Cách kiểm tra bắt buộc trong nhóm |
| --- | --- | --- |
| Thiếu video demo có âm thanh **hoặc** thiếu source đầy đủ kèm hướng dẫn cài chạy | **Toàn bộ phần DEMO = 0/4** (trang 10) | B mở/nghe video sau khi giải nén gói cuối; C cài source theo README trên môi trường sạch |
| Không nộp báo cáo hoặc bìa sai nghiêm trọng: logo biến dạng/sai màu, sai trường/khoa/giảng viên/thành viên/đề tài/môn | Có thể rơi vào mức **0 của R4**, tức mất tối đa 2 điểm tiêu chí này (trang 10) | A đối chiếu thông tin lớp; B giữ tỷ lệ logo theo mẫu; C kiểm tra chéo từng tên/MSSV |
| Báo cáo/video không bằng tiếng Anh; thiếu người trong video khi chưa được chấp thuận; âm thanh/hình kém; chia việc không đều; sai định dạng/thiếu thông tin hoặc tài khoản cần dùng | Có thể trừ **0,5-1,0 điểm mỗi trường hợp** (trang 11) | Kiểm tra ngôn ngữ, phân đoạn A/B/C, chất lượng video, nhật ký đóng góp, manifest, README và tài khoản demo |
| Nộp trễ | **-1 điểm/ngày**, kể cả trễ một giây (trang 11) | Đặt hạn nội bộ sớm; A tải nộp và kiểm tra lại tệp trên hệ thống, B giữ bằng chứng xác nhận |

PDF không quy định mỗi tính năng chat được bao nhiêu điểm, không quy định bắt buộc public hosting, không chốt số slide hay video phải dài 14 phút. Những lựa chọn đó, số test, số trang từng chương và định mức giờ phía dưới là **kế hoạch nội bộ**.

## 1. Phạm vi và công nghệ thống nhất

Xây dựng website chat thời gian thực gồm: đăng nhập bằng tài khoản demo, chọn 1 trong 3 phòng, tải tối đa 50 tin nhắn gần nhất, gửi và nhận tin trực tiếp, danh sách người online, thông báo đang nhập, tự kết nối lại và lưu lịch sử sau khi khởi động lại server.

Không làm đăng ký, gửi file, chat riêng hoặc chạy nhiều máy chủ trong đợt này.

| Thành phần | Lựa chọn triển khai |
| --- | --- |
| HTTP API, đăng nhập, nghiệp vụ | PHP 8.3+, tổ chức controller/service/repository, Composer autoload |
| Giao diện | View `.php`, HTML, CSS và JavaScript thuần |
| WebSocket server | PHP CLI chạy Workerman 5.x, **1 worker WebSocket** |
| Database | SQLite qua PDO; bật foreign keys, WAL và busy timeout |
| Chạy chung cho cả nhóm | Docker Compose: `nginx`, `app` chạy PHP-FPM, `ws` chạy PHP CLI |
| Kiểm thử | PHPUnit tương thích phiên bản PHP đã chọn; checklist bằng 2 trình duyệt và kiểm tra nhiều socket |

**Chuyển đổi so với tài liệu gốc:** thay Node.js/Express/ws/TypeScript/Vite/Vitest bằng PHP/Workerman/Composer/PHPUnit. Toàn bộ xử lý phía server viết bằng PHP. Trình duyệt vẫn cần JavaScript để dùng WebSocket API, cập nhật giao diện và reconnect; không cần Node.js để build hoặc chạy ứng dụng.

Chọn Docker Linux làm môi trường chuẩn, kể cả khi máy cá nhân dùng Windows. Không dùng riêng Apache/XAMPP để thay cho tiến trình WebSocket chạy liên tục.

**Ưu tiên phạm vi:** P0 là quy định nộp và chức năng cốt lõi; P1 là minh chứng chiều sâu (so sánh có số liệu, nhiều kết nối, reconnect, bảo mật); P2 là trang trí/mở rộng chỉ làm sau khi P0/P1 đã nghiệm thu. Giữ các tính năng đã chốt; không tự mở thêm chat riêng/upload để chạy theo điểm mà làm thiếu báo cáo/video.

## 2. Chia 3 phần chính

| Thành viên | Phần chịu trách nhiệm chính | Sản phẩm phải bàn giao |
| --- | --- | --- |
| **A — HTTP, tài khoản và nền dự án** | Cấu hình, router, API đăng nhập/đăng xuất, phiên, API phòng và lịch sử; đóng gói Docker/Nginx | API chạy thật, xác thực dùng chung cho HTTP/WS, môi trường chạy và README |
| **B — Trang web và trải nghiệm chat** | View PHP, CSS responsive, gọi API, kết nối WebSocket bằng JS, hiển thị chat/online/typing/reconnect | Website thao tác được từ đăng nhập đến chat, checklist giao diện và hình demo |
| **C — Database và chat realtime** | Schema/seed, repository dữ liệu chat, Workerman, phòng, broadcast, presence, typing, giới hạn gửi | Server chat PHP có lưu dữ liệu, cô lập phòng, kiểm thử realtime và sơ đồ kỹ thuật |

Mỗi người phụ trách trọn phần của mình, gồm code, kiểm thử và tài liệu liên quan. A phụ trách tích hợp HTTP/deployment; C phụ trách tích hợp WebSocket; B kiểm tra luồng sử dụng thực tế.

### Thành viên A — HTTP, đăng nhập và cấu hình

- [ ] Tạo skeleton, Composer PSR-4 namespace `App\`, file môi trường mẫu và bộ kiểm thử.
- [ ] Dựng `public/index.php`, router và bootstrap dùng lại được cho PHP-FPM lẫn PHP CLI; không chạy tác vụ HTTP khi WS nạp bootstrap.
- [ ] Viết API login/me/logout; dùng `password_hash()` và `password_verify()`.
- [ ] Tạo `SessionService` dùng chung: tạo phiên, xác minh cookie, kiểm tra hết hạn và thu hồi phiên.
- [ ] Viết middleware yêu cầu đăng nhập và kiểm tra CSRF cho request thay đổi dữ liệu.
- [ ] Viết API danh sách phòng và lịch sử, gọi repository do C cung cấp.
- [ ] Chuẩn hóa HTTP status, JSON thành công/lỗi và không trả password hash/token phiên trong response.
- [x] Tạo Dockerfile, Compose, Nginx proxy `/ws`, volume SQLite; ghi cách chạy vào README.
- [ ] Chốt PHP image và dependency tương thích; kiểm tra các extension cần dùng như `pdo_sqlite`, `mbstring` và yêu cầu chạy Workerman trên Linux; khóa phiên bản bằng lockfile.
- [ ] Test đăng nhập sai/đúng, hết phiên, CSRF, logout và HTTP API cần đăng nhập.

**Hoàn thành khi:** B gọi được toàn bộ API; C xác minh được cùng cookie bằng `SessionService`; chạy dự án theo README trên môi trường sạch.

### Thành viên B — Giao diện PHP và client

- [ ] Làm `login.php`, `chat.php`, layout chung và CSS cho desktop/mobile.
- [ ] Tạo form đăng nhập, hiển thị lỗi và chuyển sang màn hình chat khi thành công.
- [ ] Hiển thị 3 phòng, phòng hiện tại, tối đa 50 tin gần nhất, người gửi và thời gian.
- [ ] Viết `api.js`: gọi cùng origin, gửi cookie và CSRF token, xử lý `401`/lỗi mạng.
- [ ] Viết `socket.js`: connect, nhận/gửi event, reconnect theo khoảng 1–2–4–8–10 giây và vào lại phòng.
- [ ] Hiển thị trạng thái đang kết nối/đã kết nối/mất kết nối; vô hiệu hóa nút gửi khi socket chưa sẵn sàng.
- [ ] Hiển thị online/đang nhập; gửi trạng thái ngừng nhập khi đổi phòng hoặc rời màn hình.
- [ ] Chống XSS: nội dung chat dùng `textContent`; biến từ PHP dùng `htmlspecialchars()` hoặc JSON encode phù hợp.
- [ ] Gộp lịch sử và tin realtime theo `message.id`, sắp xếp tăng dần, không mất hoặc lặp tin khi vào lại phòng.
- [ ] Logout phải đóng socket và dừng reconnect; không tự gửi lại tin chưa rõ đã được lưu hay chưa.
- [ ] Kiểm tra bằng 2 trình duyệt; chụp màn hình, chuẩn bị slide và lời dẫn demo.

**Hoàn thành khi:** mở hai trình duyệt, đăng nhập hai tài khoản, vào cùng phòng và chat được; đổi phòng, mất mạng, đăng xuất đều có trạng thái rõ ràng.

### Thành viên C — Database và WebSocket PHP

- [ ] Tạo schema, seed 3 tài khoản demo và 3 phòng; chạy seed lại không sinh bản ghi trùng.
- [ ] Viết kết nối PDO và repository users/rooms/messages theo interface ở mục 5.
- [ ] Tạo `bin/ws-server.php`, cấu hình 1 worker; dùng thư viện xử lý RFC 6455, không tự viết handshake/frame.
- [ ] Kiểm tra đường dẫn `/ws`, Origin và phiên hợp lệ trước khi chấp nhận kết nối.
- [ ] Viết `room:join`, chuyển phòng, xác nhận `room:joined`, broadcast trong đúng phòng.
- [ ] Lưu tin thành công trước khi phát `chat:new`; lấy danh tính từ phiên, không tin `userId` client gửi.
- [ ] Quản lý presence theo tài khoản; một người mở nhiều tab chỉ hiện một lần.
- [ ] Quản lý typing theo socket và gom theo tài khoản; tự xóa sau 3 giây không cập nhật.
- [ ] Giới hạn nội dung 500 ký tự Unicode, JSON 8 KiB và 20 tin/phút/tài khoản trên toàn bộ socket của tài khoản đó.
- [ ] Heartbeat ping/pong, dọn socket chết, xóa membership/typing khi disconnect hoặc đổi phòng.
- [ ] Kiểm tra thu hồi/hết hạn phiên trước mỗi event và định kỳ mỗi giây; đóng socket không còn phiên hợp lệ.
- [x] Viết test repository, validation, giới hạn gửi, broadcast/cô lập phòng; sơ đồ kiến trúc và HTTP Upgrade.

**Hoàn thành khi:** tin được lưu sau restart, phòng khác không nhận nhầm, client chưa đăng nhập bị chặn, logout đóng được mọi socket của phiên đã thu hồi trong khoảng 1 giây.

## 3. Quyền phụ trách file để hạn chế xung đột

```text
GK-WEB/
├── composer.json, composer.lock                 # A
├── .env.example, .gitignore, phpunit.xml         # A
├── Dockerfile, docker-compose.yml, README.md    # A
├── config/app.php                              # A
├── bootstrap/app.php                           # A
├── public/
│   ├── index.php                               # A: front controller/router
│   └── assets/
│       ├── css/app.css                         # B
│       └── js/{app,api,socket,chat}.js          # B
├── resources/views/
│   ├── layout.php, login.php, chat.php          # B
├── src/
│   ├── Http/{Router,JsonResponse}.php          # A
│   ├── Http/Controllers/
│   │   ├── AuthController.php                  # A
│   │   ├── RoomController.php                  # A
│   │   └── PageController.php                  # A: render view của B
│   ├── Http/Middleware/{RequireUser,Csrf}.php   # A
│   ├── Auth/{Password,SessionService}.php      # A
│   ├── Repositories/SessionRepository.php     # A
│   ├── Database/Connection.php                # C
│   ├── Repositories/
│   │   ├── UserRepository.php                  # C
│   │   ├── RoomRepository.php                  # C
│   │   └── MessageRepository.php               # C
│   ├── Services/ChatService.php                # C
│   ├── Realtime/
│   │   ├── Gateway.php, EventHandler.php       # C
│   │   └── RoomHub.php, RateLimiter.php        # C
│   └── Shared/{Protocol,Validator}.php         # C: nội dung cả nhóm chốt ngày 1
├── database/{schema.sql,seed.php}               # C
├── bin/ws-server.php                           # C
├── storage/                                    # A: tạo thư mục và quyền ghi
├── test-data/{accounts,messages}.json           # C
├── tests/
│   ├── Http/, Auth/                            # A
│   ├── Realtime/, Database/                    # C
│   └── manual-acceptance.md                    # B
├── deploy/nginx.conf                           # A
├── docs/
│   ├── contracts.md                            # A: chủ file, cả nhóm duyệt
│   ├── architecture.mmd, erd.mmd               # C
│   ├── sequences.mmd, test-cases.md            # C
│   ├── theory-notes.md, references.md          # A; B/C góp nguồn
│   ├── comparison.md                           # B: bảng và phân tích
│   ├── experiment-plan.md                     # C: phương pháp đo
│   ├── worklog.md, rubric-evidence.md          # C: tiến độ/minh chứng
│   ├── report-review.md                        # B: checklist hình thức
│   ├── demo-script.md                          # B
│   └── report-outline.md                       # A: tổng hợp nội dung 3 người
├── tools/benchmark/                            # A: polling đối chứng; C: WS runner
├── evidence/
│   ├── screenshots/                            # B: ảnh thực tế có mã minh chứng
│   ├── raw/, logs/                             # C: dữ liệu đo và log đã bỏ bí mật
│   └── charts/                                 # B: biểu đồ từ dữ liệu thật
└── submission/                                 # Cả nhóm, phân công tại mục 8
```

Người phụ trách được quyền sửa chính; thay đổi file của người khác phải báo trước. A quản lý mọi thay đổi dependency và lockfile. C quản lý schema, kể cả bảng sessions theo yêu cầu của A. Không để cả ba tự tạo router, kết nối DB hoặc format event riêng.

## 4. Kiến trúc chạy và xác thực dùng chung

```text
Trình duyệt → Nginx tại http://localhost:8080
              ├── /, /assets, /api → PHP-FPM (A và view của B)
              └── /ws             → PHP CLI Workerman (C)
PHP-FPM và Workerman → cùng file /app/storage/chat.sqlite
```

- Nginx giữ cùng origin cho HTTP và WebSocket; chuyển tiếp `Upgrade`, `Connection`, cookie và Origin. Không công khai cổng WS nội bộ trong bản triển khai.
- PHP-FPM và Workerman là hai tiến trình riêng. Không dùng biến toàn cục PHP hoặc session chỉ lưu trong RAM để chia sẻ đăng nhập giữa hai bên.
- Bảng `sessions` là nguồn xác thực chung. Cookie `chat_session` chứa token ngẫu nhiên và chữ ký HMAC; DB chỉ lưu hash token. Cả hai tiến trình dùng cùng `SESSION_SECRET` và `SessionService` của A.
- Cookie dùng `HttpOnly`, `SameSite=Lax`, `Path=/`, thời hạn 8 giờ; bật `Secure` khi dùng HTTPS. Không đưa token phiên vào localStorage hoặc URL WebSocket.
- Login và logout kiểm tra Origin/CSRF theo cơ chế A thống nhất; `GET /api/auth/me` trả CSRF token cho client, kể cả phiên chưa đăng nhập. Token CSRF không phải token đăng nhập.
- Logout thu hồi phiên trong SQLite trước khi xóa cookie. WS kiểm tra mỗi giây để đóng các socket của phiên đó; kiểm tra lại trước mỗi event và trước khi phát tin tới socket. B chủ động đóng socket ngay khi người dùng logout.
- Mỗi tiến trình tự mở PDO tới cùng database; dùng đường dẫn tuyệt đối, transaction ngắn và không giữ khóa SQLite trong lúc gửi mạng. Volume phải chứa cả thư mục SQLite để giữ được file WAL/SHM.
- Presence, typing, membership và rate limit ở RAM của **một** worker WS. Không tăng số worker khi chưa thiết kế chia sẻ trạng thái.

Biến môi trường A phải cung cấp: `APP_ENV`, `APP_URL`, `DATABASE_PATH`, `SESSION_SECRET`, `ORIGIN_ALLOWLIST`, `WS_HOST`, `WS_PORT`. B lấy URL WS từ cấu hình trang, đổi `ws://` sang `wss://` khi trang chạy HTTPS.

## 5. Hợp đồng cần chốt ngay ngày 1

### 5.1 Database và hàm dùng chung

| Bảng | Các cột chính |
| --- | --- |
| `users` | `id`, `username` UNIQUE, `display_name`, `password_hash` |
| `rooms` | `id`, `name` UNIQUE |
| `messages` | `id`, `room_id` FK, `user_id` FK, `content`, `created_at` |
| `sessions` | `id`, `user_id` FK nullable khi chưa login, `token_hash` UNIQUE, `csrf_token`, `expires_at`, `revoked_at` |

C tạo index `messages(room_id, id)`; A và C thống nhất cách tạo/đổi phiên ẩn danh sang phiên đăng nhập, luôn đổi token sau login. Không lưu mật khẩu thô trong DB; tài khoản giả lập trong test-data chỉ dùng demo.

| Chủ sở hữu | Hàm cần cung cấp cho người còn lại |
| --- | --- |
| C | `Connection::open(): PDO`; `UserRepository::findByUsername(string): ?array` |
| C | `RoomRepository::all(): array`; `RoomRepository::exists(int): bool` |
| C | `MessageRepository::latest(int $roomId, int $limit = 50): array` |
| C | `MessageRepository::create(int $userId, int $roomId, string $content): array` |
| A | `SessionService::resolveCookie(?string $cookie): ?array` trả phiên và user đã xác minh, hoặc null |
| A | `SessionService::isActive(int $sessionId): bool`; `SessionService::revoke(int $sessionId): void` |

Các hàm repository dùng tham số SQL, không ghép chuỗi input. `latest()` chọn 50 bản ghi mới nhất theo ID rồi trả thứ tự ID tăng dần. Dùng múi giờ UTC ISO 8601 trong API; B chuyển sang giờ địa phương khi hiển thị.

### 5.2 HTTP API do A cung cấp

Tất cả response JSON có dạng `{ "data": ... }` hoặc `{ "error": { "code": "...", "message": "..." } }`. ID là số nguyên; không đổi tên trường riêng ở frontend.

| Method và endpoint | Request | Response `data` / yêu cầu |
| --- | --- | --- |
| `GET /api/auth/me` | Cookie nếu có | `{user: null hoặc User, csrfToken: "..."}`; tạo phiên ẩn danh nếu cần |
| `POST /api/auth/login` | `{username, password}` và `X-CSRF-Token` | `{user: User, csrfToken: "..."}` + cookie phiên mới |
| `POST /api/auth/logout` | `X-CSRF-Token` | `{ok: true}`; thu hồi phiên |
| `GET /api/rooms` | Phiên đăng nhập | `[{id, name}]` |
| `GET /api/rooms/{id}/messages?limit=50` | Phiên đăng nhập | `Message[]`, giới hạn `limit` trong 1–50 |
| `GET /api/health` | Không cần đăng nhập | `{status: "ok"}` khi DB truy cập được; lỗi dịch vụ trả 503 |

`User = {id, username, displayName}`. `Message = {id, roomId, user: User, content, createdAt}`. Lỗi dùng `400` dữ liệu sai, `401` chưa đăng nhập, `403` CSRF/Origin sai, `404` phòng không tồn tại, `429` vượt giới hạn. Login sai luôn trả thông báo chung.

### 5.3 Event WebSocket do C cung cấp và B sử dụng

Envelope thống nhất: `{ "type": "tên:event", "payload": { ... } }`. Chỉ nhận JSON object đúng schema, không nhận nội dung nhị phân hoặc event lạ.

| Hướng | Event | `payload` |
| --- | --- | --- |
| Client → server | `room:join` | `{roomId: 1}` |
| Server → client | `room:joined` | `{roomId: 1}`; xác nhận đã đăng ký nhận tin phòng |
| Client → server | `chat:send` | `{roomId: 1, content: "Xin chào"}` |
| Server → client | `chat:new` | `Message` cùng cấu trúc với HTTP |
| Client → server | `typing:set` | `{roomId: 1, isTyping: true}` |
| Server → client | `room:presence` | `{roomId: 1, users: User[]}`; danh sách đầy đủ |
| Server → client | `room:typing` | `{roomId: 1, users: User[]}`; danh sách đầy đủ |
| Server → client | `system:error` | `{code: "RATE_LIMITED", message: "..."}` |

`room:joined` là bổ sung của kế hoạch PHP để đồng bộ lúc tải lịch sử. Các mã lỗi dùng chung: `UNAUTHORIZED`, `INVALID_PAYLOAD`, `ROOM_NOT_FOUND`, `NOT_IN_ROOM`, `MESSAGE_TOO_LONG`, `PAYLOAD_TOO_LARGE`, `RATE_LIMITED`, `INTERNAL_ERROR`.

Quy tắc xử lý bắt buộc:

1. Mỗi socket tham gia một phòng tại một thời điểm; join lại cùng phòng không tạo membership trùng. Đổi phòng phải xóa presence/typing cũ và broadcast lại hai phòng.
2. Khi mở phòng/reconnect: B gửi `room:join`, nhận `room:joined`, đệm các `chat:new` rồi gọi HTTP lấy lịch sử; gộp theo ID để đồng bộ snapshot và event đang nhận. B bỏ response HTTP cũ nếu người dùng đã đổi phòng. Bản này chỉ tải 50 tin mới nhất: nếu mất mạng lâu và bỏ lỡ hơn 50 tin, không cam kết phục hồi toàn bộ khoảng trống; nêu rõ giới hạn ở báo cáo/UI. Không gọi cơ chế này là bảo đảm không mất tin hoặc exactly-once delivery.
3. Server chỉ nhận `chat:send`/`typing:set` cho phòng socket đang tham gia. `chat:new` gửi cho mọi socket trong phòng, gồm cả người gửi.
4. Server trim nội dung và từ chối chuỗi rỗng; giới hạn 500 ký tự Unicode bằng `mb_strlen`, JSON message tối đa 8192 byte. C cấu hình cả giới hạn frame/buffer và kiểm tra kích thước message sau ghép frame.
5. Giới hạn 20 tin chat hợp lệ trong cửa sổ trượt 60 giây theo tài khoản, tính cả các tab. Client chỉ chặn gửi để hỗ trợ UX; server là nơi quyết định.
6. B gửi typing có tiết lưu, ngừng sau 3 giây hoặc khi gửi tin; C tự hết hạn trạng thái để không treo. Heartbeat dọn socket chết trong tối đa khoảng 60 giây.

## 6. Cách làm song song và tích hợp

- **Đầu ngày 1:** cả nhóm chốt mục 5; A tạo `docs/contracts.md`, C chốt schema, B chốt bố cục. Thay đổi contract sau đó phải báo cả hai người còn lại trước khi merge.
- **B chưa có API/WS thật:** dùng mock có cùng JSON trong `test-data`; tách transport khỏi render để đổi sang server thật không phải viết lại giao diện.
- **A chưa có repository thật:** dùng stub đúng interface mục 5.1; không tự tạo phiên bản DB riêng.
- **C chưa có login thật:** test gateway bằng phiên giả lập trong DB test; không mở chế độ bỏ xác thực ở bản demo.
- Mỗi ngày dành một lượt ghép chạy thật: A kiểm tra API, C kiểm tra socket, B chạy luồng hai trình duyệt. Ghi lỗi và chủ xử lý vào `docs/test-cases.md`.
- Nhánh: `feat/http-auth` của A, `feat/chat-ui` của B, `feat/realtime-db` của C; merge vào `dev`, bản ổn định vào `main`. Dùng commit `feat:`, `fix:`, `test:`, `docs:`.

## 7. Lịch thực hiện trong 7 ngày

Đây là kế hoạch nước rút kế thừa từ DOCX, không thay thời hạn 8 tuần của học phần. Bắt đầu nghiên cứu/viết từ ngày 1; không để toàn bộ 6 điểm báo cáo tới ngày 6. Mỗi ngày dự kiến khoảng 5 giờ/người; chi tiết gói việc và thời lượng ở mục 10. Nếu thời gian thực tế khác, cập nhật mốc với hạn nộp thật trước khi nhận việc.

| Ngày | A | B | C | Mốc tích hợp và tài liệu phải có cuối ngày |
| --- | --- | --- | --- | --- |
| 1 | Skeleton, Composer, Docker nền; lấy template, xác minh thông tin lớp/hạn nộp, dàn ý và nguồn | Wireframe/mock; mục tiêu/đối tượng, khung so sánh | Schema/seed, WS skeleton; khung ERD, kế hoạch test/đo | Chốt contract; trang/WS skeleton chạy (chưa mở WS bỏ auth cho demo); dàn ý 5 chương và bảng minh chứng có chủ việc |
| 2 | Login/me/logout/CSRF, phiên chung; lý thuyết HTTP/Upgrade | Login/rooms UI; viết chương 1, bảng so sánh nháp | Repository và WS xác minh Origin/phiên; viết luồng dữ liệu | Login -> WS bằng cùng cookie; ERD + sequence nháp; nguồn được ghi ngay khi dùng |
| 3 | Rooms/history API, test auth; viết bảo mật và polling đối chứng | Join/online/typing, so sánh các công nghệ | Join/presence/typing; thiết kế đo và client nhiều socket | Hai trình duyệt hiện đúng người/typing; chương 2-3 có bản nháp, phương pháp đo được A rà |
| 4 | Hoàn tất API, lỗi; hoàn thiện lý thuyết và phần auth/HTTP trong báo cáo | Chat/gộp lịch sử/reconnect; chụp ảnh đầu, viết client | Lưu/broadcast, rate limit/heartbeat; viết thiết kế/triển khai | Luồng chat đầy đủ; phòng cô lập; chương 1-4 có bản nháp ghép chung |
| 5 | Docker/Nginx, README, kiểm tra bảo mật; rà lý thuyết | Responsive, checklist, chạy demo cho phép đo; vẽ biểu đồ | Test payload/DB/logout, đo polling/WS và concurrent sockets, lưu raw data | `v0.9.0-rc1`; dừng tính năng; kết quả test/đo thật; bản báo cáo đủ 5 chương để rà |
| 6 | Ghép báo cáo theo template, đối chiếu nguồn/Word/PDF; sửa lỗi tích hợp | Hoàn thiện ảnh/so sánh/slide, quay thử có tiếng | Kiểm tra số liệu/sơ đồ, chạy thử README từ bản sạch, manifest nháp | Báo cáo 26-30 trang mục tiêu; 10-12 slide; video thử đủ A/B/C khoảng 14 phút; lỗi còn lại có người nhận |
| 7 | Chốt source/tag, xuất Word/PDF, đóng gói và nộp trước hạn nội bộ | Quay/dựng cuối, mở nghe lại video, rà ảnh/bìa và tệp tải lên | Nghiệm thu cuối, rà minh chứng/manifest, kiểm tra gói đã giải nén | `v1.0.0`; gói nộp mở được/cài được; đủ 7 tiêu chí có minh chứng; có xác nhận nộp |

## 8. Kiểm thử và bàn giao

### Checklist nghiệm thu website

| Kịch bản | Kết quả cần đạt | Chủ kiểm tra |
| --- | --- | --- |
| Đăng nhập sai, thiếu cookie, hết phiên | API riêng/WS từ chối; UI báo đúng trạng thái | A |
| Origin lạ, cookie sửa chữ ký, CSRF sai | Không đăng nhập/gửi request trái phép được | A + C |
| Hai tài khoản cùng phòng | Nhận tin realtime, mỗi message chỉ hiện một lần | B + C |
| Hai tài khoản khác phòng | Không nhận tin/typing/presence của phòng kia | C |
| Một người mở hai tab, đóng một tab | Online vẫn có người đó; đóng tab cuối thì mất | B + C |
| Chuỗi rỗng, 501 ký tự, JSON lỗi, message trên 8 KiB | Từ chối, server không bị dừng | C |
| Tin thứ 21 trong 60 giây, gửi từ nhiều tab | Báo `RATE_LIMITED`, không lưu/broadcast tin bị chặn | C |
| Ngừng gõ, đổi phòng, mất mạng | Typing được xóa; socket chết được dọn | B + C |
| Mất mạng rồi kết nối lại, đổi phòng khi đang tải | Vào lại đúng phòng, gộp lịch sử không lặp/lẫn phòng | B |
| Logout ở một tab khi tab khác giữ cùng phiên | Thu hồi DB; mọi socket của phiên đó đóng trong khoảng 1 giây | A + C |
| Gửi `<script>alert(1)</script>` | Chỉ hiện chữ, không chạy mã | B |
| Restart cả HTTP/WS | Lịch sử còn; client phục hồi phòng sau đăng nhập nếu cần | A + C |
| Clone sạch, Compose, seed, chạy test | Làm đúng README là chạy được, không cần file cá nhân | A |

### Sản phẩm nộp và người tổng hợp

| Sản phẩm | Phân công |
| --- | --- |
| Source, README, Docker, lockfile, ZIP từ tag cuối | A tổng hợp; cả nhóm rà |
| `submission/report.docx`, `submission/report.pdf` | A tổng hợp, **nội dung tiếng Việt theo yêu cầu cập nhật**; phân công từng chương tại mục 11 |
| `submission/slides.pptx`, `submission/slides.pdf` | B tổng hợp; A và C cung cấp phần kỹ thuật |
| `submission/demo.mp4` | B dựng; cả 3 người đều tham gia và giải thích phần mình |
| `submission/manifest.md`, kết quả test, sơ đồ | C tổng hợp |

**Bản cập nhật tiếng Việt:** báo cáo có 5 chương và mục lục cập nhật theo lần xuất; slide 12 trang và ghi chú tiếng Việt. Các định mức độ dài/ngôn ngữ môn học cần đối chiếu trước khi nộp. Video thật chưa ghi; khung luyện tập khoảng 14 phút, tối đa 20 phút và đủ ba người có âm thanh. Đây là định mức nội bộ trong các giới hạn của PDF. **Theo chỉ dẫn mới của người dùng: dùng bố cục tự tạo, để trống thông tin nhóm/lớp/giảng viên để người dùng tự điền/chỉnh mẫu sau.** Không khẳng định bản này đã theo template chính thức của Khoa.

Không commit `.env` thật, `vendor/`, database runtime hoặc dữ liệu cá nhân. Cần commit `composer.lock`, schema, seed, tài khoản giả lập và hướng dẫn tạo `SESSION_SECRET`. Mount volume SQLite có quyền ghi cho cả HTTP và WS; môi trường test dùng DB riêng.

## 9. Việc mỗi người bắt đầu ngay

1. **A:** tạo nền PHP/Composer/Docker, router, `SessionService` và hợp đồng API.
2. **B:** dựng `login.php`, `chat.php`, CSS và client theo mock đúng hợp đồng.
3. **C:** tạo schema/seed/repository và Workerman skeleton có kiểm tra phiên.

Cuối ngày đầu tiên, nhóm phải mở được trang PHP qua Nginx, có database demo và kết nối được tới tiến trình WebSocket. Chưa cần giao diện đẹp hoặc đầy đủ tính năng ở mốc này.

Nguồn kỹ thuật dự kiến để nghiên cứu và xác minh khi triển khai: [RFC 6455](https://www.rfc-editor.org/rfc/rfc6455.html), [Workerman](https://github.com/walkor/workerman), [tài liệu WebSocket của Workerman](https://manual.workerman.net/doc/en/appendices/about-websocket.html), [HTTP Request của Workerman](https://manual.workerman.net/doc/en/http/request.html). Danh sách này không thay cho tài liệu tham khảo đầy đủ trong báo cáo.

## 10. Gói việc cụ thể, phụ thuộc và nghiệm thu từng người

### 10.1 Quy tắc nhận và hoàn thành việc

Mỗi mã việc có một người chịu trách nhiệm cuối cùng. “Người kiểm tra” phải tự mở/chạy/đọc sản phẩm và ghi kết quả; không chỉ nhận lời báo đã xong. Giờ dưới đây là **ước lượng nội bộ**, tổng 105 giờ (35 giờ/người), đã tính code, nghiên cứu, kiểm thử, viết và trình bày; không phải số giờ đã làm hoặc tỷ lệ điểm cá nhân. Cuối mỗi ngày cập nhật giờ thực tế và chuyển bớt việc nếu một người quá tải.

Một việc chỉ chuyển sang `DONE` khi có đủ: sản phẩm đúng cột bàn giao; đạt tiêu chí nghiệm thu; link commit/file/minh chứng thật trong `docs/worklog.md`; người kiểm tra ký tên. Chưa có sản phẩm thì giữ `TODO`, đang vướng phụ thuộc thì ghi `BLOCKED` cùng việc có thể làm trước bằng mock/stub.

### 10.2 A - HTTP, xác thực, lý thuyết nền và tích hợp

| Mã | Việc và sản phẩm bàn giao | Phụ thuộc | Hạn nội bộ / giờ | Tiêu chí nghiệm thu cụ thể | Người kiểm tra / rubric |
| --- | --- | --- | --- | --- | --- |
| A01 **[ ] CÒN PHẦN NGƯỜI THẬT/THÔNG TIN LỚP** | Xác minh thông tin lớp/template/hạn nộp; dàn ý báo cáo; `references.md`, khung `rubric-evidence.md` | PDF và thông tin lớp | N1 / 3h | Từng quy định có nguồn/trang; phần chưa rõ được ghi rõ, không bịa; đủ R1-R4/D1-D3 có chủ việc | B / R4, D3 |
| A02 **[x] ?? XONG** | Composer/autoload/bootstrap/router; Dockerfile/Compose nền; contract chung | Chốt stack; C01 thống nhất schema | N1-2 / 3h | HTTP health chạy; bootstrap CLI không xuất response; B/C dùng cùng contract; hướng dẫn dựng nền có lệnh thật | C / R3, D1 |
| A03 **[x] ?? XONG** | Auth API, `SessionService`, `SessionRepository`, CSRF/Origin, cookie và test | C01; UserRepository C02 | N2-3 / 5h | Login sai/đúng, đổi token, hết hạn, logout, CSRF qua test; không lộ hash/token; C xác minh được cookie | C / R1, R3, D1 |
| A04 **[x] ?? XONG** | Rooms/history API, status/JSON và integration test | C02; A03 | N3-4 / 3h | 3 phòng; history tăng dần, tối đa 50; thiếu phiên/room sai trả đúng lỗi; B gọi API thật | B / R3, D1 |
| A05 **[x] ĐÃ XONG** | Nginx `/ws`, volume/quyền ghi, `.env.example`, hướng dẫn WS/WSS và runtime | A02; C03 | N4-5 / 3h | Hai process thấy cùng DB; `/ws` upgrade được; restart giữ lịch sử; không lộ cổng WS nội bộ; mô tả TLS khớp môi trường thực | C / R3, D1 |
| A06 **[x] ?? XONG** | Polling đối chứng theo mục 12; README cài/chạy/seed/test/reset demo | A04; phương pháp C06 | N3-5 / 4h | C chạy được polling cùng dataset; B dùng README không cần hỏi lệnh ngoài; phân biệt endpoint thử nghiệm với luồng sản phẩm | C / R2, D1 |
| A07 **[x] ĐÃ XONG** | Viết lý thuyết HTTP/WS, handshake/frame/vòng đời và auth; phần HTTP trong chương 3-4; kết luận chung | Nguồn sơ cấp; code thực tế A03/C03 | Nháp N2-4, chốt N6 / 5h | Nội dung tiếng Anh, có trích dẫn; sơ đồ 101; giải thích frame khác JSON event, masking khác mã hóa; mỗi kết luận gắn cơ chế/thực nghiệm | C / R1, R3, R4 |
| A08 **[x] ĐÃ XONG** | Ghép 5 chương theo template, tham khảo, phụ lục; xuất Word/PDF | A07, B05/B06, C07, template | N5-7 / 3h | Đủ 20-60 trang theo đề; mục tiêu 26-30 trang chính; không còn placeholder; Word/PDF thống nhất, mục lục/số hình đúng | B / R4 |
| A09 **[ ] CÒN PHẦN NGƯỜI THẬT/THÔNG TIN LỚP** | Chuẩn bị slide/lời nói và trình bày phân đoạn A; diễn tập câu hỏi | A07; kịch bản B08 | N6-7 / 3h | Tự giải thích HTTP/Upgrade, auth và cách chạy bằng tiếng Anh; hiện diện trong video, âm thanh nghe được | B / D2, D3 |
| A10 **[ ] CÒN PHẦN NGƯỜI THẬT/THÔNG TIN LỚP** | Sửa lỗi cuối; chốt tag/source ZIP, kiểm tra source và báo cáo khớp, nộp và lưu xác nhận | C08/C09, B09 | N7, trước hạn nội bộ / 3h | Gói cuối giải nén được; manifest đúng phiên bản; không thiếu source/README/video; kiểm tra tệp đã tải lên | C / R4, D1, D3 |

### 10.3 B - Giao diện, phân tích so sánh và truyền đạt sản phẩm

| Mã | Việc và sản phẩm bàn giao | Phụ thuộc | Hạn nội bộ / giờ | Tiêu chí nghiệm thu cụ thể | Người kiểm tra / rubric |
| --- | --- | --- | --- | --- | --- |
| B01 **[x] ?? XONG** | Wireframe/login/chat/layout responsive; mục tiêu, đối tượng, use case chương 1 | Contract nháp; phạm vi mục 1 | N1-2 / 3h | Có login, danh sách phòng, tin nhắn, online, typing, trạng thái kết nối; chương 1 nêu vấn đề và tiêu chí thành công | A / R3, R4, D1 |
| B02 **[x] ?? XONG** | `api.js`, login/logout, lỗi form/401, CSRF và dữ liệu mock | A03; trước đó mock | N2-3 / 3h | Login đúng/sai rõ; không lưu token phiên trong JS storage; logout dừng reconnect; không hardcode danh tính | A / R3, D1 |
| B03 **[x] ?? XONG** | `socket.js`, chọn phòng, gộp history/event theo ID, online/typing và render chat | A04, C03/C04; mock trước | N3-4 / 5h | Hai client nhận tin một lần; một user nhiều tab không lặp presence; HTTP response cũ không làm lẫn phòng | C / R3, D1 |
| B04 **[x] ?? XONG** | Reconnect/backoff, trạng thái gửi, XSS, responsive và checklist UI | B03, C05 | N4-5 / 4h | Offline/online đúng trạng thái; không tự gửi lại tin chưa rõ; payload HTML chỉ hiện chữ; dùng được màn hình 390px và desktop | A / D1, D2 |
| B05 **[x] ?? XONG** | Bảng short/long polling/SSE/WS, nhận xét tình huống lựa chọn; `comparison.md` | Nguồn chính thức; A/C rà | Nháp N2-3, chốt N6 / 3h | Có tiêu chí mục 12; không gán SSE gửi hai chiều qua một stream; kết luận giải thích cả ưu/nhược WS | A / R2 |
| B06 **[x] ĐÃ XONG** | Viết chương 1, phần client/UX/chương 4, phân tích kết quả đo, kết luận; vẽ biểu đồ thật | B01/B05; raw data C06 | N2-6 / 5h | Tiếng Anh, ảnh đúng bản chạy; mọi số trên biểu đồ truy về raw data; phân biệt số đo với tính toán lý thuyết | C / R2, R3, R4 |
| B07 **[x] ĐÃ XONG** | Chụp minh chứng, caption; rà bìa/logo/font/lề/TOC/trích dẫn và Word/PDF | Template; bản ghép A08 | N4-7 / 3h | Checklist `report-review.md` được ghi kết quả; hình đọc được, không chứa secret; thông tin thành viên đúng nguồn | C / R4, D2 |
| B08 **[ ] CÒN PHẦN NGƯỜI THẬT/THÔNG TIN LỚP** | 10-12 slide, kịch bản timestamp, lời dẫn/chuyển đoạn; quay thử và dựng video | Sơ đồ C07; phần trình bày A09/C09 | N5-7 / 5h | Có đủ lý thuyết/so sánh/kiến trúc/demo/kết quả; A/B/C đều giải thích; thời lượng mục tiêu 14 phút, không vượt 20 | A / D2, D3 |
| B09 **[ ] CÒN PHẦN NGƯỜI THẬT/THÔNG TIN LỚP** | Tự trình bày phần B; quay cuối, mở nghe toàn video; rà giao diện và tệp nộp | B08; bản source cuối A10 | N6-7 / 4h | Tiếng Anh rõ; đọc được thao tác; không mất âm thanh sau xuất; không dùng ảnh/video cũ khác code cuối | C / D1, D2, D3 |

### 10.4 C - Dữ liệu, WebSocket, thực nghiệm và kiểm chứng

| Mã | Việc và sản phẩm bàn giao | Phụ thuộc | Hạn nội bộ / giờ | Tiêu chí nghiệm thu cụ thể | Người kiểm tra / rubric |
| --- | --- | --- | --- | --- | --- |
| C01 **[x] ?? XONG** | Schema 4 bảng, index, seed/test data, PDO, ERD nháp; phối hợp sessions | A chốt session contract | N1-2 / 4h | FK bật; seed lặp không trùng; password hash; DB test riêng; A chạy được seed và đọc user/session | A / R3, D1 |
| C02 **[x] ?? XONG** | User/Room/MessageRepository, transaction và test dữ liệu | C01 **[x] ?? XONG** | N2-3 / 4h | `latest` đúng thứ tự/giới hạn; create trả Message chuẩn; query có tham số; rollback không phát tin giả | A / R3, D1 |
| C03 **[x] ?? XONG** | WS gateway một worker; `/ws`, Origin/session; handler và lưu trước broadcast | A02/A03; C02 | N2-4 / 5h | Client hợp lệ upgrade; client sai bị chặn; danh tính lấy từ session; DB lỗi không phát `chat:new` | A / R1, R3, D1 |
| C04 **[x] ?? XONG** | Join/chuyển phòng, hub, presence nhiều tab, typing TTL | C03; event contract | N3-4 / 4h | Mỗi socket một phòng; broadcast đúng phạm vi; dọn trạng thái phòng cũ; TTL typing đạt 3 giây theo thiết kế | B / R1, D1 |
| C05 **[x] ?? XONG** | Payload/Unicode/size, account rate limit, heartbeat và thu hồi session | C03/C04; A03 | N4-5 / 4h | TC biên pass; nhiều tab không vượt chung 20 tin/60 giây; socket hết phiên bị đóng; server sống sau dữ liệu lỗi | A / R1, D1 |
| C06 **[x] ?? XONG** | `experiment-plan.md`, runner WS/nhiều socket, đo so sánh, raw/log test | A06; app ổn định N4; A rà phương pháp | N3-5 / 4h | Đủ cấu hình/lệnh/dataset/lặp đo; có polling và WS cùng điều kiện; lưu cả lỗi; không tự điền số chưa đo | B / R2, D1 |
| C07 **[x] ĐÃ XONG** | Viết yêu cầu/thiết kế/triển khai/test/kết quả; ERD, architecture, sequence, giới hạn | C01-C06; phần A/B | Nháp N2-4, chốt N6 / 4h | Nêu cơ chế event loop/đồng thời, lý do 1 worker, SQLite và hạn chế; sơ đồ khớp code; kết quả có TC/EV | A / R1, R3, R4 |
| C08 **[ ] CÒN PHẦN NGƯỜI THẬT/THÔNG TIN LỚP** | Cài từ ZIP/clone sạch, checklist TC, `worklog.md`, `rubric-evidence.md`, manifest | README A06; bản RC | N5-7 / 3h | Thực hiện chỉ bằng tài liệu; ghi pass/fail và lỗi; mỗi rubric có link thật; chia việc/giờ thực tế được ba người xác nhận | B / R4, D1, D3 |
| C09 **[ ] CÒN PHẦN NGƯỜI THẬT/THÔNG TIN LỚP** | Trình bày phần C, diễn tập; rà kết quả cuối, source/video/report cùng phiên bản | C07; B08; A10 | N6-7 / 3h | Tiếng Anh; giải thích lưu/broadcast/rate limit và kết quả đo; kiểm tra được gói cuối có source và video có tiếng | A / D2, D3 |

**Tuyến phụ thuộc cần giữ:** C01 -> A03/C02 -> C03 -> C04/C05 -> tích hợp B03/B04 -> TC/đo -> chốt báo cáo/video. A/B/C vẫn viết tài liệu và làm mock/stub độc lập khi chờ code; cuối ngày 2 phải gỡ xong phụ thuộc session dùng chung.

## 11. Dàn ý báo cáo tiếng Anh và minh chứng cho 6 điểm REPORT

### 11.1 Bố cục 5 chương, mục tiêu 28 trang nội dung chính

Đây là dàn ý nội bộ đáp ứng các nội dung PDF trang 6-11. Điều chỉnh tên/thứ tự chương khi có template thật, nhưng phải giữ khảo sát lý thuyết, yêu cầu, thiết kế, triển khai/kết quả có phân tích và kết luận. Phân trang dưới đây không tính bìa/phụ lục; không kéo dài bằng cách chép code hoặc phóng hình.

| Phần | Nội dung bắt buộc trong bản nhóm | Trang mục tiêu | Người viết / rà | Rubric và minh chứng |
| --- | --- | --- | --- | --- |
| Trang đầu theo template | Bìa chính xác; các trang đầu/abstract, TOC, danh mục hình/bảng/từ viết tắt nếu mẫu yêu cầu | Theo mẫu, không dùng để bù nội dung chính | A điền; B rà; C xác minh MSSV | R4; checklist bìa và mục lục |
| **Chapter 1 - Introduction** | Bối cảnh realtime; bài toán chat; mục đích, đối tượng, mục tiêu đo/kiểm tra được; phạm vi và bố cục báo cáo | **2** | B viết; A rà | R3/R4; use case và bảng yêu cầu FR |
| **Chapter 2 - Background and Phân tích so sánh** | Lịch sử/chuẩn; HTTP/WS; handshake/frame/control frame; event/server push/broadcast; bảo mật và đồng thời; so sánh 4 cách giao tiếp và lý do chọn WS | **7** | A lý thuyết; B so sánh; C vòng đời/đồng thời; kiểm tra chéo | R1/R2; EV-01/02/11/12 và nguồn kỹ thuật |
| **Chapter 3 - Requirements and System Design** | Functional/non-functional requirements; use case; kiến trúc PHP-FPM/Workerman/Nginx; ERD 4 bảng; hợp đồng HTTP/event; sequence auth/join/send/reconnect | **5** | C chủ trì; A auth/API; B client; A rà | R3; EV-03/04/05, contract |
| **Chapter 4 - Implementation, Testing and Evaluation** | Cấu trúc module và quyết định kỹ thuật; luồng UI; triển khai/seed; auth/security; test case và kết quả thật; so sánh số liệu; thử đồng thời; khó khăn, cách xử lý, hạn chế | **11** | C server/test; B UI/ảnh/phân tích; A auth/deploy; cả nhóm rà | R2/R3; TC-01..20, EV-06..12 |
| **Chapter 5 - Conclusion and Future Work** | Đối chiếu từng mục tiêu; đóng góp thật; điều chưa đạt; giới hạn của benchmark/50 tin/1 worker; hướng pub/sub, recovery, nhiều worker nếu mở rộng | **3** | A ghép; B/C mỗi người viết phần đánh giá; B rà | R1/R3/R4; không kết luận vượt quá test |
| References | Nguồn đã thật sự đọc/dùng; trích dẫn nhất quán theo template; không để nguồn “có tên nhưng không được dẫn” | Theo nội dung thực | A tổng hợp; C rà URL/tác giả/ngày | R1/R2/R4 |
| Appendices | Hướng dẫn test đầy đủ, contract dài, raw-data locator, đóng góp, các trang mẫu yêu cầu; source dài để ngoài phần chính | Không tính trong giới hạn 20-60 theo đề | C + A | R4; không chuyển nội dung cốt lõi hết xuống phụ lục |

### 11.2 R1 - Những ý lý thuyết phải giải thích được

| Ý cần nghiên cứu | Câu hỏi nội dung phải trả lời | Người viết | Minh họa/liên hệ demo |
| --- | --- | --- | --- |
| Bối cảnh và lịch sử | Vì sao polling từng được dùng; WebSocket giải quyết bài toán nào; RFC 6455 quy định điều gì? | A | Timeline ngắn có nguồn; chat là use case, bên cạnh game/dashboard/thông báo |
| HTTP và WS | HTTP request-response khác kênh WebSocket lâu dài như thế nào; client/server có thể gửi chủ động ra sao? | A | Hai sequence cùng một tin chat, tránh khẳng định WS luôn nhanh hơn mọi HTTP |
| Opening handshake | Với triển khai HTTP/1.1 của nhóm: Upgrade/Connection, Sec-WebSocket-Key/Accept và 101 hoạt động ra sao? | A + C | EV-01 từ DevTools và sequence; không nói HTTP 101 là cách duy nhất cho mọi phiên bản HTTP |
| Frame và message | Text/binary, fragmentation, opcode, close/ping/pong là gì; thư viện xử lý phần nào? | A | Phân biệt WebSocket frame với JSON event `chat:send`; không tự viết parser frame |
| Masking và TLS | Masking client frame có ý nghĩa gì; tại sao không thay mã hóa; `ws` và `wss` khác gì? | A | Sơ đồ TLS/proxy; không gọi `ws://localhost` là kết nối đã mã hóa |
| Event và server push | Gửi event, lưu DB, broadcast tới các socket đúng phòng theo thứ tự nào? | C | Sequence `chat:send` -> validate -> persist -> `chat:new`; EV-02/05 |
| Vòng đời | Connecting/open/closing/closed; ping/pong; disconnect; rejoin/reconnect khác retry gửi tin ra sao? | B viết client; C server | State diagram; backoff; TTL typing và giới hạn 50 tin khi phục hồi |
| Xác thực và phân quyền | Cookie/Origin/CSRF có vai trò gì; handshake hợp lệ đã đủ để gửi vào mọi phòng chưa? | A | Bảng kiểm tra handshake/event; nguồn danh tính từ session; TC-02/03/05/14 |
| An toàn dữ liệu | Validate size/Unicode, XSS, SQL parameterization, rate limit theo tài khoản bảo vệ điều gì và còn hạn chế gì? | A + C | Ví dụ payload và kết quả chặn; không coi rate limit chat là giải pháp DDoS đầy đủ |
| Đồng thời và hiệu năng | Một event loop xử lý nhiều connection khác một worker/request thế nào; tác vụ DB chặn có ảnh hưởng gì? | C | Thử nhiều socket; giải thích 1 worker giữ hub chung và SQLite write contention |
| Reliability và mở rộng | WebSocket có tự lưu tin/offline retry/exactly-once không; muốn nhiều worker cần gì? | C | SQLite giữ tin; memory giữ presence; message ID hỗ trợ gộp; đề xuất shared state/pub-sub và cơ chế recovery, chưa nhận đã triển khai |

### 11.3 R3 - Truy vết yêu cầu, cơ chế và kết quả

| Mã yêu cầu nội bộ | Hành vi sản phẩm | Cơ chế/chủ phụ trách | Kiểm tra và minh chứng |
| --- | --- | --- | --- |
| FR-01 | Chỉ người đăng nhập hợp lệ dùng chat | HTTP session A; WS resolve/check C | TC-01/02/03; EV-01/10 |
| FR-02 | Chọn 1/3 phòng, dữ liệu không lẫn | Room API A, hub C, UI B | TC-04/05/07; EV-05/06 |
| FR-03 | Gửi/nhận hai chiều, lưu rồi broadcast | ChatService/repository C, render B | TC-04/16/17; EV-02/05/08 |
| FR-04 | Xem tối đa 50 tin mới nhất | Repository C, endpoint A, merge B | TC-08/16; EV-08 |
| FR-05 | Online và typing đúng khi nhiều tab | Hub/TTL C, UI/throttle B | TC-06/07/12; EV-07 |
| FR-06 | Khôi phục kết nối/phòng khi mạng phục hồi | Backoff/merge B, cleanup C | TC-08/12; EV-09 |
| NFR-01 | Dữ liệu sai không phá server hoặc thực thi trên UI | A/C validation/auth; B safe render | TC-09/10/11/13/14/17; EV-10 |
| NFR-02 | Có thể tái lập môi trường và kết quả | A Docker/README; C seed/test/đo | TC-18/19/20; EV-11/12 |

Khi viết một cơ chế, dùng chuỗi giải thích: **yêu cầu -> giải pháp -> luồng xử lý -> kết quả test -> hạn chế**. Đưa đoạn code ngắn khi cần giải thích quyết định, không thay cả chương bằng ảnh chụp source.

### 11.4 R4 - Checklist hình thức và học thuật

- [ ] Đối chiếu template chính thức; logo không méo/sai màu; tên trường/khoa/môn `503073`, giảng viên, lớp, nhóm, họ tên/MSSV và tên đề tài đúng thông tin được xác nhận.
- [ ] Nội dung chính bằng tiếng Anh, thuật ngữ nhất quán; có mở đầu, thân bài, kết luận, đủ các nội dung ở trang 11 của đề.
- [ ] Bản PDF có 20-60 trang không tính bìa/phụ lục; đặt mục tiêu 26-30 trang chính; kiểm tra lại sau mỗi lần thay hình/style lớn.
- [ ] Heading/style/font/cỡ chữ/lề/giãn dòng/đánh số theo mẫu; không tự coi một bộ số đo lề/phông là quy định khi chưa có template.
- [ ] Mục lục tự động, caption/numbering, danh mục hình/bảng/từ viết tắt và các trang đầu/phụ lục theo mẫu đã được cập nhật.
- [ ] Mỗi hình/bảng có tên, caption, nguồn nếu lấy ngoài, và đoạn văn giải thích ý nghĩa; ảnh đọc được ở kích thước trang thật, không tràn lề, không chụp phần thừa.
- [ ] Các ý lấy từ nguồn có trích dẫn đúng chỗ; ảnh sửa từ nguồn ngoài vẫn ghi nguồn; phần tự đo/tự thiết kế ghi rõ là của nhóm.
- [ ] Dự kiến dùng ít nhất 8 nguồn phù hợp đã đọc thật (mục tiêu nội bộ), ưu tiên RFC, tài liệu WebSocket/Fetch/SSE, Workerman, PHP PDO/password, SQLite, Nginx và OWASP; style trích dẫn theo template.
- [ ] Không có số liệu bịa, tài liệu tham khảo không tồn tại, hình stock giả làm demo, code chưa chạy nhưng mô tả là đã hoàn thành; ghi trung thực test fail/chưa thực hiện.
- [ ] So sánh Word và PDF: không vỡ bảng/hình, không mất ký tự, không comment/track changes/placeholder; thông tin source/video/report nhất quán.

## 12. R2 - Phân công so sánh và thực nghiệm cụ thể

### 12.1 Bảng so sánh phải có trong báo cáo

**B chủ trì, A rà lý thuyết, C bổ sung số đo.** So sánh **short polling, long polling, Server-Sent Events (SSE), WebSocket** theo: hướng truyền dữ liệu; cách duy trì kết nối; độ trễ cập nhật; request/overhead; tải server khi idle và khi có sự kiện; hỗ trợ browser/proxy/reconnect; định dạng dữ liệu; độ phức tạp auth/vận hành; trường hợp dùng phù hợp. Mỗi lựa chọn phải có cả ưu/nhược và một ví dụ thực tế.

Kết thúc bảng bằng quyết định cho chính dự án: chat cần gửi/nhận hai chiều và các event presence/typing nên chọn WS; HTTP vẫn hợp lý cho login/lịch sử; SSE phù hợp hơn trong một số luồng chỉ cần server phát dữ liệu xuống; polling có thể đủ cho tần suất cập nhật thấp. Những nhận định về hiệu năng phải nêu điều kiện, không ghi “WS luôn tốt nhất”.

Long polling và SSE được nghiên cứu/so sánh bằng nguồn; **không bắt buộc code cả bốn transport** trong kế hoạch 7 ngày. Phần đo nội bộ tối thiểu là **short polling đối chứng và WebSocket** trên cùng môi trường.

### 12.2 Thiết kế phép đo - không điền trước kết quả

| Mục đo nội bộ | Cách thực hiện thống nhất | Người làm | Đầu ra |
| --- | --- | --- | --- |
| Môi trường | Ghi OS/CPU/RAM, Docker/PHP/Workerman, tag commit, cấu hình 1 worker, đường đi mạng và có/không TLS; tắt tác vụ tải lớn ngoài bài đo | C, A rà | `experiment-plan.md`, thông tin môi trường trong báo cáo |
| Polling đối chứng | A tạo endpoint/harness riêng có auth, đọc tin mới theo `afterId`; B dùng client gọi mỗi 1 giây; giữ cùng DB/nội dung và rate limit như WS; không giả dữ liệu hoặc đọc full lịch sử mỗi vòng nếu không giải thích | A + B | `tools/benchmark/`, hướng dẫn chạy; endpoint test mặc định tắt ngoài chế độ đo |
| Workload chung | Một người gửi, một người nhận, 20 tin/120 giây với lịch gửi chung mỗi 6 giây, lệch thời điểm poll để tránh vô tình căn đồng hồ có lợi; user đã vào phòng, bỏ dữ liệu cũ theo marker | C tạo dataset; B vận hành | Marker/run ID, lịch gửi, message ID, cấu hình rate limit |
| Cách so sánh | Chạy polling và WS lần lượt, không cho cùng client bật hai transport nhận; dùng DB sạch tương đương mỗi run, cùng số client/payload/network; đảo thứ tự giữa các lần đo | C | Checklist từng run; cấu hình chính xác được lưu |
| Số lần và warm-up | Mỗi transport chạy 3 lần; warm-up kết nối trước khi tính 120 giây; ghi thời điểm bắt đầu/kết thúc, mọi run lỗi và lý do chạy lại | C | 6 run có raw data hoặc ghi rõ run chưa hoàn thành |
| Độ trễ ứng dụng | Dùng một harness cùng tiến trình/đồng hồ monotonic: đánh dấu lúc client gửi và lúc client nhận được message ID tương ứng; với polling tính đến lần poll thật sự thấy tin; timeout được ghi là thất bại | C | Raw `messageId`, transport, run, elapsedMs, status; median/p95 theo run |
| Request và traffic | Đếm HTTP request của polling, WS connection và message/frame ở đúng lớp công cụ đo; nêu rõ có tính handshake/header/TLS không | C; B vẽ | Bảng số đo có đơn vị; không lấy độ dài JSON gọi là tổng bytes trên mạng |
| Idle | Cho các client cùng giữ kết nối/luồng poll nhưng không gửi tin trong 60 giây; đếm request/event và quan sát CPU/RAM | C | So sánh overhead khi không có dữ liệu; không trộn với số liệu active |
| Đồng thời | WS tăng lần lượt 2, 10, 20 connection, cùng máy, chỉ dùng tài khoản demo; một sender phát 10 tin, các receiver kiểm tra ID; không tạo burst vượt rate limit | C | Số kết nối thành công, số nhận mong đợi/thực nhận, lỗi/duplication; CPU/RAM nếu đo được |
| Phân tích | Từ raw data tạo bảng/biểu đồ median/p95 và request; giải thích quan hệ với chu kỳ poll, workload, ảnh hưởng SQLite và tài nguyên máy | B viết; A/C rà | `comparison.md`, `evidence/charts/`, chương 4 và slide kết quả |

`afterId` chỉ thuộc **harness polling đối chứng**; API lịch sử sản phẩm ở mục 5 vẫn tải 50 tin mới nhất, không ngầm xem harness là tính năng phục hồi đầy đủ. A ghi rõ route/cấu hình đo vào contract phụ lục trước khi C viết runner. Không mở endpoint debug không xác thực.

Mốc 20 connection là mục tiêu test chức năng đồng thời, **không phải khẳng định chịu tải production hay 20 người dùng độc lập**. Nhiều connection có thể chung một user; presence vẫn gom theo user, rate limit vẫn dùng chung tài khoản. Nếu máy không đạt, ghi mức thực tế, lỗi và phân tích nguyên nhân; không sửa báo cáo để biến fail thành pass.

Mọi lần đo chạy bằng tài khoản/dữ liệu giả lập, không chứa cookie/token trong raw data. Khi so độ trễ giữa máy khác nhau, không lấy hai timestamp hệ thống chưa đồng bộ để trừ nhau; giữ cách đo một đồng hồ hoặc mô tả phương pháp đồng bộ/sai số. Báo cáo phải nói rõ đây là độ trễ end-to-end quan sát được trong bài đo, không phải độ trễ một chiều thuần của mạng.

### 12.3 Bảng kết quả mẫu phải được điền từ raw data

| Transport / run | Tin gửi / nhận / timeout | Median / p95 (ms) | HTTP request / WS message theo lớp đo | CPU/RAM nếu có | Raw data |
| --- | --- | --- | --- | --- | --- |
| Polling / 1-3 | CHƯA ĐO | CHƯA ĐO | CHƯA ĐO | CHƯA ĐO | Điền đường dẫn sau khi chạy |
| WebSocket / 1-3 | CHƯA ĐO | CHƯA ĐO | CHƯA ĐO | CHƯA ĐO | Điền đường dẫn sau khi chạy |
| WS concurrent / 2, 10, 20 connection | CHƯA ĐO | Không gộp với benchmark transport nếu khác workload | CHƯA ĐO | CHƯA ĐO | Điền đường dẫn sau khi chạy |

Nếu chưa đo, ghi `NOT MEASURED` trong bản nháp tiếng Anh; không nộp bảng placeholder làm kết quả. Muốn đưa tính toán như “poll mỗi giây tạo xấp xỉ 120 lần trong 120 giây” phải ghi là **ước tính lý thuyết**, tách khỏi request count đo thực tế. Nêu giới hạn: mẫu nhỏ, môi trường local/Docker, chu kỳ poll cố định, một worker, chưa đo mạng WAN hay tải lớn.

## 13. Bộ nghiệm thu chi tiết và danh mục minh chứng

### 13.1 Test case phải ghi kết quả thực tế

Checklist ở mục 8 là bản tóm tắt. Bảng dưới là kế hoạch thực thi có mã để dẫn trong báo cáo/video. **Tất cả hiện ở trạng thái CHƯA CHẠY.** Khi thực hiện, C cập nhật `docs/test-cases.md` với: commit/môi trường/thời gian, bước làm, expected/actual, PASS/FAIL, người chạy, log/ảnh và issue sửa lỗi. Test lại lỗi đã sửa trước khi đóng.

| TC | Tiền điều kiện và thao tác | Kết quả nghiệm thu | Người chạy / người sửa chính |
| --- | --- | --- | --- |
| TC-01 | DB seed; thử login sai/đúng; gọi me/rooms/history với và không có phiên | Sai trả lỗi chung; đúng đổi token và có user; API riêng thiếu phiên trả 401; không lộ hash/token | B / A |
| TC-02 | Mở WS thiếu cookie, cookie sửa, phiên hết hạn/thu hồi, sai `/ws` | Không nhận client trái phép; không đăng ký membership hoặc phát dữ liệu chat | A / C |
| TC-03 | Gửi login/logout sai CSRF/Origin; mở WS với Origin ngoài allowlist | HTTP trả 403 theo contract; WS bị từ chối; trường hợp hợp lệ vẫn dùng được | C / A + C |
| TC-04 | Hai browser/profile đăng nhập user khác, cùng phòng; mỗi bên gửi 3 tin có marker | Không reload; mỗi tin nhận đúng một lần ở hai phía, có ID/tên/thời gian đúng và tồn tại DB | B / B + C |
| TC-05 | A ở phòng 1, B ở phòng 2; gửi chat/typing; thử gửi roomId khác membership | Không rò chat/presence/typing; event sai phòng trả NOT_IN_ROOM, không ghi DB | A / C |
| TC-06 | Một user hai tab cùng phòng và user khác quan sát; đóng lần lượt từng tab | Presence chỉ có một user; đóng một tab vẫn online; tab cuối đóng thì được dọn theo cơ chế disconnect/heartbeat | B / C + B |
| TC-07 | Join lặp, đổi phòng liên tục trong khi history cũ chưa về; đổi lúc đang gõ | Không membership trùng; bỏ response cũ; không lẫn tin/phòng; typing/presence phòng cũ được xóa | B / B + C |
| TC-08 | Ngắt mạng, gửi vài tin từ client khác (không quá 50); nối lại; nhận trùng ID có chủ đích trong test client | Backoff/rejoin đúng phòng; gộp snapshot/event không lặp ID; nút gửi theo trạng thái; hơn 50 tin là giới hạn được ghi rõ | B / B + C |
| TC-09 | Gửi rỗng/chỉ khoảng trắng; 500/501 ký tự Unicode, tiếng Việt và emoji | Rỗng/501 bị chặn; 500 ký tự theo quy tắc đã chốt được nhận; không lưu/broadcast tin sai | A / C |
| TC-10 | Gửi JSON lỗi, array thay object, type lạ, payload sai kiểu, binary; thử dưới/trên 8192 byte, cả fragmented message | Trường hợp sai bị từ chối/đóng theo chính sách contract; message hợp lệ dưới giới hạn hoạt động; server không dừng | A / C |
| TC-11 | Cùng tài khoản mở nhiều tab gửi đủ 20 tin rồi tin 21 trong cửa sổ 60 giây; sau đó chờ cửa sổ trượt qua | Tin 21 RATE_LIMITED, không lưu/phát; chia tab không lách giới hạn; được gửi khi hết hạn tương ứng | A / C |
| TC-12 | Ngừng gõ >3 giây, đổi phòng khi gõ; tắt client đột ngột/mất mạng | Typing tự hết; socket chết được dọn trong mục tiêu khoảng 60 giây; presence không treo vô hạn | B / C |
| TC-13 | Gửi `<script>alert(1)</script>`, chuỗi HTML và chuỗi kiểu SQL injection | UI chỉ hiện chữ, không chạy script; query tham số không làm sai DB; không in stack trace/secret ra client | C / B + A + C |
| TC-14 | Hai tab cùng phiên; logout một tab; thử event sau logout và chờ kiểm tra định kỳ | Phiên bị thu hồi trước xóa cookie; tab logout dừng reconnect; mọi socket cùng phiên đóng mục tiêu khoảng 1 giây theo timer; không nhận thêm dữ liệu được phép sau thu hồi | B / A + C |
| TC-15 | Rà DOM/storage, log/JSON, port Docker và biến môi trường; HTTPS nếu môi trường có TLS | Không lộ password hash/token; cookie flags đúng môi trường; WS nội bộ không public; nếu chưa có TLS ghi rõ local ws và hướng dẫn wss | C / A + B |
| TC-16 | Gửi tin có marker, restart HTTP/WS không xóa volume; seed lại; tạo >50 tin lịch sử | Lịch sử đã lưu còn; seed không trùng; lấy đúng 50 tin mới nhất tăng dần; client reconnect được | B / A + C |
| TC-17 | DB test gây lỗi ghi có kiểm soát, khôi phục quyền/kết nối sau test | Không broadcast tin chưa lưu; lỗi được log an toàn; server không crash; tiếp tục gửi được sau khi khôi phục | A / C |
| TC-18 | Giải nén source cuối vào thư mục sạch, dùng DB/volume test mới; một người khác làm đúng README | Cài dependency, tạo secret, up, seed, test và truy cập được; có tài khoản demo; không cần file ngoài gói | C / A |
| TC-19 | Chạy polling/WS theo 6 run ở mục 12 và lưu kết quả | Tái lập được phương pháp, ID và số liệu; biểu đồ khớp raw; lỗi/timeouts không bị giấu | C / A + B + C |
| TC-20 | Chạy 2/10/20 socket theo cùng-room marker; sau test đóng hết | Số nhận khớp số receiver đã join; không nhận trùng/rò phòng; bộ nhớ membership được dọn; ghi mức đạt thật | A + C / C |

Tự động hóa ưu tiên validation/rate limiter, repository, auth và broadcast isolation; kiểm thử tay dành cho browser/UX/DevTools, audio/video và hình thức báo cáo. Không cần tạo test hình thức chỉ để tăng số lượng. Log kiểm thử không chứa session secret hoặc cookie đầy đủ.

### 13.2 Minh chứng phải có, ai tạo và dùng ở đâu

Mã EV là nhãn nội bộ; đặt tên tệp theo mã và nội dung để truy vết. Ảnh UI, DevTools, sơ đồ, test log và biểu đồ là các loại minh chứng khác nhau; **không dùng ảnh vẽ thay ảnh ứng dụng đã chạy**.

| Mã | Minh chứng dự kiến | Người tạo / rà | Nơi dùng |
| --- | --- | --- | --- |
| EV-01 | DevTools request/response handshake HTTP/1.1 101, header liên quan đã che cookie/secret | A chụp; C rà | R1, D2; chương 2; video A |
| EV-02 | DevTools WS message `room:join`, `room:joined`, `chat:send`, `chat:new` với hai client | B chụp; C rà | R1/R3, D1/D2; chương 2/4; video B/C |
| EV-03 | Sơ đồ browser/Nginx/PHP-FPM/Workerman/SQLite và boundary HTTP/WS | C vẽ; A rà | R3, D2; chương 3; slide architecture |
| EV-04 | ERD users/rooms/messages/sessions; PK/FK/index và mục đích bảng | C vẽ; A rà | R3; chương 3; video C |
| EV-05 | Sequence login -> WS -> join -> history/merge và send -> persist -> broadcast; state reconnect | C vẽ server; B client; A rà | R1/R3, D2; chương 3/4 |
| EV-06 | Hai tài khoản cùng phòng và khác phòng, kèm marker tin nhắn chứng minh phạm vi | B chụp; C rà | R3, D1; chương 4; video B |
| EV-07 | Presence nhiều tab, typing hết hạn, UI desktop/mobile | B chụp; C rà | D1/D2, R3/R4; chương 4 |
| EV-08 | Tin marker trước/sau restart, truy vấn DB test và history tối đa 50 tin | C lưu; B chụp UI; A rà | R3, D1; chương 4; video C |
| EV-09 | Trạng thái offline/reconnecting/connected và khôi phục phòng | B quay/chụp; C rà | D1/D2; chương 4; video B |
| EV-10 | Log/test result cho unauthorized, Origin/CSRF, payload/XSS, rate limit và logout | A + C tạo; B rà phần UI | R1/R3, D1; chương 4; video A/C |
| EV-11 | Bảng so sánh, raw data 6 run, biểu đồ từ số đo, phương pháp và giới hạn | C raw; B bảng/biểu đồ; A rà | R2; chương 2/4; slide results |
| EV-12 | TC result, thử nhiều connection, log clean setup, worklog và đối chiếu rubric | C tổng hợp; A/B kiểm tra chéo | R3/R4, D1/D3; chương 4/phụ lục/manifest |

`docs/rubric-evidence.md` phải có một dòng cho mỗi R1-R4/D1-D3: chủ việc, mục/trang báo cáo **sau khi xuất PDF**, tệp minh chứng thật, TC liên quan, timestamp video thật và trạng thái rà. Không dùng số trang/timestamp dự kiến thay số của bản cuối.

## 14. Slide, video và thuyết trình để đáp ứng 2 điểm D2 + D3

### 14.1 Dàn 12 slide tiếng Việt (mục tiêu nội bộ)

| Slide | Nội dung | Người nói | Hình/minh chứng chính |
| --- | --- | --- | --- |
| 1 | Topic, course, team, problem and objectives | A | Đủ 3 tên/MSSV, đúng tên đề tài |
| 2 | HTTP vs WebSocket, realtime use cases | A | Sequence và ví dụ chat |
| 3 | Handshake, connection lifecycle, security principles | A | EV-01; masking khác TLS |
| 4 | Polling / long polling / SSE / WS and design choice | B | Bảng so sánh gọn, không nhồi đoạn văn |
| 5 | System architecture and PHP stack | C | EV-03 |
| 6 | Database and event/data flow | C | EV-04/05 |
| 7 | Live demo plan: login, rooms, bidirectional chat | B | Chuyển sang app thật, EV-06 |
| 8 | Presence, typing, mobile and reconnect | B | App thật, EV-07/09 |
| 9 | Persistence, isolation and security tests | C | EV-08/10 |
| 10 | Comparison experiment and concurrent connections | C | EV-11/12, có đơn vị và môi trường |
| 11 | Results, limitations and lessons learned | B | Bảng mục tiêu đạt/chưa đạt, không phóng đại |
| 12 | Conclusion, future work and selected references | A | Tóm tắt có căn cứ; nguồn chính |

Slide là công cụ trình bày nhóm chuẩn bị theo yêu cầu lớp trang 1; PDF không ấn định 12 slide hay bắt buộc nộp riêng PPTX/PDF. Nhóm vẫn đưa cả hai vào gói nội bộ để có bản sửa và bản xem ổn định.

### 14.2 Kịch bản video mục tiêu 14 phút, tối đa 20 phút

| Mốc dự kiến | Người trực tiếp trình bày | Thao tác/nội dung | Điều cần chứng minh |
| --- | --- | --- | --- |
| 00:00-01:00 | A | Giới thiệu ba thành viên, vấn đề, phạm vi và sản phẩm | Nói rõ đề tài và mục tiêu |
| 01:00-03:00 | A | Giải thích HTTP/WS, mở DevTools thấy 101, event/connection, auth và lệnh chạy chính | R1 + D2; hiểu cơ chế thay vì chỉ đọc định nghĩa |
| 03:00-04:30 | C | Sơ đồ thành phần/DB, chỉ luồng validate -> persist -> broadcast | R3 + D2; cấu trúc khớp code |
| 04:30-07:30 | B | Hai browser/profile, login hai user, cùng phòng gửi qua lại; đổi phòng để thấy cô lập; presence/typing | D1; hai chiều/server push không reload; không nhầm hai tab cùng session là hai user |
| 07:30-09:00 | B | Mất mạng -> trạng thái UI -> reconnect/rejoin/history; mobile; một ví dụ XSS chỉ hiện text | D1/D2; phục hồi có giới hạn, không tự gửi lặp |
| 09:00-11:30 | C | Restart để chứng minh lưu DB; giới hạn tin và kết quả auth/payload; log nhiều socket; giải thích phần code then chốt | D1/D2; kết quả có kiểm chứng và hiểu code |
| 11:30-12:30 | C | Bảng/biểu đồ polling/WS, phương pháp đo và một hạn chế | R2 + D2; số liệu không tách khỏi điều kiện đo |
| 12:30-13:00 | B | Kết luận so sánh và trải nghiệm người dùng; điều đã/chưa đạt | Giải thích vì sao chọn WS cho bài toán |
| 13:00-14:00 | A | Giới hạn 1 worker/50 tin, hướng mở rộng, chỉ vị trí source/README/report và kết luận | D3; người chấm biết cách kiểm tra lại |

Phân bố mục tiêu: **A 4 phút, B 5 phút, C 5 phút**, mỗi người tự nói tiếng Anh và giải thích phần đã làm. Sau quay, B thay timestamp dự kiến bằng timestamp thực trong kịch bản và bảng rubric. Không cắt ghép làm một người chỉ hiện tên mà không tham gia thuyết trình.

### 14.3 Checklist trước và sau quay

- [ ] Mỗi người chuẩn bị lời dẫn tiếng Anh từ phần mình; diễn tập chuyển người, thao tác và câu hỏi. Có thể xem ghi chú ngắn, nhưng phải giải thích được khi hỏi lại.
- [ ] Hai browser/profile tách cookie dùng hai user demo; phòng/dataset reset bằng lệnh rõ trong README; marker tin dễ nhận biết.
- [ ] Quay thử 30 giây để kiểm tra cả tiếng người nói, chữ trong app/terminal/slide; mục tiêu 1080p nếu máy cho phép, tăng font để xem được.
- [ ] Che `.env`, secret, cookie, token, dữ liệu cá nhân; dùng log đã lọc và tài khoản giả lập. Không cắt cảnh tạo ấn tượng test pass khi thực tế fail.
- [ ] Có đủ lý thuyết, so sánh, architecture, demo app thật, giải thích code/cơ chế, kết quả và hạn chế; không dành phần lớn video chỉ lướt slide.
- [ ] Sau xuất, mở **chính file MP4 trong gói nộp**, nghe từ đầu tới cuối, kiểm tra các đoạn đổi người, không mất tiếng/đen màn hình/chữ mờ; thời lượng không vượt 20 phút.
- [ ] Tên/đóng góp A/B/C rõ; cả ba có mặt và trực tiếp trình bày. Nếu có ngoại lệ phải theo chấp thuận của giảng viên, không tự bỏ người.
- [ ] Bản video dùng code/tag và số liệu khớp báo cáo cuối; nếu sửa cơ chế sau quay phải cập nhật cảnh/lời giải thích liên quan.

### 14.4 Câu hỏi diễn tập, cả ba đều phải nắm luồng chung

| Người chuẩn bị sâu | Câu hỏi cần tự trả lời bằng tiếng Anh |
| --- | --- |
| A | Vì sao HTTP API vẫn tồn tại khi đã có WS? 101 làm gì? Masking có phải encryption? Cookie/session/Origin/CSRF khác nhau thế nào? Làm sao PHP-FPM và WS chia sẻ login? |
| B | Vì sao WS thay vì SSE/polling cho chat này? Vì sao history + live event có thể trùng/lẫn? Message ID/rejoin giúp gì? Nếu offline lâu >50 tin thì sao? Vì sao không tự resend tin chưa rõ đã lưu? |
| C | Vì sao lưu trước broadcast? Một user nhiều tab được đếm/rate-limit ra sao? Tại sao chỉ một worker? DB write chậm ảnh hưởng event loop thế nào? Muốn nhiều worker cần thay gì? Số đo đang đo cái gì và giới hạn ở đâu? |
| Cả nhóm | Chỉ ra một yêu cầu -> module -> TC -> kết quả -> đoạn báo cáo; nêu một khó khăn thật đã xử lý và một việc chưa đạt. |

## 15. Gói nộp, README và thời điểm chốt

### 15.1 Cấu trúc gói đề xuất

Tên gói và kênh nộp phải đổi theo hướng dẫn lớp/LMS khi đã xác nhận. Cấu trúc dưới đây là phương án nội bộ, không phải tên thư mục do PDF bắt buộc.

```text
<CLASS>_<GROUP>_Topic09_WebSockets/
├── README.md                   # Bản đồ gói nộp và thông tin nhóm
├── report/
│   ├── report.docx             # Bắt buộc theo đề
│   └── report.pdf              # Bắt buộc theo đề
├── source/                     # Source đầy đủ + hướng dẫn đã thử: bắt buộc
│   ├── README.md
│   ├── composer.json, composer.lock
│   ├── .env.example, Dockerfile, docker-compose.yml
│   └── public/, resources/, src/, config/, bootstrap/,
│       database/, bin/, deploy/, tests/, test-data/, tools/
├── video/demo.mp4              # Bắt buộc: có tiếng, đủ thành viên, <=20 phút
├── slides/{slides.pptx,slides.pdf}
├── evidence/                   # Log/raw/ảnh/biểu đồ phù hợp, không có bí mật
└── manifest.md                 # Danh mục, phiên bản, kết quả rà
```

Các file trong `submission/` ở mục 8 là bản làm việc trước đóng gói; A ánh xạ sang cấu trúc trên và cập nhật manifest, không để hai bản report/video khác nội dung cùng mang nhãn “final”. ZIP từ tag chỉ chứa source đã theo dõi bằng Git; A phải thêm report/video/slide và evidence đã duyệt vào gói tổng nếu chúng nằm ngoài source hoặc được gitignore.

### 15.2 Nội dung README do A soạn, C chạy thử độc lập

| Mục README | Thông tin cụ thể cần có | Nghiệm thu |
| --- | --- | --- |
| Giới thiệu và nhóm | Topic 9, chức năng/phạm vi, đủ tên/MSSV/lớp, bản tag/commit | Đối chiếu bìa/manifest |
| Yêu cầu máy | Docker Desktop/Linux containers hoặc Docker Engine/Compose, OS được thử, cổng, tài nguyên đo thực tế; PHP/dependency bên trong container | Không ghi “đã hỗ trợ” OS chưa thử |
| Tạo cấu hình | Copy `.env.example` thành `.env`, sinh `SESSION_SECRET` bằng lệnh cụ thể, `APP_URL`, origin, DB path, port và quyền volume | Máy sạch làm được; không yêu cầu secret riêng của người viết |
| Cài dependency | Lệnh Composer trong đúng service/container, lockfile và cách tải thư viện; nêu có cần internet lần đầu | Không chỉ ghi “cài thư viện cần thiết”; không thiếu extension |
| Khởi tạo và chạy | Lệnh build/up, seed, URL HTTP, đường WS; thứ tự lệnh và dấu hiệu health thành công | HTTP/WS/DB cùng chạy từ source gói cuối |
| Tài khoản và demo | Danh sách 3 tài khoản giả lập, mật khẩu demo, 3 phòng; cách mở 2 browser/profile và reset dữ liệu demo | Dùng được ngay; không có admin thì ghi rõ app không có vai trò admin |
| Kiểm thử | Lệnh PHPUnit; test DB riêng; checklist tay; benchmark polling/WS và nhiều socket; nơi lưu kết quả | Lệnh đã được chạy lại bởi C |
| Dừng/restart/reset | Lệnh stop/restart; cách giữ lịch sử; lệnh xóa dữ liệu chỉ rõ là reset demo/test có chủ đích | Không vô tình mất DB khi restart thông thường |
| Triển khai và lỗi thường gặp | Nginx Upgrade/proxy timeout, port bị chiếm, quyền SQLite/WAL, Origin/cookie sai, WS lỗi kết nối; TLS/WSS nếu triển khai HTTPS | Phân biệt môi trường local đã thử và cấu hình triển khai đề xuất |
| Giới hạn và cấu trúc | 1 worker, 50 tin, rate limit, phạm vi benchmark; module/directory; vị trí report/video/slide/evidence | Không cam kết tính năng chưa triển khai |

Trong kế hoạch này chưa có service/lệnh seed/test thật để chứng minh; A phải viết **lệnh copy-paste được và đã chạy thành công** sau khi có skeleton, không nộp ký hiệu `<command>` hoặc các lệnh ví dụ chưa thử. Source phải chứa dependency manifest/lock; thư viện được cài lại theo README, không phụ thuộc `vendor/` riêng trên máy tác giả.

### 15.3 Mốc đóng gói theo hạn nộp thật

| Mốc tương đối | Việc phải hoàn thành | Người làm / kiểm tra |
| --- | --- | --- |
| Ngày 1 | Điền hạn nộp chính xác kèm múi giờ/kênh nộp, lịch trình bày và quy tắc tên file; kiểm tra đăng ký thuyết trình | A / B |
| **Hạn - 48 giờ** | Bản RC chạy được; đầy đủ bản nháp report/video có tiếng; mọi R/D có chủ việc và minh chứng dự kiến | C báo trạng thái / cả nhóm |
| **Hạn - 24 giờ** | Dừng thêm tính năng; source/tag và report khớp; sửa các lỗi chặn; xong video cuối, xuất report Word/PDF | A chốt / B + C |
| **Hạn - 12 giờ** | Giải nén gói tổng ra nơi sạch, cài theo README; mở tất cả file; đo thời lượng/nghe video; điền manifest | C chạy source, B video, A báo cáo / kiểm tra chéo |
| **Hạn - 6 giờ** | Hoàn tất tải nộp theo hướng dẫn lớp; kiểm tra trạng thái thành công và tải/mở lại tệp nếu hệ thống hỗ trợ | A thao tác / B xác minh |
| Sau nộp | Giữ gói đã nộp, tag/commit tương ứng và xác nhận nộp; chuẩn bị cùng bản cho thuyết trình tuần 9-10 | C lưu / cả nhóm |

Các mốc -48/-24/-12/-6 giờ là vùng đệm do nhóm đặt; ưu tiên hoàn thành sớm hơn nếu hạn thực không cho phép đủ 7 ngày. Nếu LMS yêu cầu upload từng file, A nộp đúng từng mục; không mặc định một ZIP thay được mọi trường bắt buộc.

### 15.4 Checklist manifest bản cuối

- [ ] Đủ tên/MSSV/lớp/nhóm/đề tài/giảng viên đã xác nhận; tên tệp theo LMS; không còn `A/B/C`, `<CLASS>`, `CHƯA XÁC NHẬN` trong sản phẩm nộp.
- [ ] Ghi tag/commit source, ngày build/xuất, danh sách tệp, dung lượng, người tạo và người kiểm tra; checksum nếu cần kiểm tra tính toàn vẹn sau upload.
- [ ] Có Word + PDF, source đầy đủ + README đã cài thử, video có tiếng; thiếu một trong các điều kiện demo phải sửa ngay trước khi chốt.
- [ ] Có dependency/lock/schema/seed/test data và tài khoản demo; không có `.env` thật, runtime DB cá nhân, cookie/token hay đường dẫn chỉ tồn tại trên máy một thành viên.
- [ ] Có slide và evidence đã duyệt; link nếu dùng phải truy cập được theo hướng dẫn lớp, không chỉ trỏ file local của người dựng.
- [ ] Bảy tiêu chí R1-R4/D1-D3 có vị trí minh chứng thật; mọi số liệu/test được đối chiếu; giới hạn và mục chưa đạt được trình bày trung thực.
- [ ] Đã giải nén/chạy thử/nghe video/mở Word/PDF từ gói cuối; nội dung tương ứng cùng phiên bản và không lỗi file rỗng/hỏng.
- [ ] Đã kiểm tra tệp tải lên, trạng thái nộp và thời gian; lưu bằng chứng xác nhận đúng hạn.

## 16. Theo dõi đóng góp, xử lý chậm và điều kiện hoàn tất kế hoạch

### 16.1 Mẫu cập nhật cuối mỗi ngày

Mỗi người điền các việc của mình vào `docs/worklog.md`; C tổng hợp, người kiểm tra điền kết quả. Commit là một phần bằng chứng, không phải thước đo duy nhất: viết nội dung, đo, rà báo cáo, quay/dựng và hỗ trợ tích hợp đều phải ghi nhận.

| Ngày / thành viên | Mã việc | Ước lượng / thực tế | Trạng thái | Sản phẩm/commit/TC/EV thật | Người rà và kết quả | Việc còn vướng / bước tiếp theo |
| --- | --- | --- | --- | --- | --- | --- |
| N1 / A | A01, A02 | Theo mục 10 / CHƯA GHI | TODO | Chưa có | B/C chưa rà | Xác minh template/hạn nộp, dựng skeleton |
| N1 / B | B01 **[x] ?? XONG** | Theo mục 10 / CHƯA GHI | TODO | Chưa có | A chưa rà | Wireframe/mock và chương 1 |
| N1 / C | C01, C03 phần skeleton | Theo mục 10 / CHƯA GHI | TODO | Chưa có | A chưa rà | Schema/interface, tiến trình WS |

Cuối ngày dành 15 phút rà: việc nào có sản phẩm, việc nào fail, người nào đang bị quá tải, phụ thuộc nào cần gỡ trước, tài liệu nào chưa khớp code. Chuyển việc thì cập nhật chủ mới/người hỗ trợ, giờ thực tế và lý do; không đổi tên tác giả để làm đẹp phân công. Không để B chỉ làm slide, A/C chỉ code mà thiếu đóng góp nghiên cứu/thuyết trình.

### 16.2 Khi không kịp tiến độ

1. Giữ điều kiện bắt buộc của đề và sản phẩm chạy được: report tiếng Anh đúng mẫu/độ dài, Word/PDF, source + hướng dẫn, video có tiếng đủ người và đúng hạn.
2. Sửa trước lỗi login/WS/chat/lưu DB/cô lập phòng/không cài được, rồi lỗi minh chứng và nội dung rubric. Không dùng thời gian này mở thêm tính năng P2.
3. Giữ phần so sánh có phân tích; nếu phép đo chưa hoàn thành, ghi thật mức đã làm và giới hạn, không tạo số liệu để lấp ô. Rà lại tác động tới R2 và giao người hoàn thành phần còn thiếu.
4. Thu gọn trang trí, animation, theme, public hosting hoặc cảnh quay dư; giữ nội dung giải thích và các luồng chứng minh chủ đề. Chỉ trình bày TLS/public deployment là đã làm khi có môi trường kiểm chứng.
5. Nếu một thành viên không thể tiếp tục, cập nhật phân công và thực hiện quy định báo giảng viên; không tự giả lập phần trình bày hay đóng góp của người đó.

### 16.3 Chỉ coi là hoàn tất khi

**Về ứng dụng:** TC cốt lõi đạt, lỗi còn lại có mô tả/hạn chế rõ; cài từ gói source sạch chạy được; hai browser chat thật, dữ liệu lưu, phòng cô lập, trạng thái/reconnect/bảo mật có kiểm chứng.

**Về thang điểm:** R1 có lý thuyết sâu và nguồn; R2 có so sánh và kết quả/phạm vi thực nghiệm minh bạch; R3 giải thích được từ kiến trúc tới kết quả; R4 đúng mẫu/thông tin/ngôn ngữ/độ dài; D1 có demo đủ chức năng; D2 có luồng trình diễn rõ; D3 đủ ba người hiểu và giải thích phần mình bằng tiếng Anh, video nghe/xem được.

**Về bàn giao:** đủ Word/PDF/source/README/video cùng phiên bản; manifest được kiểm tra chéo; nộp đúng kênh/định dạng/trước hạn và có xác nhận. Các ô trong tài liệu này là kế hoạch nghiệm thu, không chứng nhận dự án đã đạt các điều kiện đó.


### Trạng thái bàn giao thực tế

**21/28 gói đã hoàn thành và kiểm tra kỹ thuật/tài liệu.** Các gói còn lại có sản phẩm hỗ trợ nhưng giữ `[ ]` vì còn thông tin lớp, người thật trình bày/ghi hình, xác nhận đóng góp hoặc nộp bài. Người dùng đã yêu cầu để trống thông tin cá nhân và tự chỉnh template sau; không hỏi lại. Xem `docs/task-status.md`, `submission/manifest.md`.


### Bản cập nhật giao diện và tài liệu tiếng Việt ngày 29/10/2026

Đã thêm tìm không dấu, bản nháp riêng theo phòng, trích dẫn, sao chép, xuất TXT, biểu tượng cảm xúc, sáng/tối, thu gọn và xem thành viên trên điện thoại. Đã chạy lại kiểm tra PHP, tích hợp và trình duyệt; xem `evidence/logs/vietnamese-validation.json`. Bản bàn giao hiện tại ở `submission/`; bản cũ được giữ trong thư mục sao lưu `tmp/`.
