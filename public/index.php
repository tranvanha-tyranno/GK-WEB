<?php
declare(strict_types=1);

use App\Http\{HttpError, JsonResponse, Router};

require_once dirname(__DIR__) . '/vendor/autoload.php';
try {
    $app = require dirname(__DIR__) . '/bootstrap/app.php';
    (new Router($app))->run();
} catch (HttpError $error) {
    JsonResponse::send(['error' => ['code' => $error->errorCode, 'message' => $error->getMessage()]], $error->status);
} catch (Throwable $error) {
    error_log($error::class . ': ' . $error->getMessage());
    JsonResponse::send(['error' => ['code' => 'SERVICE_UNAVAILABLE', 'message' => 'Dịch vụ tạm thời không khả dụng. Vui lòng thử lại.'] ], 503);
}
