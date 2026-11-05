# Relay — Trò chuyện nhóm bằng PHP và WebSocket

Ứng dụng minh họa giao tiếp thời gian thực cho học phần **503073 — Lập trình Web và Ứng dụng**, chủ đề 9 **WebSocket và giao tiếp thời gian thực**. PHP xử lý HTTP và máy chủ WebSocket; JavaScript trên trình duyệt dùng WebSocket gốc. Không cần máy chủ Node.js hoặc bước biên dịch giao diện.

## Khởi động trên Windows

Cài và mở Docker Desktop, chọn **Linux containers**, rồi chạy PowerShell tại thư mục dự án:

```powershell
powershell -ExecutionPolicy Bypass -File tools/setup.ps1
```

Lệnh tạo `.env` với khóa phiên ngẫu nhiên, dựng ảnh PHP, cài thư viện theo `composer.lock`, tạo SQLite và dữ liệu mẫu, sau đó mở ba dịch vụ. Lần đầu cần Internet để tải ảnh và thư viện. Truy cập **http://localhost:8080**.

| Tên đăng nhập | Mật khẩu | Tên hiển thị |
| --- | --- | --- |
| `alice` | `ChatDemo!2026` | Alice Nguyen |
| `bob` | `ChatDemo!2026` | Bob Tran |
| `charlie` | `ChatDemo!2026` | Charlie Le |

Đây là tài khoản giả lập; mật khẩu được băm trong SQLite. Dùng hai hồ sơ trình duyệt hoặc cửa sổ ẩn danh để đăng nhập hai người khác nhau. Các thẻ trong cùng hồ sơ dùng chung cookie và danh tính. Ứng dụng chưa có vai trò quản trị hoặc đăng ký tài khoản.

## Bạn bè, nhóm riêng và khám phá

Thanh bên có **Bạn bè** và **Tạo nhóm**, dùng cùng giao diện sáng/tối hiện tại.

- **Bạn bè**: tìm theo tên hoặc tên đăng nhập (có thể gõ không dấu), gửi/hủy lời mời, chấp nhận/từ chối lời mời đến và hủy kết bạn.
- **Gợi ý**: ưu tiên số bạn chung, sau đó số nhóm riêng chung. **Ngẫu nhiên** đổi thứ tự những người chưa kết bạn và chưa có lời mời khi nhấn làm mới.
- **Lân cận**: chủ động cấp quyền và chia sẻ vị trí để tìm người trong 10 km. Cả hai phải bật chia sẻ. Vị trí hết hiệu lực sau 24 giờ; nhấn **Tắt chia sẻ** để xóa ngay. API chỉ trả khoảng cách gần đúng, không trả tọa độ người khác. Trình duyệt cần cho phép định vị trên localhost hoặc HTTPS.
- **Tạo nhóm**: nhập tên 2–60 ký tự, chọn tối đa 30 người đã kết bạn, hoặc tạo nhóm cho riêng mình. Chỉ người được chọn được thấy nhóm, đọc lịch sử và tham gia WebSocket. Nhóm xuất hiện trên tài khoản thành viên trong khoảng 10 giây. Hủy kết bạn không xóa tư cách thành viên nhóm đã tạo.

Bản demo hiện có ba tài khoản nên gợi ý sẽ ít; lân cận chỉ có kết quả khi tài khoản khác cũng chia sẻ vị trí trong vùng. Nhóm hiện hỗ trợ chọn thành viên khi tạo, chưa có quản lý thêm/bớt thành viên sau đó.

Cập nhật cơ sở dữ liệu đang có mà không xóa hội thoại:

```powershell
docker compose exec -T app php bin/migrate-social.php
docker compose restart ws
```

Cài mới bằng `tools/setup.ps1` đã bao gồm schema này. Kiểm thử quyền riêng tư, quan hệ và khoảng cách nằm trong `tests/Database/SocialRepositoryTest.php`; luồng trình duyệt ba tài khoản ở `tools/social-browser-check.js` chạy trên môi trường riêng cổng 8088.

## Những thao tác giúp làm việc nhanh hơn

