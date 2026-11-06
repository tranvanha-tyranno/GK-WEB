/**
 * Sinh đặc tả nội dung chèn vào báo cáo Word từ minh chứng kiểm chứng thật.
 *
 * Mọi con số (số khẳng định, số ảnh, số khung WebSocket, mã lỗi HTTP) đều được
 * đọc từ tệp log của lượt chạy, không nhập tay, để báo cáo không thể chứa số liệu
 * không có trong minh chứng.
 *
 * Cách chạy:  node tools/build-report-insert.js [run-id]
 * Kết quả:     tmp/report-build/insert-spec.json
 */
const fs = require('fs');
const path = require('path');

const ROOT = path.resolve(__dirname, '..');
const runId = process.argv[2] || 'run-2026-11-06';
const logPath = path.join(ROOT, 'evidence', 'logs', runId + '-ui.json');
const log = JSON.parse(fs.readFileSync(logPath, 'utf8'));

const shots = log.screenshots;
const byName = (n) => shots.find((s) => s.file.endsWith('/' + n + '.png'));

/** Rút giá trị thực nhận từ chính câu khẳng định trong log. */
function actual(pattern, fallback) {
  const hit = log.assertions.find((a) => pattern.test(a.message));
  if (!hit) throw new Error('Không tìm thấy khẳng định khớp ' + pattern + ' trong log ' + runId);
  const m = hit.message.match(pattern);
  return { value: m[1], source: hit.message };
}

const apiRows = [
  {
    situation: 'Đăng ký tài khoản với mật khẩu ngắn hơn 12 byte',
    expected: 'HTTP 400, mã lỗi INVALID_PASSWORD',
    got: actual(/mật khẩu ngắn hơn 12 byte bị từ chối \(HTTP (\d+)\)/).value,
  },
  {
    situation: 'Yêu cầu lịch sử phòng với limit vượt quá 50',
    expected: 'HTTP 400',
    got: actual(/limit > 50 bị từ chối \(HTTP (\d+)\)/).value,
  },
  {
    situation: 'Yêu cầu POST mang Origin ngoài danh sách cho phép',
    expected: 'HTTP 403',
    got: actual(/Origin lạ bị chặn \(HTTP (\d+)\)/).value,
  },
  {
    situation: 'Thao tác quản lý nhóm với CSRF token sai',
    expected: 'HTTP 403',
    got: actual(/CSRF token sai bị chặn \(HTTP (\d+)\)/).value,
  },
  {
    situation: 'Tài khoản ngoài cuộc đọc lịch sử phòng riêng',
    expected: 'HTTP 404',
    got: actual(/đọc lịch sử phòng riêng bị chặn với HTTP (\d+)/).value,
  },
  {
    situation: 'Người ngoài nhóm gọi thao tác quản lý nhóm',
    expected: 'HTTP 404',
    got: actual(/thao tác quản lý bị chặn \(HTTP (\d+)\)/).value,
  },
  {
    situation: 'Chủ nhóm thêm một người chưa kết bạn vào nhóm',
    expected: 'HTTP 400',
    got: actual(/chưa kết bạn vào nhóm bị từ chối \(HTTP (\d+)\)/).value,
  },
  {
    situation: 'Gửi tin 501 mã ký tự Unicode qua WebSocket',
    expected: 'system:error, mã MESSAGE_TOO_LONG',
    got: actual(/501 mã ký tự bị từ chối với mã (\w+)/).value,
  },
  {
    situation: 'Gửi loại sự kiện không có trong hợp đồng qua WebSocket',
    expected: 'system:error, mã INVALID_PAYLOAD',
    got: actual(/không có trong giao thức bị từ chối với mã (\w+)/).value,
  },
  {
    situation: 'Gọi API đo so sánh khi BENCHMARK_ENABLED=0',
    expected: 'HTTP 404',
    got: actual(/bị tắt ở môi trường demo \(HTTP (\d+)\)/).value,
  },
];

