<?php

declare(strict_types=1);

namespace Rede\Auth;

/**
 * Persistence boundary for OAuth 2.0 access tokens.
 *
 * The SDK ships {@see InMemoryTokenStore} as a per-process default. Host
 * applications (e.g. a Magento module) may inject an implementation backed by a
 * shared cache so the ~24 minute token is reused across requests instead of
 * fetching a new one on every transaction.
 */
interface TokenStoreInterface
{
    /**
     * @param string $key Opaque cache key (see Store::getTokenCacheKey()).
     *
     * @return AccessToken|null The stored token, or null when missing or expired.
     */
    public function get(string $key): ?AccessToken;

    public function save(string $key, AccessToken $token): void;

    public function clear(string $key): void;
}
