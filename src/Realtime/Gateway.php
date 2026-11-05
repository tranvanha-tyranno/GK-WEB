<?php
declare(strict_types=1);
namespace App\Realtime;
use App\Application;
use App\Services\ChatService;
use App\Shared\ProtocolError;
use Workerman\{Worker, Timer};
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;

final class Gateway
{
    private RoomHub $hub;
    private RateLimiter $limiter;
    private EventHandler $handler;
    public function __construct(private readonly Application $app)
    {
        $this->hub = new RoomHub();
        $this->limiter = new RateLimiter();
        $this->handler = new EventHandler($app->rooms, $this->hub, new ChatService($app->messages, $this->hub, $this->limiter));
    }
    public function bind(Worker $worker): void
    {
        $worker->onConnect = static function (TcpConnection $connection): void {
            // RFC frame headers add up to 14 bytes; the application checks reassembled JSON at 8192.
            $connection->maxPackageSize = 8192 + 14;
            $connection->maxSendBufferSize = 256 * 1024;
            $connection->context->openedAt = microtime(true);
            $connection->onBufferFull = static fn (TcpConnection $c) => $c->destroy();
        };
        $worker->onWebSocketConnect = function (TcpConnection $connection, Request $request): void {
            try {
                $session = $this->app->sessions->resolveCookie($request->cookie('chat_session'));
                if ($request->path() !== '/ws' || !in_array($request->header('origin', ''), $this->app->config['origins'], true) || $session === null || $session['user'] === null) {
                    $connection->close("HTTP/1.1 403 Forbidden\r\nConnection: close\r\nContent-Length: 9\r\n\r\nForbidden", true);
                    return;
                }
                $connection->websocketType = "\x81";
                $peer = new Peer($connection->id, $session,
                    function (string $json) use ($connection, $session): void {
                        try {
                            if (!$this->app->sessions->isActive($session['id'])) { $connection->destroy(); return; }
                            $connection->send($json);
                        } catch (\Throwable $error) { error_log($error::class . ': websocket delivery failed'); $connection->destroy(); }
                    }, static fn () => $connection->destroy());
                $connection->context->peer = $peer;
                $this->hub->attach($peer);
            } catch (\Throwable $error) {
                error_log($error::class . ': websocket authentication unavailable');
                $connection->close("HTTP/1.1 503 Service Unavailable\r\nConnection: close\r\nContent-Length: 0\r\n\r\n", true);
            }
        };
        $worker->onMessage = function (TcpConnection $connection, string $raw): void {
            $peer = $connection->context->peer ?? null;
            if (!$peer instanceof Peer) { $connection->destroy(); return; }
            try {
                if (!$this->app->sessions->isActive($peer->session['id'])) { $connection->destroy(); return; }
                if ($connection->context->relayBinary ?? false) { throw new ProtocolError('INVALID_PAYLOAD', 'Không hỗ trợ tin nhắn dạng nhị phân.'); }
                $this->handler->handle($peer, $raw);
            } catch (ProtocolError $error) {
                $peer->send('system:error', ['code' => $error->errorCode, 'message' => $error->getMessage()]);
            } catch (\Throwable $error) {
                error_log($error::class . ': websocket event failed');
                $peer->send('system:error', ['code' => 'INTERNAL_ERROR', 'message' => 'Không lưu được tin nhắn. Vui lòng thử lại.']);
            }
        };
        $worker->onWebSocketPong = static function (TcpConnection $connection): void {
            if (isset($connection->context->peer)) { $connection->context->peer->lastPong = microtime(true); }
        };
        $worker->onClose = function (TcpConnection $connection): void {
            if (isset($connection->context->peer)) { $this->hub->detach($connection->context->peer); }
        };
        Timer::add(1, function () use ($worker): void {
            $now = microtime(true);
            foreach ($worker->connections as $connection) {
                $peer = $connection->context->peer ?? null;
                if (!$peer instanceof Peer) {
                    if ($now - ($connection->context->openedAt ?? $now) > 10) { $connection->destroy(); }
                    continue;
                }
                try {
                    if (!$this->app->sessions->isActive($peer->session['id']) || $now - $peer->lastPong > 55) { $connection->destroy(); continue; }
                    if ($now - $peer->lastPing >= 20) {
                        $connection->websocketType = "\x89";
                        $connection->send('relay');
                        $connection->websocketType = "\x81";
                        $peer->lastPing = $now;
                    }
                } catch (\Throwable $error) { error_log($error::class . ': websocket heartbeat failed'); $connection->destroy(); }
            }
            $this->hub->expireTyping($now);
            $this->limiter->cleanup($now);
        });
    }
}
