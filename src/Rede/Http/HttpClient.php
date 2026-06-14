<?php

declare(strict_types=1);

namespace Rede\Http;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use RuntimeException;

/**
 * Thin transport over any PSR-18 client + PSR-17 factories. When no
 * implementation is injected it is auto-discovered (php-http/discovery), so
 * callers can keep using {@see \Rede\eRede} without wiring HTTP plumbing while
 * still being free to inject Guzzle, Symfony HttpClient, a mock, etc.
 */
final class HttpClient
{
    private ClientInterface $client;

    private RequestFactoryInterface $requestFactory;

    private StreamFactoryInterface $streamFactory;

    public function __construct(
        ?ClientInterface $client = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
    ) {
        $this->client = $client ?? Psr18ClientDiscovery::find();
        $this->requestFactory = $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
    }

    /**
     * Sends a request and returns the PSR-7 response.
     *
     * @param array<string, string> $headers
     *
     * @throws RuntimeException on a transport-level failure.
     */
    public function send(string $method, string $url, array $headers = [], ?string $body = null): ResponseInterface
    {
        $request = $this->requestFactory->createRequest($method, $url);

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if ($body !== null) {
            $request = $request->withBody($this->streamFactory->createStream($body));
        }

        try {
            return $this->client->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new RuntimeException('Rede HTTP transport error: ' . $e->getMessage(), 0, $e);
        }
    }
}
