# Configuration

[← Back to README](../README.md)

## Store

`Rede\Store` carries your credentials and the environment.

```php
use Rede\Store;
use Rede\Environment;

$store = new Store(
    filiation: 'PV',                 // OAuth clientId
    token: 'INTEGRATION_KEY',        // OAuth clientSecret
    environment: Environment::production(), // optional; defaults to production
    tokenStore: null,                // optional; see "Token cache" below
);
```

`getClientId()` / `getClientSecret()` are aliases of `getFiliation()` / `getToken()`.

## Environment

```php
Environment::production();
Environment::sandbox();
```

| Endpoint | Production | Sandbox |
| --- | --- | --- |
| Transactions (v2, OAuth) | `https://api.userede.com.br/erede/v2/...` | `https://sandbox-erede.useredecloud.com.br/v2/...` |
| OAuth token | `https://api.userede.com.br/redelabs/oauth2/token` | `https://rl7-sandbox-api.useredecloud.com.br/oauth2/token` |
| Token-service | `https://api.userede.com.br/token-service/oauth/v2/tokenization` | `https://rl7-sandbox-api.useredecloud.com.br/token-service/oauth/v2/tokenization` |

For antifraud you may attach the buyer IP / session id, which are serialized into the request:

```php
$environment = Environment::production()
    ->setIp('203.0.113.7')
    ->setSessionId('store-session-id');
```

## Authentication (OAuth 2.0)

You never call the token endpoint yourself. On the first request the SDK performs a
`client_credentials` grant, caches the access token, reuses it until it is about to expire, and
— if Rede returns `401` — refreshes it once and retries the original request transparently.

## Token cache

By default tokens live in memory for the current process (`Rede\Auth\InMemoryTokenStore`).
To share a token across processes/servers, implement `Rede\Auth\TokenStoreInterface`:

```php
use Rede\Auth\TokenStoreInterface;
use Rede\Auth\AccessToken;

final class RedisTokenStore implements TokenStoreInterface
{
    public function __construct(private \Redis $redis) {}

    public function get(string $key): ?AccessToken { /* ... */ }
    public function save(string $key, AccessToken $token): void { /* ... */ }
    public function clear(string $key): void { /* ... */ }
}

$store = new Store('PV', 'INTEGRATION_KEY', Environment::production(), new RedisTokenStore($redis));
```

The cache key (`Store::getTokenCacheKey()`) is derived from the PV + token endpoint, so
different credentials/environments never share a token.

## HTTP client

The SDK depends only on the PSR-18 / PSR-17 **interfaces** and auto-discovers an installed
implementation. To inject one explicitly (custom timeouts, proxy, a specific library, or tests):

```php
use Rede\Http\HttpClient;
use Rede\eRede;

$httpClient = new HttpClient(
    client: $psr18Client,          // Psr\Http\Client\ClientInterface
    requestFactory: $psr17Factory, // Psr\Http\Message\RequestFactoryInterface
    streamFactory: $psr17Factory,  // Psr\Http\Message\StreamFactoryInterface
);

$erede = new eRede($store, $httpClient);
```

Any argument left `null` is auto-discovered. With no client installed at all, discovery throws —
install e.g. `guzzlehttp/guzzle` or `symfony/http-client`.

## Logging

Pass any PSR-3 logger as the third argument. Requests/responses are logged at `debug` level with
card number, CVV and the `Authorization` header redacted.

```php
$erede = new eRede($store, null, $psrLogger);
```
