<?php

declare(strict_types=1);

namespace Rede\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rede\Environment;
use ReflectionClass;

#[CoversClass(Environment::class)]
final class EnvironmentTest extends TestCase
{
    #[Test]
    public function production_points_at_the_production_hosts(): void
    {
        $env = Environment::production();

        self::assertSame('https://api.userede.com.br/erede/v2/transactions', $env->getEndpoint('transactions'));
        self::assertSame('https://api.userede.com.br/erede/v2/transactions', $env->getEndpoint('transactions', 'v2'));
        self::assertSame(Environment::TOKEN_ENDPOINT_PRODUCTION, $env->getTokenEndpoint());
    }

    #[Test]
    public function sandbox_points_at_the_sandbox_hosts(): void
    {
        $env = Environment::sandbox();

        self::assertSame('https://sandbox-erede.useredecloud.com.br/v2/transactions', $env->getEndpoint('transactions'));
        self::assertSame('https://rl7-sandbox-api.useredecloud.com.br/oauth2/token', $env->getTokenEndpoint());
    }

    #[Test]
    public function it_builds_tokenization_endpoints_on_the_token_service_host(): void
    {
        self::assertSame(
            'https://rl7-sandbox-api.useredecloud.com.br/token-service/oauth/v2/tokenization',
            Environment::sandbox()->getTokenizationEndpoint()
        );
        self::assertSame(
            'https://api.userede.com.br/token-service/oauth/v2/tokenization/tok-123',
            Environment::production()->getTokenizationEndpoint('tok-123')
        );
    }

    #[Test]
    public function it_serializes_the_antifraud_consumer_block(): void
    {
        $env = Environment::sandbox();
        $returned = $env->setIp('203.0.113.7')->setSessionId('sess-abc');

        self::assertSame($env, $returned);

        $serialized = $env->jsonSerialize();
        self::assertArrayHasKey('consumer', $serialized);
        self::assertSame('203.0.113.7', $serialized['consumer']->ip);
        self::assertSame('sess-abc', $serialized['consumer']->sessionId);
    }

    #[Test]
    public function the_constructor_is_private_so_only_the_factories_build_environments(): void
    {
        self::assertTrue((new ReflectionClass(Environment::class))->getConstructor()->isPrivate());
    }
}
