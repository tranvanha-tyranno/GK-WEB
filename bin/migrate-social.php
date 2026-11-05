<?php
declare(strict_types=1);
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->db->exec(file_get_contents(dirname(__DIR__) . '/database/schema.sql'));
echo "Social schema ready; existing data preserved.\n";
