<?php declare(strict_types=1); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="description" content="Relay is a small, thoughtful space for live conversations. A PHP WebSocket demonstration.">
    <title><?= $page === 'chat' ? 'Your workspace' : 'Welcome' ?> · Relay</title>
    <link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/readability.css">
    <script type="module" src="/assets/js/app.js"></script>
</head>
<body data-page="<?= htmlspecialchars($page, ENT_QUOTES, 'UTF-8') ?>">
<?php require __DIR__ . '/' . $page . '.php'; ?>
<div id="toast" role="status" aria-live="polite" hidden></div>
</body>
</html>
