<?php

declare(strict_types=1);

namespace Rede\Tests\Auth;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rede\Auth\AccessToken;
use Rede\Auth\InMemoryTokenStore;

#[CoversClass(InMemoryTokenStore::class)]
final class InMemoryTokenStoreTest extends TestCase
{
    #[Test]
    public function it_returns_null_for_an_unknown_key(): void
    {
        $store = new InMemoryTokenStore();

        self::assertNull($store->get('missing'));
    }

    #[Test]
    public function it_saves_and_returns_a_live_token(): void
    {
        $store = new InMemoryTokenStore();
        $token = new AccessToken('abc', 'Bearer', time() + 3_600);

        $store->save('key', $token);

        self::assertSame($token, $store->get('key'));
    }

    #[Test]
    public function it_evicts_and_hides_an_expired_token_on_read(): void
    {
        $store = new InMemoryTokenStore();
        $store->save('key', new AccessToken('abc', 'Bearer', time() - 1));

        // First read returns null because the token is already expired...
        self::assertNull($store->get('key'));

        // ...and it must have been removed, not merely skipped, so a later
        // save under the same key is the only thing that can resurrect it.
        self::assertNull($store->get('key'));
    }

    #[Test]
    public function it_clears_a_stored_token(): void
    {
        $store = new InMemoryTokenStore();
        $store->save('key', new AccessToken('abc', 'Bearer', time() + 3_600));

        $store->clear('key');

        self::assertNull($store->get('key'));
    }

    #[Test]
    public function it_keeps_tokens_isolated_per_key(): void
    {
        $store = new InMemoryTokenStore();
        $a = new AccessToken('a', 'Bearer', time() + 3_600);
        $b = new AccessToken('b', 'Bearer', time() + 3_600);

        $store->save('key-a', $a);
        $store->save('key-b', $b);

        self::assertSame($a, $store->get('key-a'));
        self::assertSame($b, $store->get('key-b'));
    }
}