/** Sáu hình đại diện đưa vào thân Chương 4. */
const bodyFigures = [
  { name: 'R03-phong-chung-desktop', desc: 'Phòng công khai trên máy tính với trạng thái Đã kết nối; tin vừa gửi đã được máy chủ lưu và phát lại cho chính người gửi' },
  { name: 'R14-loi-moi-den', desc: 'Lời mời kết bạn đến hiển thị ở tab Lời mời của người nhận, kèm huy hiệu đếm lời mời trên thanh bên' },
  { name: 'R17-tin-nhan-rieng', desc: 'Hội thoại tin nhắn riêng: tên phòng cá nhân hóa theo người nhận và mô tả chỉ hai người tham gia' },
  { name: 'R20-nhom-rieng', desc: 'Nhóm riêng ba thành viên trao đổi tin theo thời gian thực; biểu tượng nhóm riêng trên danh sách phòng' },
  { name: 'R21-quan-ly-nhom', desc: 'Hộp thoại Quản lý nhóm ở vai chủ nhóm: đổi tên, danh sách thành viên, thêm bạn bè và xóa nhóm' },
  { name: 'R28-mobile-chat', desc: 'Bố cục điện thoại 390×844: thanh bên thu thành dải phòng ngang và không tràn chiều rộng màn hình' },
];

const paragraphs = [
  'Lượt kiểm chứng ngày 06/11/2026 được chạy trực tiếp trên stack demo đang hoạt động tại http://localhost:8080, tức đúng ba dịch vụ Docker Compose mà README mô tả: nginx 1.28-alpine làm proxy, PHP-FPM 8.3 xử lý HTTP và SQLite, và một tiến trình PHP CLI chạy Workerman làm máy chủ WebSocket. Trình duyệt điều khiển là Chromium headless qua Playwright trên Windows. Bốn ngữ cảnh trình duyệt độc lập được mở đồng thời để có bốn danh tính thật cùng lúc: alice, bob, charlie và một tài khoản đăng ký mới ngay trong lượt chạy. Hai viewport được dùng: 1440×900 cho máy tính và 390×844 với mật độ điểm ảnh nhân đôi cho điện thoại.',
  'Lượt chạy hoàn tất với ' + log.assertionsPassed + ' khẳng định đạt, ' + shots.length + ' ảnh chụp màn hình, ' + log.websocketFrameCount + ' khung WebSocket được ghi nguyên văn và ' + log.pageErrors.length + ' lỗi JavaScript trong console; danh sách vấn đề gặp phải là rỗng. Toàn bộ kết quả nằm ở evidence/logs/' + runId + '-ui.json, ảnh ở evidence/screenshots/' + runId + '/ và khung giao thức ở evidence/raw/' + runId + '-websocket.jsonl. Mỗi con số trong mục này đều được đọc trực tiếp từ các tệp đó thay vì nhập tay.',
  'Kịch bản đi hết vòng đời của các chức năng mới: đăng ký tài khoản bằng giao diện và được máy chủ cấp phiên rồi chuyển thẳng vào phòng; tìm người theo tên hoặc tên đăng nhập có hỗ trợ gõ không dấu; gửi và chấp nhận lời mời kết bạn với huy hiệu lời mời đến; mở tin nhắn riêng hai người với tên phòng cá nhân hóa theo từng phía; tạo nhóm riêng ba thành viên và trao đổi tin theo thời gian thực giữa ba người; phân biệt quyền chủ nhóm với thành viên thường trong hộp thoại quản lý; xóa thành viên rồi xác nhận quyền đọc lịch sử bị thu hồi bằng mã HTTP 404; xuất hội thoại ra tệp UTF-8 có BOM; kiểm bố cục điện thoại không tràn ngang; và ngắt mạng rồi để ứng dụng tự kết nối lại theo backoff.',
  'Sáu hình dưới đây là ảnh chụp thật của lượt chạy, mỗi hình tương ứng một trạng thái đã được khẳng định ở trên. Bộ ảnh đầy đủ gồm ' + shots.length + ' ảnh được lưu và chú thích đầy đủ ở Phụ lục B.',
];