- Ba phòng hiển thị bằng tiếng Việt: **Trò chuyện chung**, **Kỹ thuật**, **Nhóm học tập**. Ứng dụng nhớ phòng mở gần nhất theo tài khoản.
- Tìm trong tin nhắn đã tải bằng nội dung hoặc tên người gửi; có thể gõ không dấu. Nhấn **Ctrl/⌘ + K** để tìm nhanh, **Esc** để xóa bộ lọc.
- Bản nháp lưu riêng từng tài khoản và phòng trong thẻ trình duyệt hiện tại. Đổi phòng hoặc tải lại trang vẫn có thể tiếp tục soạn; đăng xuất chủ động sẽ xóa bản nháp của tài khoản đó.
- **Trả lời** chèn trích dẫn văn bản tối đa 120 mã ký tự từ tin gốc; phần trích dẫn cũng tính trong giới hạn gửi 500 mã ký tự. **Sao chép** đưa nội dung vào bộ nhớ tạm.
- Chọn biểu tượng cảm xúc ở ô soạn. **Enter** gửi tin; **Shift + Enter** xuống dòng. Khi mất mạng vẫn có thể soạn, nhưng nút Gửi bị khóa đến khi tham gia lại phòng.
- **Xuất hội thoại** tải tệp `.txt` UTF-8 của toàn bộ tin đã tải trong phòng hiện tại, kể cả tin bị ẩn bởi tìm kiếm. Đây không phải xuất toàn bộ cơ sở dữ liệu.
- Mở menu **⋯** ở góc phải để đổi sáng/tối, bật **Thu gọn**, xuất hội thoại hoặc xem hướng dẫn. Hai tùy chọn được nhớ trên trình duyệt.
- Mục **Thành viên** trong menu **⋯** mở danh sách trực tuyến trên cả máy tính và điện thoại. Nút về tin mới nhất xuất hiện khi cuộn lên đọc tin cũ.

Với bản đã có dữ liệu cũ, chạy `docker compose exec -T app php bin/localize-demo.php` để đổi đúng bốn lời chào mẫu; lệnh không xóa hội thoại.

Xem hướng dẫn chi tiết tại [docs/huong-dan-su-dung.md](docs/huong-dan-su-dung.md).

## Cài thủ công trên Linux hoặc macOS

Cần Docker Engine/Desktop và Compose. Cấu hình đã được kiểm tra trên Windows với Linux containers; cần chạy lại quy trình cài sạch khi dùng hệ điều hành khác.

```sh
cp .env.example .env
docker compose build
docker compose run --rm app php -r 'echo bin2hex(random_bytes(32)), PHP_EOL;'
```

Chép chuỗi vừa sinh vào `SESSION_SECRET` trong `.env`, rồi chạy:

```sh
docker compose run --rm app composer install --no-interaction --prefer-dist
docker compose run --rm app php database/seed.php
docker compose up -d
```

Giữ `APP_URL=http://localhost:8080` và `ORIGIN_ALLOWLIST=http://localhost:8080` cho cấu hình mặc định. Địa chỉ `http://127.0.0.1:8080` có Origin khác, nên bị từ chối nếu chưa thêm vào danh sách.

## Kiến trúc và cấu hình

```text
Trình duyệt → Nginx localhost:8080
              /api và trang → PHP-FPM → SQLite
              /ws           → PHP CLI Workerman (1 worker) → cùng SQLite
```

Chỉ Nginx được mở ra máy chủ tại `127.0.0.1`. Cổng WebSocket 8081 và PHP-FPM 9000 nằm trong Docker. Volume `chat-data` chứa SQLite cùng các tệp WAL/SHM; mã nguồn được gắn vào để phát triển. Volume Docker tránh lỗi quyền ghi đồng thời trên thư mục Windows.

| Biến cấu hình | Ý nghĩa |
| --- | --- |
| `APP_ENV` | Mặc định `local`, nhận diện môi trường |
| `APP_URL` | Origin của trình duyệt; ảnh hưởng chính sách cookie, dùng HTTPS khi có TLS |
| `DATABASE_PATH` | `/app/storage/chat.sqlite` trong volume chung |
| `SESSION_SECRET` | Ít nhất 32 ký tự ngẫu nhiên, giống nhau ở hai tiến trình PHP |
| `ORIGIN_ALLOWLIST` | Các Origin chính xác, phân cách bằng dấu phẩy, không có dấu `/` cuối |
| `WS_HOST`, `WS_PORT` | Địa chỉ lắng nghe nội bộ; mặc định `0.0.0.0:8081` |
| `BENCHMARK_ENABLED` | Mặc định `0`; đặt `1` để bật API nhận tin bằng truy vấn định kỳ phục vụ đo so sánh |

