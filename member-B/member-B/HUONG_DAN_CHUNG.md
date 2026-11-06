# Chia mã nguồn cho 3 thành viên và đưa lên GitHub từng đợt

Các gói A/B/C chia **trách nhiệm tiếp nhận, kiểm tra và tiếp tục phát triển** mã nguồn hiện có. Lịch sử commit mới ghi thời điểm nhập/rà soát thực tế; giữ nguyên nhật ký nguồn tại `docs/worklog.md`. Mỗi người dùng tài khoản và email Git của mình, ghi đúng việc đã làm. Việc tải một gói lên không tự chứng minh người tải đã viết toàn bộ mã trong gói.

## 1. Ba gói riêng, một repository chung

Chạy từ thư mục dự án:

```powershell
python tools/build-github-handoff.py
```

Kết quả ở `github-handoff/`: ba thư mục `member-A`, `member-B`, `member-C` và ba ZIP cùng tên. Gửi đúng ZIP cho từng người. Trong mỗi gói có `dot-01`, `dot-02`, `dot-03`, hướng dẫn riêng, danh sách file và script nhập. Các file bên dưới `dot-XX/files/` giữ nguyên đường dẫn của dự án; không đưa thư mục `member-X` lên repo.

| Người | Đợt 1 | Đợt 2 | Đợt 3 |
| --- | --- | --- | --- |
| A — HTTP, xác thực, tích hợp | Composer, Docker/Nginx, bootstrap/config, hướng dẫn chạy | HTTP API, đăng nhập/phiên/CSRF, user/session repositories, test xác thực | Báo cáo Word/PDF và nguồn, tài liệu nguồn/tham chiếu, đóng gói và kiểm tra bản ghép |
| B — giao diện, trải nghiệm, trình bày | PHP views, CSS, favicon | JavaScript API/socket/chat, checklist giao diện | Slide PowerPoint/PDF, lời thuyết trình, ảnh và công cụ kiểm tra trình duyệt, phân tích so sánh |
| C — dữ liệu, realtime, kiểm thử | Schema/seed, kết nối SQLite, room/message repositories, contract giao thức | Workerman gateway, hub, limiter, handler/service, test database và realtime | Integration/benchmark, sơ đồ kiến trúc/ERD, dữ liệu đo và log kỹ thuật |

Danh sách **từng file** nằm trong `github-handoff/ownership.csv`. Một file chỉ thuộc một người và một đợt. Đây là chia theo module, không phải chia đều số lượng file; cả ba cùng chạy và giải thích được hệ thống cuối.

`.env`, dependency `vendor`, database đang chạy, cache và ZIP cũ không nằm trong các gói. Báo cáo, slide và video tham chiếu hiện có được phân bổ theo vai trò; video tham chiếu vẫn là bản im lặng, không thay video ba thành viên.

## 2. Chuẩn bị repo một lần

Một thành viên tạo **một repository trống trên GitHub**, không chọn tạo sẵn README, license hoặc .gitignore. Thêm hai bạn còn lại làm collaborator và chờ họ nhận lời mời. Nếu có repo sẵn với code, giữ lịch sử và làm tiếp trên repo đó; không xóa để tái tạo các đợt nhập này. Script sẽ dừng nếu gặp file có nội dung khác cần ghép thủ công.

Người A khởi tạo clone làm việc đầu tiên ở **thư mục mới**, tách khỏi thư mục chứa các ZIP:

```powershell
git clone https://github.com/OWNER/REPO.git C:\Projects\relay-team
Set-Location C:\Projects\relay-team
git switch --orphan main
git config user.name "TEN THAT CUA A"
git config user.email "EMAIL GITHUB CUA A"
```

Thay `OWNER/REPO` và thông tin cá nhân. Dùng email đã xác minh trên GitHub hoặc địa chỉ noreply trong GitHub Settings > Emails. Không dùng thông tin của bạn khác. Sau khi A push đợt đầu, B và C mới clone repo; mỗi bạn cấu hình `user.name`/`user.email` của riêng mình trong clone của mình.

Không cần đặt ngày commit hoặc chờ nhiều ngày cho các đợt. Chỉ push sau khi thực sự hoàn thành việc rà soát/sửa/kiểm tra của đợt đó.

## 3. Thứ tự chín lượt push

Mỗi lượt chỉ người được chỉ định push; các bạn khác đợi lượt để tránh lệch nhánh. Trong một nhóm có thể hoàn thành nhiều lượt cùng ngày.

| Lượt | Gói / đợt | Nội dung commit nhập gợi ý |
| --- | --- | --- |
| 1 | A / 1 | `chore: import project scaffold and Docker baseline` |
| 2 | C / 1 | `chore: import database and protocol baseline` |
| 3 | B / 1 | `chore: import PHP views and stylesheet baseline` |
| 4 | A / 2 | `chore: import HTTP authentication and API baseline` |
| 5 | C / 2 | `chore: import WebSocket services and server tests` |
| 6 | B / 2 | `chore: import browser chat and reconnect client` |
| 7 | C / 3 | `chore: import integration tools and experiment evidence` |
| 8 | A / 3 | `docs: import report and packaging material` |
| 9 | B / 3 | `docs: import slides and presentation material` |

