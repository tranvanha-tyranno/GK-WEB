<?php
declare(strict_types=1);

if (!in_array('--confirm=RESET_DEMO', $argv, true)) {
    fwrite(STDERR, "Chưa xóa dữ liệu. Thao tác này xóa tin nhắn và phiên demo. Chỉ dùng --confirm=RESET_DEMO khi thực sự muốn xóa.\n");
    exit(2);
}
/** @var App\Application $app */
$app = require dirname(__DIR__) . '/bootstrap/app.php';
if (!in_array($app->config['env'], ['local', 'test'], true)) {
    fwrite(STDERR, "Chỉ cho phép xóa dữ liệu trong môi trường local/test.\n");
    exit(2);
}
$app->db->beginTransaction();
try {
    $app->db->exec('DELETE FROM messages; DELETE FROM sessions;');
    $app->db->commit();
} catch (Throwable $error) {
    $app->db->rollBack();
    throw $error;
}
require dirname(__DIR__) . '/database/seed.php';
echo "Đã đặt lại hội thoại mẫu và thu hồi mọi phiên. Khởi động lại WS để xóa trạng thái tạm.\n";
