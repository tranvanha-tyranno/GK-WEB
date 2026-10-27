<?php
declare(strict_types=1);

/** @var App\Application $app */
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$db = $app->db;
$db->exec(file_get_contents(__DIR__ . '/schema.sql'));
$db->beginTransaction();
try {
    $users = json_decode(file_get_contents(dirname(__DIR__) . '/test-data/accounts.json'), true, 512, JSON_THROW_ON_ERROR);
    $insert = $db->prepare('INSERT OR IGNORE INTO users(username, display_name, password_hash) VALUES (?, ?, ?)');
    foreach ($users as $user) {
        $insert->execute([$user['username'], $user['displayName'], password_hash($user['password'], PASSWORD_DEFAULT)]);
    }
    $insertRoom = $db->prepare('INSERT OR IGNORE INTO rooms(name) VALUES (?)');
    foreach (['General', 'Engineering', 'Study group'] as $room) {
        $insertRoom->execute([$room]);
    }
    // Only add welcome messages to an empty database. Repeated seed never duplicates messages.
    if ((int) $db->query('SELECT COUNT(*) FROM messages')->fetchColumn() === 0) {
        $messages = json_decode(file_get_contents(dirname(__DIR__) . '/test-data/messages.json'), true, 512, JSON_THROW_ON_ERROR);
        foreach ($messages as $message) {
            $user = $app->users->findByUsername($message['username']);
            $room = $db->prepare('SELECT id FROM rooms WHERE name = ?');
            $room->execute([$message['room']]);
            $app->messages->create((int) $user['id'], (int) $room->fetchColumn(), $message['content']);
        }
    }
    $db->commit();
    echo "Schema and demo data ready. Re-running seed preserves existing data.\n";
} catch (Throwable $error) {
    $db->rollBack();
    throw $error;
}