Các lượt đầu là mốc ghép module, **chưa phải bản ứng dụng đầy đủ**. Sau lượt 6 mới có đủ mã ứng dụng; sau lượt 7 có đủ bộ kiểm thử/công cụ; sau lượt 9 đủ tài liệu. Các log cũ là bằng chứng lần chạy cũ, không tự coi là kết quả chạy mới của người tiếp nhận.

Mỗi lượt chạy từ clone của mình; ví dụ B đợt 2, ZIP đã giải nén tại `C:\Downloads\member-B`:

```powershell
Set-Location C:\Projects\relay-team
git switch main
git pull --ff-only origin main
git status --short
git config user.name
git config user.email

# Xem trước, sau đó nhập đúng đợt. Nếu báo lỗi thì dừng xử lý lỗi trước.
powershell -ExecutionPolicy Bypass -File C:\Downloads\member-B\Import-Round.ps1 -Round 2 -Repo C:\Projects\relay-team -Preview
powershell -ExecutionPolicy Bypass -File C:\Downloads\member-B\Import-Round.ps1 -Round 2 -Repo C:\Projects\relay-team

# Đọc code, kiểm tra/sửa phần mình; xem lại thay đổi rồi mới commit.
git diff
git status --short
git add -- .
git diff --cached --stat
git diff --cached
git commit -m "chore: import browser chat and reconnect client"
git push origin main
```

**Riêng A lượt 1:** bỏ `git pull` vì remote chưa có commit và dùng `git push -u origin main` sau commit. Những lượt khác pull trước khi nhập. Kiểm tra mã thoát của lệnh; không tiếp tục commit khi nhập hoặc kiểm tra thất bại. Script chỉ sao chép file, không commit, không push và không thay tác giả Git. Script yêu cầu cây làm việc sạch và không ghi đè file đang có nội dung khác.

Nếu repo bật branch protection, dùng nhánh cá nhân và PR theo cùng thứ tự thay cho push trực tiếp. Chọn merge giữ commit của thành viên nếu cần xem từng commit. Không force push hay sửa tác giả của lịch sử đã có.

## 4. Việc từng người cần thực sự làm

Đọc `README.md` riêng trong gói để biết việc rà soát theo ba đợt. Sau khi đủ mã (lượt 6), chạy cài đặt theo README dự án. Khi đủ bộ test (lượt 7), dùng các lệnh kiểm tra trong README và đọc kết quả thật.

- A giải thích luồng login → cookie → xác minh session → CSRF → logout; chạy test auth, kiểm tra HTTP API và cài sạch.
- B kiểm tra login/chat trên desktop/mobile, hai tài khoản khác profile, hiển thị XSS như văn bản, đổi phòng/reconnect; rà lại slide và tự tập phần nói.
- C giải thích lưu trước broadcast, cô lập phòng, presence/typing, rate limit/heartbeat; chạy PHPUnit và integration, đọc phép đo và giới hạn.

Mỗi người điền `docs/member-reviews/A.md`, `B.md` hoặc `C.md` do mình phụ trách ở đợt 3: ngày thật, thay đổi thật, lệnh chạy, kết quả, lỗi còn lại, URL commit/PR. Để trống mục chưa làm. Khi có sửa lỗi hoặc cải tiến thật, tạo commit riêng mô tả thay đổi đó, ví dụ `fix: ...` hoặc `test: ...`, rồi push ngay khi hoàn thành. Mỗi người tự thêm xác nhận đóng góp thật vào `docs/contributions.md` sau khi đã pull bản mới.

## 5. Minh chứng nộp

Giữ link repository, link commit/PR của từng thành viên, file review đã điền, ảnh kết quả test và video mỗi người giải thích module. Từ clone đã nhận đủ commit:

```powershell
git pull --ff-only origin main
git log --all --date=iso --format="%h | %an | %ae | %ad | %s"
git shortlog -sne HEAD
git log --all --numstat
```

Lấy URL cụ thể của từng commit từ GitHub để đưa vào bảng đóng góp. Số commit và dòng code giúp truy vết; phần kiểm tra, thay đổi và giải thích thực tế mới mô tả được đóng góp của từng người. Cả ba vẫn cần tự tham gia video/thuyết trình theo yêu cầu môn học.

## 6. Cập nhật lại các gói

Nếu sửa source trước khi gửi, chạy lại `python tools/build-github-handoff.py`. Builder kiểm tra đủ file, không trùng chủ sở hữu, hash nội dung và ZIP đọc được. Đầu ra cũ được giữ nguyên nếu đã tồn tại; cần chọn thư mục mới qua `--output tmp/github-handoff-v2`. Không sửa tay nội dung payload rồi dùng manifest cũ.
