<?php
declare(strict_types=1);

// Chỉ đổi bốn lời chào mẫu đã biết, giữ nguyên tin do người dùng soạn.
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$originals = [
    'Welcome to Relay. Choose a room and say hello!',
    'Messages arrive live. No page refresh needed.',
    'Use this room to discuss the WebSocket implementation.',
    'A quiet space to prepare for the midterm presentation.',
];
$samples = json_decode(file_get_contents(dirname(__DIR__) . '/test-data/messages.json'), true, 512, JSON_THROW_ON_ERROR);
$app->db->beginTransaction();
try {
    $update = $app->db->prepare('UPDATE messages SET content = ? WHERE content = ? AND user_id = (SELECT id FROM users WHERE username = ?) AND room_id = (SELECT id FROM rooms WHERE name = ?)');
    $count = 0;
    foreach ($samples as $index => $sample) {
        $update->execute([$sample['content'], $originals[$index], $sample['username'], $sample['room']]);
        $count += $update->rowCount();
    }
    $app->db->commit();
    echo "Đã chuyển $count lời chào mẫu sang tiếng Việt. Hội thoại khác được giữ nguyên.\n";
} catch (Throwable $error) {
    $app->db->rollBack();
    throw $error;
}
