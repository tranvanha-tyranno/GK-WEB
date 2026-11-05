<?php
declare(strict_types=1);
namespace App\Shared;

final class Validator
{
    public static function event(string $raw): array
    {
        if (strlen($raw) > 8192) { throw new ProtocolError('PAYLOAD_TOO_LARGE', 'Dữ liệu tin nhắn không được vượt quá 8192 byte.'); }
        try { $event = json_decode($raw, false, 16, JSON_THROW_ON_ERROR); }
        catch (\JsonException) { throw new ProtocolError('INVALID_PAYLOAD', 'Dữ liệu phải là đối tượng JSON hợp lệ.'); }
        if (!$event instanceof \stdClass || !isset($event->type, $event->payload) || !is_string($event->type) || !$event->payload instanceof \stdClass) {
            throw new ProtocolError('INVALID_PAYLOAD', 'Cần có trường type và đối tượng payload.');
        }
        if (array_diff(array_keys((array) $event), ['type', 'payload'])) { throw new ProtocolError('INVALID_PAYLOAD', 'Thông điệp chứa trường không được hỗ trợ.'); }
        $payload = (array) $event->payload;
        $fields = match ($event->type) {
            'room:join' => ['roomId'], 'chat:send' => ['roomId', 'content'], 'typing:set' => ['roomId', 'isTyping'],
            default => throw new ProtocolError('INVALID_PAYLOAD', 'Loại sự kiện không được hỗ trợ.'),
        };
        if (array_diff(array_keys($payload), $fields) || array_diff($fields, array_keys($payload))) { throw new ProtocolError('INVALID_PAYLOAD', 'Các trường dữ liệu không hợp lệ.'); }
        if (!is_int($payload['roomId']) || $payload['roomId'] < 1 || $payload['roomId'] > 999999999) { throw new ProtocolError('INVALID_PAYLOAD', 'roomId phải là số nguyên dương.'); }
        if ($event->type === 'chat:send') { $payload['content'] = self::content($payload['content']); }
        if ($event->type === 'typing:set' && !is_bool($payload['isTyping'])) { throw new ProtocolError('INVALID_PAYLOAD', 'isTyping phải là giá trị đúng hoặc sai.'); }
        return ['type' => $event->type, 'payload' => $payload];
    }
    public static function content(mixed $content): string
    {
        if (!is_string($content) || !mb_check_encoding($content, 'UTF-8')) { throw new ProtocolError('INVALID_PAYLOAD', 'Nội dung phải là văn bản UTF-8.'); }
        $content = preg_replace('/\A[\s\p{Z}]+|[\s\p{Z}]+\z/u', '', $content);
        if ($content === '') { throw new ProtocolError('INVALID_PAYLOAD', 'Hãy nhập nội dung tin nhắn trước.'); }
        if (mb_strlen($content, 'UTF-8') > 500) { throw new ProtocolError('MESSAGE_TOO_LONG', 'Tin nhắn được chứa tối đa 500 mã ký tự Unicode.'); }
        return $content;
    }
}
