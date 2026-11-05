# Phương pháp đo polling và WebSocket

Chỉ chạy trên ngăn xếp kiểm thử riêng tại `http://localhost:8088`. Dừng kiểm thử tích hợp trước. Bản demo 8080 dùng dữ liệu riêng. Cài `tools/requirements.txt`, tạo seed và chạy `tests/compose.yml`, rồi:

```powershell
docker compose -p relay-test -f tests/compose.yml restart ws
python tools/benchmark/run.py
```

Mỗi phương thức nhận có ba lần chạy 120 giây, xen kẽ polling/WS, WS/polling, polling/WS. Mỗi lần người gửi và nhận vào cùng phòng, gửi 20 tin có dấu nhận diện riêng cách nhau 6 giây, bắt đầu sau 0,17 giây. Polling lấy ID mới mỗi giây, lệch đầu 0,55 giây. Người gửi đều dùng WebSocket; vì thế đây là so sánh **kênh nhận/cập nhật**, không phải hai ngăn xếp hoàn toàn khác. Thời gian đăng nhập/kết nối nằm ngoài cửa sổ đo.

Mốc gửi và nhận dùng `time.perf_counter` trong cùng tiến trình Python. Khoảng đo gồm kiểm tra dữ liệu, lưu, phát hoặc chờ polling, mạng loopback và lịch điều phối Python/luồng. Không gọi đó là độ trễ mạng một chiều thuần. Giữ lại timeout/lỗi máy chủ trong kết quả. Tính trung vị và p95 theo phương pháp hạng gần nhất; 20 mẫu/lần là nhỏ, chỉ có ý nghĩa mô tả.

Đếm số yêu cầu HTTP polling, số sự kiện WS ứng dụng và byte JSON UTF-8 nhận. Không tính tiêu đề HTTP, TCP, khung WS hoặc TLS. JSON không đồng nghĩa băng thông trên đường truyền. WS còn nhận sự kiện trạng thái phòng, nên hai bên không có lượng nội dung ứng dụng hoàn toàn tương đương. Mỗi phương thức có thêm 60 giây nhàn rỗi; ping/pong do thư viện xử lý không nằm trong số sự kiện ứng dụng.

Bản ghi từng tin và tổng hợp ở `evidence/raw/`. Tùy chọn `--quick` chỉ chạy một lần 12 giây/hai tin mỗi phương thức để kiểm tra sơ bộ, không thay sáu lần đo cuối. Chạy từng bộ tuần tự để tránh giới hạn tài khoản và nhiễu. Ghi phiên bản ảnh, runtime và máy chủ trong minh chứng môi trường.

Kiểm tra đồng thời 2/10/20 socket là kiểm tra chức năng riêng, có thể nhiều socket cùng tài khoản. Nó chứng minh phát tin và thành viên trong phạm vi đó, không chứng minh số người độc lập hoặc thông lượng tối đa. Độ trễ WAN, tải lớn, TLS công khai, nhiều máy và khôi phục ngoại tuyến dài chưa nằm trong phép đo.
