<?php
declare(strict_types=1);

if (!in_array('--confirm=RESET_DEMO', $argv, true)) {
    fwrite(STDERR, "Refusing reset. This deletes demo messages and sessions. Use --confirm=RESET_DEMO deliberately.\n");
    exit(2);
}
/** @var App\Application $app */
$app = require dirname(__DIR__) . '/bootstrap/app.php';
if (!in_array($app->config['env'], ['local', 'test'], true)) {
    fwrite(STDERR, "Reset is allowed only in local/test environments.\n");
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
echo "Demo conversations reset; all sessions revoked. Restart WS to clear transient state.\n";