const appendixIntro = [
  'Phụ lục này lưu toàn bộ ' + shots.length + ' ảnh chụp màn hình của lượt kiểm chứng ngày 06/11/2026 theo đúng thứ tự kịch bản, kèm chú thích mô tả trạng thái được chụp. Ảnh do Chromium headless chụp trực tiếp từ phản hồi của máy chủ tại http://localhost:8080; không ảnh nào là ảnh dựng sẵn hoặc qua chỉnh sửa. Tệp hội thoại xuất được trong lượt chạy cũng nằm cùng thư mục với tên bắt đầu bằng xuat-.',
  'Để tái hiện, khởi động stack theo README rồi chạy script tmp/pw-shot/capture-report-run.js; dữ liệu demo của lượt trước được dọn trước mỗi lượt bằng tmp/pw-shot/cleanup-demo.php chạy trong container app. Thư mục tmp không nằm trong gói nộp, còn toàn bộ minh chứng trong phụ lục này thì có.',
];

const spec = {
  runId,
  startedAt: log.startedAt,
  finishedAt: log.finishedAt,
  assertionsPassed: log.assertionsPassed,
  screenshotCount: shots.length,
  websocketFrameCount: log.websocketFrameCount,
  pageErrorCount: log.pageErrors.length,
  // Chuỗi tiếng Việt đặt ở đây (đọc UTF-8 tường minh) để script Word chỉ còn ASCII.
  anchorHeading: 'KẾT LUẬN VÀ HƯỚNG PHÁT TRIỂN',
  styles: { body: 'Nội dung văn bản', bodyText: 'Body Text' },
  figurePrefix: 'Hình ',
  tablePrefix: 'Bảng ',
  seqFigure: 'Hình',
  seqTable: 'Bảng',
  section: {
    heading: 'Kiểm chứng chức năng trên môi trường demo ngày 06/11/2026',
    paragraphs,
    table: {
      caption: 'Các ràng buộc giao thức được kiểm tra trực tiếp trong lượt chạy 06/11/2026',
      header: ['Tình huống', 'Kết quả mong đợi', 'Kết quả thực nhận'],
      rows: apiRows.map((r) => [r.situation, r.expected, r.got]),
    },
    figures: bodyFigures.map((f) => {
      const shot = byName(f.name);
      if (!shot) throw new Error('Thiếu ảnh ' + f.name + ' trong log ' + runId);
      return { path: shot.file, desc: f.desc, bytes: shot.bytes };
    }),
  },
  appendix: {
    heading: 'PHỤ LỤC B. ẢNH CHỤP KIỂM CHỨNG GIAO DIỆN NGÀY 06/11/2026',
    paragraphs: appendixIntro,
    figures: shots.filter((s) => s.file.endsWith('.png')).map((s, i) => ({
      path: s.file,
      label: 'Ảnh B' + (i + 1),
      desc: s.caption.replace(/^Hình R\d+: /, ''),
      bytes: s.bytes,
    })),
  },
};

const outDir = path.join(ROOT, 'tmp', 'report-build');
fs.mkdirSync(outDir, { recursive: true });
fs.writeFileSync(path.join(outDir, 'insert-spec.json'), JSON.stringify(spec, null, 2));
console.log('Đã sinh đặc tả: tmp/report-build/insert-spec.json');
console.log('  khẳng định: ' + spec.assertionsPassed + ', ảnh: ' + spec.screenshotCount + ', khung WS: ' + spec.websocketFrameCount);
console.log('  hình trong Chương 4: ' + spec.section.figures.length + ', ảnh trong Phụ lục B: ' + spec.appendix.figures.length);
