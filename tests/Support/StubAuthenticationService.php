<?php

declare(strict_types=1);

namespace Rede\Tests\Support;

use Rede\Auth\AccessToken;
use Rede\Service\AuthenticationService;

/**
 * Drop-in AuthenticationService for AbstractService tests: returns a fixed
 * token and counts refreshes, so the token round-trip is irrelevant and the
 * 401-retry path can be asserted directly. Deliberately bypasses the parent
 * constructor (no Store needed) by overriding both public entry points.
 */
final class StubAuthenticationService extends AuthenticationService
{
    public int $refreshCount = 0;

    private AccessToken $token;

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct(?AccessToken $token = null)
    {
        $this->token = $token ?? new AccessToken('stub-token', 'Bearer', PHP_INT_MAX);
    }

    public function getToken(): AccessToken
    {
        return $this->token;
    }

    public function refreshToken(): AccessToken
    {
        $this->refreshCount++;

        return $this->token;
    }
}
