<?php declare(strict_types=1); ?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <meta name="description" content="Relay — không gian trò chuyện nhóm theo thời gian thực, dễ sử dụng và hoàn toàn bằng tiếng Việt.">
    <title><?= $page === 'chat' ? 'Không gian làm việc' : 'Đăng nhập' ?> · Relay</title>
    <link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/readability.css">
    <link rel="stylesheet" href="/assets/css/workspace.css">
    <link rel="stylesheet" href="/assets/css/social.css">
    <script type="module" src="/assets/js/app.js"></script>
</head>
<body data-page="<?= htmlspecialchars($page, ENT_QUOTES, 'UTF-8') ?>">
<?php require __DIR__ . '/' . $page . '.php'; ?>
<div id="toast" role="status" aria-live="polite" hidden></div>
</body>
</html>
