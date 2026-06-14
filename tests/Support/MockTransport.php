<?php

declare(strict_types=1);

namespace Rede\Tests\Support;

use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Rede\Auth\AccessToken;
use Rede\Http\HttpClient;
use Rede\Store;
use RuntimeException;

/**
 * Wraps a php-http mock client behind the SDK's {@see HttpClient}, so tests can
 * queue canned PSR-7 responses and inspect the outgoing PSR-7 requests — the
 * PSR-18 replacement for the old curl seams.
 */
final class MockTransport
{
    public MockClient $client;

    public HttpClient $http;

    private Psr17Factory $factory;

    public function __construct()
    {
        $this->client = new MockClient();
        $this->factory = new Psr17Factory();
        $this->http = new HttpClient($this->client, $this->factory, $this->factory);
    }

    public function queue(int $status, string $body = ''): self
    {
        $this->client->addResponse(
            $this->factory->createResponse($status)->withBody($this->factory->createStream($body))
        );

        return $this;
    }

    /**
     * Makes the next request raise a PSR-18 transport error.
     */
    public function queueTransportError(string $message = 'Connection failed'): self
    {
        $this->client->addException(
            new class ($message) extends RuntimeException implements ClientExceptionInterface {
            }
        );

        return $this;
    }

    /**
     * @return RequestInterface[]
     */
    public function requests(): array
    {
        return $this->client->getRequests();
    }

    public function lastRequest(): RequestInterface
    {
        $requests = $this->requests();

        return $requests[count($requests) - 1];
    }

    /**
     * Pre-seeds a valid token so service tests skip the OAuth round-trip.
     */
    public static function seedToken(Store $store, string $token = 'stub-token'): void
    {
        $store->getTokenStore()->save(
            $store->getTokenCacheKey(),
            new AccessToken($token, 'Bearer', PHP_INT_MAX)
        );
    }
}
