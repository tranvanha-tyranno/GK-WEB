<?php
declare(strict_types=1);
namespace App\Shared;

final class Validator
{
    public static function event(string $raw): array
    {
        if (strlen($raw) > 8192) { throw new ProtocolError('PAYLOAD_TOO_LARGE', 'Messages must be at most 8192 bytes.'); }
        try { $event = json_decode($raw, false, 16, JSON_THROW_ON_ERROR); }
        catch (\JsonException) { throw new ProtocolError('INVALID_PAYLOAD', 'Expected a valid JSON object.'); }
        if (!$event instanceof \stdClass || !isset($event->type, $event->payload) || !is_string($event->type) || !$event->payload instanceof \stdClass) {
            throw new ProtocolError('INVALID_PAYLOAD', 'Expected type and an object payload.');
        }
        if (array_diff(array_keys((array) $event), ['type', 'payload'])) { throw new ProtocolError('INVALID_PAYLOAD', 'Unknown envelope fields.'); }
        $payload = (array) $event->payload;
        $fields = match ($event->type) {
            'room:join' => ['roomId'], 'chat:send' => ['roomId', 'content'], 'typing:set' => ['roomId', 'isTyping'],
            default => throw new ProtocolError('INVALID_PAYLOAD', 'Unknown event type.'),
        };
        if (array_diff(array_keys($payload), $fields) || array_diff($fields, array_keys($payload))) { throw new ProtocolError('INVALID_PAYLOAD', 'Invalid payload fields.'); }
        if (!is_int($payload['roomId']) || $payload['roomId'] < 1 || $payload['roomId'] > 999999999) { throw new ProtocolError('INVALID_PAYLOAD', 'roomId must be a positive integer.'); }
        if ($event->type === 'chat:send') { $payload['content'] = self::content($payload['content']); }
        if ($event->type === 'typing:set' && !is_bool($payload['isTyping'])) { throw new ProtocolError('INVALID_PAYLOAD', 'isTyping must be boolean.'); }
        return ['type' => $event->type, 'payload' => $payload];
    }
    public static function content(mixed $content): string
    {
        if (!is_string($content) || !mb_check_encoding($content, 'UTF-8')) { throw new ProtocolError('INVALID_PAYLOAD', 'Content must be UTF-8 text.'); }
        $content = preg_replace('/\A[\s\p{Z}]+|[\s\p{Z}]+\z/u', '', $content);
        if ($content === '') { throw new ProtocolError('INVALID_PAYLOAD', 'Write a message first.'); }
        if (mb_strlen($content, 'UTF-8') > 500) { throw new ProtocolError('MESSAGE_TOO_LONG', 'Messages can contain up to 500 Unicode code points.'); }
        return $content;
    }
}
