<?php
declare(strict_types=1);
namespace Tests\Realtime;
use App\Shared\{ProtocolError, Validator};
use PHPUnit\Framework\Attributes\DataProvider;

final class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    public function testUnicodeCodePointsAndWhitespace(): void
    {
        self::assertSame(str_repeat('😀', 500), Validator::content('  ' . str_repeat('😀', 500) . '  '));
        self::assertSame('Xin chào', Validator::content("\u{00a0}Xin chào\u{00a0}"));
        $this->expectException(ProtocolError::class); Validator::content(str_repeat('á', 501));
    }
    public function testEnvelopeNormalizesChatContent(): void
    {
        $event = Validator::event('{"type":"chat:send","payload":{"roomId":1,"content":" hello "}}');
        self::assertSame('hello', $event['payload']['content']);
    }
    #[DataProvider('invalidEvents')]
    public function testInvalidEventsAreRejected(string $raw): void
    { $this->expectException(ProtocolError::class); Validator::event($raw); }
    public static function invalidEvents(): array
    {
        return array_map(static fn (string $value): array => [$value], [
            '{bad', '[]', 'null', '{"type":"unknown","payload":{}}',
            '{"type":"room:join","payload":{"roomId":"1"}}', '{"type":"room:join","payload":{"roomId":0}}',
            '{"type":"room:join","payload":{"roomId":1,"userId":2}}',
            '{"type":"typing:set","payload":{"roomId":1,"isTyping":1}}',
            '{"type":"chat:send","payload":{"roomId":1,"content":"  "}}',
            '{"type":"chat:send","payload":{"roomId":1,"content":[]}}', str_repeat('x', 8193),
        ]);
    }
}
