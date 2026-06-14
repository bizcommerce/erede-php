<?php

declare(strict_types=1);

namespace Rede\Auth;

/**
 * Immutable value object representing an OAuth 2.0 access token issued by Rede.
 *
 * The expiry is stored as an absolute unix timestamp already discounted by a
 * safety margin (see AuthenticationService), so {@see AccessToken::isExpired()}
 * can be answered without knowing how the token was obtained.
 */
final class AccessToken
{
    /**
     * @param string $accessToken The bearer token value.
     * @param string $tokenType   The token type returned by Rede (usually "Bearer").
     * @param int    $expiresAt   Absolute unix timestamp at which the token must be considered expired.
     */
    public function __construct(
        private string $accessToken,
        private string $tokenType,
        private int $expiresAt
    ) {
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getTokenType(): string
    {
        return $this->tokenType;
    }

    public function getExpiresAt(): int
    {
        return $this->expiresAt;
    }

    public function isExpired(?int $now = null): bool
    {
        return ($now ?? time()) >= $this->expiresAt;
    }

    /**
     * @return string The value to send in the "Authorization" header, e.g. "Bearer abc123".
     */
    public function getAuthorizationHeader(): string
    {
        return sprintf('%s %s', $this->tokenType !== '' ? $this->tokenType : 'Bearer', $this->accessToken);
    }
}
