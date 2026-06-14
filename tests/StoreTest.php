<?php

declare(strict_types=1);

namespace Rede\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rede\Auth\InMemoryTokenStore;
use Rede\Auth\TokenStoreInterface;
use Rede\Environment;
use Rede\Store;

#[CoversClass(Store::class)]
final class StoreTest extends TestCase
{
    #[Test]
    public function client_credentials_alias_the_filiation_and_token(): void
    {
        $store = new Store('PV-123', 'secret-key', Environment::sandbox());

        self::assertSame('PV-123', $store->getClientId());
        self::assertSame($store->getFiliation(), $store->getClientId());
        self::assertSame('secret-key', $store->getClientSecret());
        self::assertSame($store->getToken(), $store->getClientSecret());
    }

    #[Test]
    public function the_token_cache_key_is_derived_from_the_filiation_and_token_endpoint(): void
    {
        $env = Environment::sandbox();
        $store = new Store('PV-123', 'secret', $env);

        $expected = 'rede_oauth_' . sha1('PV-123' . '|' . $env->getTokenEndpoint());

        self::assertSame($expected, $store->getTokenCacheKey());
    }

    #[Test]
    public function the_token_cache_key_is_stable_for_the_same_credentials(): void
    {
        $a = new Store('PV-123', 'secret', Environment::sandbox());
        $b = new Store('PV-123', 'whatever', Environment::sandbox());

        // The secret is not part of the key; the filiation + endpoint are.
        self::assertSame($a->getTokenCacheKey(), $b->getTokenCacheKey());
    }

    #[Test]
    public function the_token_cache_key_differs_per_filiation(): void
    {
        $a = new Store('PV-AAA', 'secret', Environment::sandbox());
        $b = new Store('PV-BBB', 'secret', Environment::sandbox());

        self::assertNotSame($a->getTokenCacheKey(), $b->getTokenCacheKey());
    }

    #[Test]
    public function it_defaults_to_an_in_memory_token_store(): void
    {
        $store = new Store('PV-123', 'secret', Environment::sandbox());

        self::assertInstanceOf(InMemoryTokenStore::class, $store->getTokenStore());
    }

    #[Test]
    public function it_accepts_an_injected_token_store(): void
    {
        $injected = new InMemoryTokenStore();
        $store = new Store('PV-123', 'secret', Environment::sandbox(), $injected);

        self::assertSame($injected, $store->getTokenStore());
    }

    #[Test]
    public function set_token_store_replaces_the_store_fluently(): void
    {
        $store = new Store('PV-123', 'secret', Environment::sandbox());
        $replacement = $this->createMock(TokenStoreInterface::class);

        $returned = $store->setTokenStore($replacement);

        self::assertSame($store, $returned);
        self::assertSame($replacement, $store->getTokenStore());
    }

    #[Test]
    public function it_defaults_to_the_production_environment(): void
    {
        $store = new Store('PV-123', 'secret');

        self::assertStringStartsWith(
            Environment::PRODUCTION,
            $store->getEnvironment()->getEndpoint('transactions')
        );
    }
}
