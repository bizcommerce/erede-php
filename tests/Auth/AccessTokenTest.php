<?php

declare(strict_types=1);

namespace Rede\Tests\Auth;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rede\Auth\AccessToken;

#[CoversClass(AccessToken::class)]
final class AccessTokenTest extends TestCase
{
    #[Test]
    public function it_exposes_the_values_it_was_built_with(): void
    {
        $token = new AccessToken('abc123', 'Bearer', 2_000_000_000);

        self::assertSame('abc123', $token->getAccessToken());
        self::assertSame('Bearer', $token->getTokenType());
        self::assertSame(2_000_000_000, $token->getExpiresAt());
    }

    #[Test]
    public function it_is_not_expired_before_the_expiry_instant(): void
    {
        $token = new AccessToken('abc', 'Bearer', 1_000);

        self::assertFalse($token->isExpired(999));
    }

    #[Test]
    public function it_is_expired_exactly_at_the_expiry_instant(): void
    {
        // The boundary matters: a token must be considered dead the moment it
        // reaches expiresAt, never one second later.
        $token = new AccessToken('abc', 'Bearer', 1_000);

        self::assertTrue($token->isExpired(1_000));
        self::assertTrue($token->isExpired(1_001));
    }

    #[Test]
    public function it_falls_back_to_the_current_time_when_no_instant_is_given(): void
    {
        $past = new AccessToken('abc', 'Bearer', time() - 10);
        $future = new AccessToken('abc', 'Bearer', time() + 3_600);

        self::assertTrue($past->isExpired());
        self::assertFalse($future->isExpired());
    }

    #[Test]
    public function it_builds_a_bearer_authorization_header(): void
    {
        $token = new AccessToken('abc123', 'Bearer', 1_000);

        self::assertSame('Bearer abc123', $token->getAuthorizationHeader());
    }

    #[Test]
    public function it_defaults_the_authorization_scheme_to_bearer_when_type_is_empty(): void
    {
        // Rede occasionally omits token_type; the header must still be valid.
        $token = new AccessToken('abc123', '', 1_000);

        self::assertSame('Bearer abc123', $token->getAuthorizationHeader());
    }
}
