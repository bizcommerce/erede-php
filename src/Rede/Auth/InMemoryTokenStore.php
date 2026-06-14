<?php

declare(strict_types=1);

namespace Rede\Auth;

/**
 * Default {@see TokenStoreInterface} that keeps tokens for the lifetime of the
 * current PHP process only. This preserves the historical behaviour of fetching
 * a single token per process when no shared cache is injected.
 */
final class InMemoryTokenStore implements TokenStoreInterface
{
    /**
     * @var array<string, AccessToken>
     */
    private array $tokens = [];

    public function get(string $key): ?AccessToken
    {
        $token = $this->tokens[$key] ?? null;

        if ($token !== null && $token->isExpired()) {
            unset($this->tokens[$key]);

            return null;
        }

        return $token;
    }

    public function save(string $key, AccessToken $token): void
    {
        $this->tokens[$key] = $token;
    }

    public function clear(string $key): void
    {
        unset($this->tokens[$key]);
    }
}
