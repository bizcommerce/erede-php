<?php

declare(strict_types=1);

namespace Rede\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rede\Auth\AccessToken;
use Rede\Environment;
use Rede\Service\AuthenticationService;
use Rede\Store;
use Rede\Tests\Support\MockTransport;
use RuntimeException;

#[CoversClass(AuthenticationService::class)]
final class AuthenticationServiceTest extends TestCase
{
    private function store(): Store
    {
        return new Store('PV-123', 'secret-key', Environment::sandbox());
    }

    private function okBody(string $token = 'tok-1', ?int $expiresIn = 1440, ?string $type = 'Bearer'): string
    {
        $payload = ['access_token' => $token];

        if ($expiresIn !== null) {
            $payload['expires_in'] = $expiresIn;
        }

        if ($type !== null) {
            $payload['token_type'] = $type;
        }

        return json_encode($payload);
    }

    #[Test]
    public function it_fetches_a_token_once_and_caches_it(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, $this->okBody('tok-1'));
        $auth = new AuthenticationService($this->store(), $transport->http);

        $first = $auth->getToken();
        $second = $auth->getToken();

        self::assertSame('tok-1', $first->getAccessToken());
        self::assertSame($first, $second);
        self::assertCount(1, $transport->requests(), 'A cached, unexpired token must not trigger a second request.');
    }

    #[Test]
    public function it_sends_the_client_credentials_grant_with_basic_auth(): void
    {
        $store = $this->store();
        $transport = new MockTransport();
        $transport->queue(200, $this->okBody());

        (new AuthenticationService($store, $transport->http))->getToken();

        $request = $transport->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame($store->getEnvironment()->getTokenEndpoint(), (string) $request->getUri());
        self::assertSame('grant_type=client_credentials', (string) $request->getBody());
        self::assertSame('application/x-www-form-urlencoded', $request->getHeaderLine('Content-Type'));
        self::assertSame('Basic ' . base64_encode('PV-123:secret-key'), $request->getHeaderLine('Authorization'));
    }

    #[Test]
    public function it_persists_the_fetched_token_under_the_store_cache_key(): void
    {
        $store = $this->store();
        $transport = new MockTransport();
        $transport->queue(200, $this->okBody('tok-1'));

        $token = (new AuthenticationService($store, $transport->http))->getToken();

        self::assertSame($token, $store->getTokenStore()->get($store->getTokenCacheKey()));
    }

    #[Test]
    public function it_refetches_when_the_cached_token_is_expired(): void
    {
        $store = $this->store();
        $store->getTokenStore()->save($store->getTokenCacheKey(), new AccessToken('stale', 'Bearer', time() - 1));

        $transport = new MockTransport();
        $transport->queue(200, $this->okBody('fresh'));

        $token = (new AuthenticationService($store, $transport->http))->getToken();

        self::assertSame('fresh', $token->getAccessToken());
        self::assertCount(1, $transport->requests());
    }

    #[Test]
    public function refresh_clears_the_cache_and_fetches_a_brand_new_token(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, $this->okBody('tok-1'));
        $transport->queue(200, $this->okBody('tok-2'));
        $auth = new AuthenticationService($this->store(), $transport->http);

        $first = $auth->getToken();
        $refreshed = $auth->refreshToken();

        self::assertSame('tok-1', $first->getAccessToken());
        self::assertSame('tok-2', $refreshed->getAccessToken());
        self::assertCount(2, $transport->requests());
    }

    #[Test]
    public function it_applies_the_safety_margin_to_the_reported_lifetime(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, $this->okBody('tok', 120)); // 120s - 60s margin = 60s
        $auth = new AuthenticationService($this->store(), $transport->http);

        $before = time();
        $token = $auth->getToken();
        $after = time();

        self::assertGreaterThanOrEqual($before + 60, $token->getExpiresAt());
        self::assertLessThanOrEqual($after + 60, $token->getExpiresAt());
    }

    #[Test]
    public function it_falls_back_to_a_default_lifetime_when_expires_in_is_missing(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, $this->okBody('tok', null));
        $auth = new AuthenticationService($this->store(), $transport->http);

        $before = time();
        $token = $auth->getToken();
        $after = time();

        self::assertGreaterThanOrEqual($before + 1380, $token->getExpiresAt());
        self::assertLessThanOrEqual($after + 1380, $token->getExpiresAt());
    }

    #[Test]
    public function it_defaults_the_token_type_to_bearer_when_missing(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, $this->okBody('tok', 1440, null));

        $token = (new AuthenticationService($this->store(), $transport->http))->getToken();

        self::assertSame('Bearer', $token->getTokenType());
        self::assertSame('Bearer tok', $token->getAuthorizationHeader());
    }

    #[Test]
    public function it_throws_on_an_http_error_status(): void
    {
        $transport = new MockTransport();
        $transport->queue(401, '{"error":"invalid_client"}');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('HTTP 401');

        (new AuthenticationService($this->store(), $transport->http))->getToken();
    }

    #[Test]
    public function it_throws_when_the_response_has_no_access_token(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, '{"token_type":"Bearer"}');

        $this->expectException(RuntimeException::class);

        (new AuthenticationService($this->store(), $transport->http))->getToken();
    }

    #[Test]
    public function it_throws_on_a_transport_error(): void
    {
        $transport = new MockTransport();
        $transport->queueTransportError('Operation timed out');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Rede HTTP transport error');

        (new AuthenticationService($this->store(), $transport->http))->getToken();
    }
}
