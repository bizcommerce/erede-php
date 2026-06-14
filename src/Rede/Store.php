<?php

declare(strict_types=1);

namespace Rede;

use Rede\Auth\InMemoryTokenStore;
use Rede\Auth\TokenStoreInterface;

class Store
{
    private Environment $environment;

    private TokenStoreInterface $tokenStore;

    /**
     * @param string $filiation PV / clientId
     * @param string $token     Chave de Integração / clientSecret
     */
    public function __construct(
        private string $filiation,
        private string $token,
        ?Environment $environment = null,
        ?TokenStoreInterface $tokenStore = null,
    ) {
        $this->environment = $environment ?? Environment::production();
        $this->tokenStore = $tokenStore ?? new InMemoryTokenStore();
    }

    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    public function getFiliation(): string
    {
        return $this->filiation;
    }

    /**
     * OAuth 2.0 clientId. Alias of the filiation (PV).
     */
    public function getClientId(): string
    {
        return $this->filiation;
    }

    /**
     * OAuth 2.0 clientSecret. Alias of the token (Chave de Integração).
     */
    public function getClientSecret(): string
    {
        return $this->token;
    }

    public function getTokenStore(): TokenStoreInterface
    {
        return $this->tokenStore;
    }

    public function setTokenStore(TokenStoreInterface $tokenStore): static
    {
        $this->tokenStore = $tokenStore;

        return $this;
    }

    /**
     * A stable key identifying the OAuth token for this credential + environment
     * pair, so different stores/environments never share a cached token.
     */
    public function getTokenCacheKey(): string
    {
        return 'rede_oauth_' . sha1($this->filiation . '|' . $this->environment->getTokenEndpoint());
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setEnvironment(Environment $environment): static
    {
        $this->environment = $environment;

        return $this;
    }

    public function setFiliation(string $filiation): static
    {
        $this->filiation = $filiation;

        return $this;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }
}
