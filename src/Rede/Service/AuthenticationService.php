<?php

declare(strict_types=1);

namespace Rede\Service;

use Psr\Log\LoggerInterface;
use Rede\Auth\AccessToken;
use Rede\Http\HttpClient;
use Rede\Store;
use RuntimeException;

/**
 * Obtains OAuth 2.0 access tokens from Rede using the client_credentials grant.
 *
 * This service is intentionally NOT an {@see AbstractService}: the token
 * endpoint uses a different base URL, an application/x-www-form-urlencoded body
 * and HTTP Basic authentication (the only place Basic auth survives after the
 * OAuth migration). Transaction services send the resulting token as a Bearer
 * header instead.
 *
 * Credential mapping (per Rede's official OAuth 2.0 tutorial):
 *   PV                   -> clientId      (Store::getClientId())
 *   Chave de Integração  -> clientSecret  (Store::getClientSecret())
 */
class AuthenticationService
{
    /**
     * Seconds shaved off the reported lifetime so a token is never used in the
     * window where it might already have expired on Rede's side.
     */
    private const SAFETY_MARGIN = 60;

    /**
     * Fallback lifetime (~24 minutes) used only when Rede omits expires_in.
     */
    private const DEFAULT_EXPIRES_IN = 1440;

    private HttpClient $http;

    public function __construct(
        private readonly Store $store,
        ?HttpClient $http = null,
        private readonly ?LoggerInterface $logger = null,
    ) {
        $this->http = $http ?? new HttpClient();
    }

    /**
     * Returns a valid access token, fetching a new one only when the store has
     * no cached (and unexpired) token.
     */
    public function getToken(): AccessToken
    {
        $key = $this->store->getTokenCacheKey();
        $store = $this->store->getTokenStore();

        $cached = $store->get($key);

        if ($cached !== null && !$cached->isExpired()) {
            return $cached;
        }

        $token = $this->requestToken();
        $store->save($key, $token);

        return $token;
    }

    /**
     * Forces a fresh token request, bypassing and refreshing the cache. Used to
     * recover from a 401 caused by a token that expired server-side.
     */
    public function refreshToken(): AccessToken
    {
        $this->store->getTokenStore()->clear($this->store->getTokenCacheKey());

        return $this->getToken();
    }

    private function requestToken(): AccessToken
    {
        $endpoint = $this->store->getEnvironment()->getTokenEndpoint();
        $credentials = base64_encode(
            sprintf('%s:%s', $this->store->getClientId(), $this->store->getClientSecret())
        );

        $headers = [
            'Authorization' => 'Basic ' . $credentials,
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
        ];

        if ($this->logger !== null) {
            // Never log the Basic credentials.
            $this->logger->debug(sprintf("Request Rede OAuth\nPOST %s\ngrant_type=client_credentials", $endpoint));
        }

        $response = $this->http->send('POST', $endpoint, $headers, 'grant_type=client_credentials');
        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();

        if ($this->logger !== null) {
            // Log only the status; the body carries the secret access_token.
            $this->logger->debug(sprintf("Response Rede OAuth\nStatus Code: %s", $statusCode));
        }

        $data = json_decode($body);

        if ($statusCode >= 400 || !is_object($data) || !isset($data->access_token)) {
            throw new RuntimeException(
                sprintf('Falha ao autenticar na Rede (OAuth 2.0). HTTP %d.', $statusCode)
            );
        }

        $expiresIn = isset($data->expires_in) ? (int) $data->expires_in : self::DEFAULT_EXPIRES_IN;
        $tokenType = isset($data->token_type) ? (string) $data->token_type : 'Bearer';

        return new AccessToken(
            (string) $data->access_token,
            $tokenType,
            time() + max(0, $expiresIn - self::SAFETY_MARGIN)
        );
    }
}
