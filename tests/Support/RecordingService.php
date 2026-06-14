<?php

declare(strict_types=1);

namespace Rede\Tests\Support;

use Rede\Service\AbstractService;
use Rede\Service\AuthenticationService;

/**
 * Concrete {@see AbstractService} for exercising sendRequest() directly. The
 * transport is a real (mock-backed) HttpClient; the auth service is stubbed so
 * the token round-trip never hits the wire and refreshes can be counted.
 */
final class RecordingService extends AbstractService
{
    public string $service = 'transactions';

    public ?AuthenticationService $auth = null;

    public function send(?string $body = null, string $method = self::GET, bool $isRetry = false): mixed
    {
        return $this->sendRequest($body, $method, $isRetry);
    }

    public function execute(): mixed
    {
        return $this->send();
    }

    protected function getService(): string
    {
        return $this->service;
    }

    protected function parseResponse(string $response, int $statusCode): mixed
    {
        return ['response' => $response, 'statusCode' => $statusCode];
    }

    protected function getAuthenticationService(): AuthenticationService
    {
        return $this->auth ??= new StubAuthenticationService();
    }
}
