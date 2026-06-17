<?php

declare(strict_types=1);

namespace Rede;

use stdClass;

class Environment implements RedeSerializable
{
    public const PRODUCTION = 'https://api.userede.com.br/erede';
    public const SANDBOX = 'https://sandbox-erede.useredecloud.com.br';

    /**
     * e.Rede transaction API version. After the mandatory OAuth 2.0 migration the
     * legacy v1 (HTTP Basic) path is decommissioned: it rejects Bearer tokens with
     * returnCode 25 "Affiliation: Invalid parameter format." All transaction calls
     * (credit, debit, Pix, capture, refund, query, notification-URL) use v2.
     */
    public const VERSION = 'v2';

    /**
     * OAuth 2.0 token endpoints (client_credentials grant).
     */
    public const TOKEN_ENDPOINT_PRODUCTION = 'https://api.userede.com.br/redelabs/oauth2/token';
    public const TOKEN_ENDPOINT_SANDBOX = 'https://rl7-sandbox-api.useredecloud.com.br/oauth2/token';

    /**
     * Hosts that serve the token-service (card / brand tokenization) API.
     */
    public const TOKENIZATION_HOST_PRODUCTION = 'https://api.userede.com.br';
    public const TOKENIZATION_HOST_SANDBOX = 'https://rl7-sandbox-api.useredecloud.com.br';

    private ?string $ip = null;

    private ?string $sessionId = null;

    private function __construct(
        private string $baseUrl,
        private string $tokenEndpoint,
        private string $tokenizationHost,
    ) {
    }

    /**
     * Builds a transaction endpoint URL. Defaults to the current API version
     * ({@see self::VERSION}); pass an explicit version only for legacy needs.
     */
    public function getEndpoint(string $service, string $version = self::VERSION): string
    {
        return sprintf('%s/%s/%s', $this->baseUrl, $version, $service);
    }

    /**
     * @return string The OAuth 2.0 token endpoint for this environment.
     */
    public function getTokenEndpoint(): string
    {
        return $this->tokenEndpoint;
    }

    /**
     * Builds a token-service (tokenization) endpoint URL.
     */
    public function getTokenizationEndpoint(string $path = ''): string
    {
        $base = $this->tokenizationHost . '/token-service/oauth/v2/tokenization';

        return $path === '' ? $base : sprintf('%s/%s', $base, $path);
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function jsonSerialize(): mixed
    {
        $consumer = new stdClass();
        $consumer->ip = $this->ip;
        $consumer->sessionId = $this->sessionId;

        return ['consumer' => $consumer];
    }

    public static function production(): self
    {
        return new self(self::PRODUCTION, self::TOKEN_ENDPOINT_PRODUCTION, self::TOKENIZATION_HOST_PRODUCTION);
    }

    public static function sandbox(): self
    {
        return new self(self::SANDBOX, self::TOKEN_ENDPOINT_SANDBOX, self::TOKENIZATION_HOST_SANDBOX);
    }

    public function setIp(string $ip): static
    {
        $this->ip = $ip;

        return $this;
    }

    public function setSessionId(string $sessionId): static
    {
        $this->sessionId = $sessionId;

        return $this;
    }
}