Ảnh dùng PHP 8.3 với PDO SQLite, mbstring, pcntl/posix và phần mở rộng XML cho PHPUnit. Phiên bản thư viện được khóa trong `composer.lock`; thông tin lần chạy nằm trong thư mục minh chứng.

## Chức năng và giới hạn kỹ thuật

Đăng nhập/đăng xuất dùng cookie phiên có chữ ký, HttpOnly, xoay phiên, kiểm tra Origin/CSRF và thu hồi phiên qua SQLite chung. Mỗi socket chỉ tham gia một phòng; tin nhắn, trạng thái trực tuyến và trạng thái đang nhập được giới hạn trong phòng.

Máy chủ lưu tin trước khi phát, gửi lại cho cả người gửi, kiểm tra Unicode và giới hạn nội dung **500 mã ký tự Unicode** (không phải 500 cụm ký tự hiển thị). JSON tối đa 8192 byte. Workerman giới hạn khung và tin phân mảnh; lỗi quá lớn có thể đóng socket trước khi gửi được lỗi JSON. Mỗi tài khoản được gửi 20 tin được chấp nhận trong cửa sổ trượt 60 giây, dùng chung cho mọi thẻ. Ghi thất bại không trừ lượt gửi thành công.

Danh sách trực tuyến gộp các socket cùng tài khoản. Trạng thái nhập hết hạn sau 3 giây. Ping/pong loại kết nối không phản hồi sau khoảng 55–56 giây. Phiên được kiểm tra mỗi sự kiện, trước khi giao tin và mỗi giây.

Tự kết nối lại sau 1/2/4/8/10 giây, tham gia lại phòng và gộp lịch sử theo ID. Tin gửi chưa rõ kết quả được giữ để kiểm tra, không tự gửi lại. API lịch sử chỉ khôi phục **50 tin mới nhất**; ngoại tuyến quá lâu có thể thiếu tin cũ. Gộp ID không đảm bảo chỉ lưu hoặc giao đúng một lần trong mọi tình huống.

Một tiến trình Workerman giữ trạng thái phòng, nhập và giới hạn trong RAM. Muốn tăng số worker cần trạng thái dùng chung và pub/sub. SQLite ghi đồng bộ; bộ kiểm tra 20 socket chỉ chứng minh phát tin đúng trong phạm vi đó. Chưa có tin nhắn riêng, tải tệp, đăng ký hoặc mở rộng nhiều máy. Văn bản dùng `textContent`, SQL dùng tham số ràng buộc. Nội dung do người dùng gửi được giữ nguyên ngôn ngữ.

## Lệnh kiểm tra

```powershell
docker compose ps
docker compose logs --tail 50 app ws nginx
docker compose run --rm app composer lint
docker compose run --rm app composer test
docker compose run --rm app composer validate --strict
```

PHPUnit dùng cơ sở dữ liệu trong bộ nhớ. Kiểm thử tích hợp HTTP/WebSocket dùng ngăn xếp riêng ở cổng 8088 và volume riêng:

```powershell
python -m pip install -r tools/requirements.txt
docker compose -p relay-test -f tests/compose.yml run --rm app php database/seed.php
docker compose -p relay-test -f tests/compose.yml up -d
docker compose -p relay-test -f tests/compose.yml restart ws
python tools/integration.py
```

Chạy tuần tự từng bộ tích hợp hoặc đo so sánh vì dùng chung giới hạn theo tài khoản. Khởi động lại dịch vụ WS của **môi trường test** xóa bộ đếm RAM mà không xóa tin. Kết quả tích hợp ở `evidence/logs/integration.json`. Công cụ dùng IPv4 loopback để tránh chờ IPv6 trên Windows và vẫn giữ Origin cấu hình.

API đo `GET /api/benchmark/rooms/{id}/messages?afterId=N` chỉ bật trong môi trường test; nó không thay đổi cam kết khôi phục 50 tin của sản phẩm. Xem `tests/manual-acceptance.md`, `docs/test-cases.md` và `docs/experiment-plan.md`. Log ngày 22/10/2026 là kết quả lịch sử; kiểm tra bản Việt hóa được ghi riêng, không gắn kết quả cũ thành lần chạy mới.

