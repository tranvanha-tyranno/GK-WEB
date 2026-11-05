<?php
declare(strict_types=1);
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->db->exec(file_get_contents(dirname(__DIR__) . '/database/schema.sql'));
$app->db->exec('DROP TABLE IF EXISTS user_locations');
echo "Social schema ready; existing account, room, and message data preserved; stored location data removed.\n";
