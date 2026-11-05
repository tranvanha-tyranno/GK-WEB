<?php
declare(strict_types=1);

use App\Realtime\Gateway;
use Workerman\Worker;

require_once dirname(__DIR__) . '/vendor/autoload.php';
$config = require dirname(__DIR__) . '/config/app.php';
$worker = new Worker('textWebsocket://' . $config['wsHost'] . ':' . $config['wsPort']);
$worker->count = 1;
$worker->name = 'relay-chat';
Worker::$logFile = dirname(__DIR__) . '/storage/workerman.log';
Worker::$pidFile = dirname(__DIR__) . '/storage/workerman.pid';
$worker->onWorkerStart = static function (Worker $worker): void {
    // Open PDO inside the worker process, never share a pre-fork PDO handle.
    $app = require dirname(__DIR__) . '/bootstrap/app.php';
    (new Gateway($app))->bind($worker);
};
Worker::runAll();
