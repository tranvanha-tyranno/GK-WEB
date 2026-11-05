<?php
declare(strict_types=1);
namespace Protocols;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Websocket;

/** Delegate all RFC 6455 parsing to Workerman; remember the data-message type. */
final class TextWebsocket extends Websocket
{
    public static function decode(string $buffer, TcpConnection $connection): string
    {
        $opcode = ord($buffer[0]) & 0x0f;
        if ($opcode === 1 || $opcode === 2) { $connection->context->relayBinary = $opcode === 2; }
        return parent::decode($buffer, $connection);
    }
}