## Dừng và bảo toàn dữ liệu

```powershell
docker compose stop
docker compose start
docker compose restart ws app
docker compose down
```

Các lệnh trên giữ volume dữ liệu; sau `down` dùng `docker compose up -d`. Chạy seed nhiều lần không tạo trùng tài khoản hoặc tin chào. Muốn xóa dữ liệu mẫu có chủ đích, xem `docs/demo-script.md`. `docker compose down -v` xóa volume, vì vậy chỉ dùng khi thực sự muốn xóa dữ liệu.

## Xử lý sự cố

| Hiện tượng | Cách kiểm tra |
| --- | --- |
| Lỗi Docker daemon/pipe | Mở Docker Desktop, chọn Linux containers, đợi `docker info` hoạt động |
| Cổng 8080 đã dùng | Dừng dịch vụ xung đột hoặc đổi đồng thời cổng Nginx, `APP_URL` và Origin |
| Thiếu autoload/thư viện | Chạy `docker compose run --rm app composer install` |
| HTTP 503 | Xem log, kiểm tra độ dài khóa phiên, schema/seed và quyền volume |
| WS bị từ chối 403 | Mở đúng Origin, đăng nhập, kiểm tra cookie/allowlist; không gắn token phiên vào URL |
| WS liên tục kết nối lại | Xem log WS/Nginx, cấu hình Upgrade `/ws`, tình trạng Workerman và hạn phiên |
| SQLite chỉ đọc | Dùng volume chung; dựng lại nếu thay entrypoint, bảo đảm cả hai tiến trình ghi được WAL/SHM |
| Người mất mạng vẫn hiện trực tuyến | Đóng bình thường cập nhật ngay; mất mạng đột ngột cần chờ heartbeat |
| Chạm giới hạn khi demo | Chờ 60 giây; chỉ khởi động lại WS để xóa bộ đếm trong môi trường demo/test có chủ đích |
| Bản nháp không còn sau đóng cửa sổ | Bản nháp dùng sessionStorage theo thẻ; chế độ riêng tư hoặc chính sách bộ nhớ có thể chặn lưu |

## Triển khai HTTPS và tài liệu

Bản local dùng HTTP/WS trên loopback. Triển khai TLS/WSS công khai chưa được xác nhận. Cần chứng chỉ thật, proxy Upgrade HTTP/1.1, thời gian chờ phù hợp, `APP_URL=https://...`, Origin HTTPS chính xác và CSP `connect-src` tương ứng. Trình duyệt tự chọn `wss:` trên trang HTTPS; cookie trở thành Secure khi `APP_URL` dùng HTTPS. Xem `deploy/README.md`.

`src/Http` chứa HTTP API; `src/Auth` quản lý phiên; `src/Repositories` chứa SQL; `src/Realtime` xử lý kết nối/phòng; `src/Protocols` là bộ tiếp hợp chỉ nhận văn bản; `public/assets/js` là mã trình duyệt. `docs/` chứa tài liệu tiếng Việt, `evidence/` giữ kết quả chạy thực tế, `submission/` chứa Word/PDF, slide và gói nguồn.

Phân công và nhập mã từng đợt: `docs/github-handoff.md`; dựng gói bằng `python tools/build-github-handoff.py`. Điền thông tin nhóm/lớp/giảng viên và xác nhận hạn nộp bằng thông tin thật. Tài liệu được chuyển sang tiếng Việt theo yêu cầu ngày 28/10/2026; bản yêu cầu môn học gốc vẫn có quy định thuyết trình tiếng Anh, cần đối chiếu khi nộp. Chưa có video ba thành viên thật hoặc xác nhận nộp chính thức.

Tệp kế hoạch Word ở thư mục gốc là kế hoạch ban đầu, có lựa chọn công nghệ cũ. Kiến trúc PHP đang chạy và tài liệu hiện hành được mô tả trong README, `PHAN_CONG_PHP_3_NGUOI.md` và `docs/`. Bản cập nhật hoàn tất ngày 29/10/2026 (Asia/Saigon).
