<?php
declare(strict_types=1);
namespace Tests\Auth;
use App\Auth\SessionService;
use App\Repositories\SessionRepository;
use App\Http\{HttpError};
use App\Http\Middleware\{Csrf, RequireUser};
use Tests\TestCase;

final class SessionTest extends TestCase
{
    private function service(): SessionService { return new SessionService(new SessionRepository($this->db), str_repeat('s', 64)); }
    public function testAnonymousSessionAndSignedCookie(): void
    {
        $service = $this->service(); $issued = $service->issue();
        $session = $service->resolveCookie($issued['cookie']);
        self::assertNull($session['user']); self::assertSame($issued['csrfToken'], $session['csrfToken']);
        self::assertNull($service->resolveCookie(null));
        self::assertNull($service->resolveCookie('a' . substr($issued['cookie'], 1, 64) . 'invalid'));
        self::assertStringNotContainsString(explode('.', $issued['cookie'])[0], json_encode($this->db->query('SELECT * FROM sessions')->fetchAll()));
    }
    public function testLoginRotationAndRevocationAreSharedAcrossServices(): void
    {
        $service = $this->service(); $old = $service->issue(); $new = $service->issue(1, $old['id']);
        self::assertNull($service->resolveCookie($old['cookie']));
        self::assertSame(1, $this->service()->resolveCookie($new['cookie'])['user']['id']);
        $this->service()->revoke($new['id']);
        self::assertFalse($service->isActive($new['id'])); self::assertNull($service->resolveCookie($new['cookie']));
    }
    public function testExpiredSessionIsNotResolved(): void
    {
        $service = $this->service(); $issued = $service->issue(1);
        $this->db->exec('UPDATE sessions SET expires_at = 1');
        self::assertNull($service->resolveCookie($issued['cookie'])); self::assertFalse($service->isActive($issued['id']));
    }
    public function testActiveSessionMustStillBelongToTheSocketUser(): void
    {
        $service = $this->service(); $issued = $service->issue(2);
        self::assertTrue($service->isActiveForUser($issued['id'], 2));
        self::assertFalse($service->isActiveForUser($issued['id'], 1));
        $query = $this->db->prepare('UPDATE sessions SET user_id = ? WHERE id = ?');
        $query->execute([1, $issued['id']]);
        // A valid session alone must not authorize the stale Bob peer.
        self::assertTrue($service->isActive($issued['id']));
        self::assertFalse($this->service()->isActiveForUser($issued['id'], 2));
        self::assertTrue($this->service()->isActiveForUser($issued['id'], 1));
        $query->execute([null, $issued['id']]);
        self::assertFalse($service->isActiveForUser($issued['id'], 1));
    }
    public function testUserGuardRejectsRevokedExpiredAnonymousAndMissingSessions(): void
    {
        $service = $this->service();
        $alice = $service->issue(1); $bob = $service->issue(2); $anonymous = $service->issue();
        $service->revoke($alice['id']);
        self::assertFalse($service->isActiveForUser($alice['id'], 1));
        self::assertTrue($service->isActiveForUser($bob['id'], 2));
        self::assertFalse($service->isActiveForUser($anonymous['id'], 1));
        self::assertFalse($service->isActiveForUser(99999, 1));
        $this->db->exec('UPDATE sessions SET expires_at = 1');
        self::assertFalse($service->isActiveForUser($bob['id'], 2));
    }
    public function testCsrfRejectsWrongOriginEvenWithCorrectToken(): void
    {
        $this->expectException(HttpError::class);
        Csrf::check(['csrfToken' => 'correct'], 'https://evil.example', 'correct', ['http://localhost:8080']);
    }
    public function testCsrfRejectsIncorrectToken(): void
    {
        $this->expectException(HttpError::class);
        Csrf::check(['csrfToken' => 'correct'], 'http://localhost:8080', 'wrong', ['http://localhost:8080']);
    }
    public function testAnonymousCannotUseProtectedApi(): void
    {
        $this->expectException(HttpError::class); RequireUser::check(['user' => null]);
    }
}
