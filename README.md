# eRede PHP SDK

A modern **PHP 8.1+** SDK for the [e.Rede](https://developer.userede.com.br/e-rede) payment API.

Version **4.0** is a ground-up modernization:

- 🔐 **OAuth 2.0** (`client_credentials`) — tokens fetched, cached and auto-refreshed for you.
- 🔌 **PSR-18 transport** — bring your own HTTP client (Guzzle, Symfony, …); zero hard curl dependency.
- 🧱 **Typed & enum-based API** — `declare(strict_types=1)`, typed properties, backed enums, `DateTimeImmutable`.
- 💳 Credit / debit authorization, capture, cancel/refund, query, **zero-dollar** validation.
- 🛡️ **3D Secure 2.0**, **antifraud**, **IATA/airline**, **sub-acquirer / marketplace**.
- ⚡ **Pix** (QR Code + webhooks) and **Tokenization** (card / brand token).

> **Upgrading from 3.x?** The API is intentionally breaking (enums, PSR-18, `DateTimeImmutable`).
> See the **[4.0 migration guide](docs/migration-4.0.md)**.

---

## Requirements

- PHP **>= 8.1** with `ext-json`
- A **PSR-18 HTTP client** and **PSR-17 factories** (the SDK ships none — you pick the implementation)

## Installation

```bash
composer require bizcommerce/erede-php

# plus any PSR-18 client (auto-discovered via php-http/discovery). For example:
composer require guzzlehttp/guzzle
# or:  composer require symfony/http-client nyholm/psr7
```

The SDK finds your installed client automatically. To inject one explicitly, see
[Configuration → HTTP client](docs/configuration.md#http-client).

---

## Quick start

```php
<?php

use Rede\Store;
use Rede\Transaction;
use Rede\eRede;

// 1. Credentials: PV (clientId) + Integration Key (clientSecret). Defaults to production.
$store = new Store('PV', 'INTEGRATION_KEY');

// 2. Build a transaction (amount in the major unit; 25.90 => 2590 cents).
$transaction = (new Transaction(25.90, 'order-' . time()))
    ->creditCard('5448280000000007', '123', '12', '2030', 'JOHN SNOW');

// 3. Authorize (captured automatically by default). OAuth is handled transparently.
$transaction = (new eRede($store))->create($transaction);

if ($transaction->getReturnCode() === '00') {
    printf("Authorized! tid=%s\n", $transaction->getTid());
}
```

Use the **sandbox** while integrating:

```php
use Rede\Environment;

$store = new Store('PV', 'INTEGRATION_KEY', Environment::sandbox());
```

---

## Core concepts

| Piece | What it is |
| --- | --- |
| `Rede\Store` | Your credentials + environment + (optional) token cache. |
| `Rede\Environment` | `Environment::production()` / `Environment::sandbox()`. |
| `Rede\eRede` | The facade you call for every operation. |
| `Rede\Transaction` | The request/response model (fluent builder + parsed result). |
| `Rede\Http\HttpClient` | PSR-18 wrapper; injected or auto-discovered. |

- **OAuth is automatic.** The SDK requests a `client_credentials` token, caches it (per
  credential+environment), reuses it until expiry, and transparently refreshes once on a `401`.
- **Bring your own cache.** Pass a `Rede\Auth\TokenStoreInterface` to share tokens across processes
  (Redis, etc.). See [Configuration](docs/configuration.md#token-cache).
- **Logging is optional** (PSR-3); card numbers, CVV and the `Authorization` header are redacted.

```php
$erede = new eRede($store, http: null, logger: $psrLogger); // http=null => auto-discovery
```

Full details: **[docs/configuration.md](docs/configuration.md)**.

---

## Card transactions

```php
$erede = new eRede($store);

// Authorize only (capture later):
$tx = (new Transaction(99.90, $ref))
    ->creditCard('5448280000000007', '123', '12', '2030', 'JOHN SNOW')
    ->capture(false)
    ->setInstallments(3)
    ->setSoftDescriptor('MY STORE');
$tx = $erede->create($tx);

// Capture a previously-authorized transaction (full or partial amount):
$erede->capture((new Transaction(99.90))->setTid($tx->getTid()));

// Cancel / refund:
$erede->cancel((new Transaction(99.90))->setTid($tx->getTid()));

// Query:
$erede->get($tid);                 // by transaction id
$erede->getByReference($ref);      // by your order reference
$erede->getRefunds($tid);          // refund history

// Zero-dollar (card validation / tokenization without charging):
$erede->zero((new Transaction(0, $ref))->creditCard(/* ... */));
```

More: **[docs/transactions.md](docs/transactions.md)**.

---

## 3D Secure 2.0

```php
use Rede\Device;
use Rede\Enum\Mpi;
use Rede\Enum\OnFailure;
use Rede\Enum\UrlKind;

$device = (new Device())
    ->setColorDepth('24')->setScreenHeight(900)->setScreenWidth(1440);

$tx = (new Transaction(120.00, $ref))
    ->creditCard('5448280000000007', '123', '12', '2030', 'JOHN SNOW')
    ->threeDSecure($device, OnFailure::Decline, Mpi::Rede)
    ->addUrl('https://shop.test/3ds/success', UrlKind::ThreeDSecureSuccess)
    ->addUrl('https://shop.test/3ds/failure', UrlKind::ThreeDSecureFailure);

$tx = $erede->create($tx);

if ($tx->getReturnCode() === '220') { // challenge required
    header('Location: ' . $tx->getThreeDSecure()->getUrl());
}
```

More: **[docs/3ds.md](docs/3ds.md)**.

---

## Antifraud

```php
use Rede\Enum\AddressTarget;
use Rede\Enum\Gender;
use Rede\Enum\ItemType;
use Rede\Item;

$environment = Environment::production()->setIp('203.0.113.7')->setSessionId('store-session-id');
$store = new Store('PV', 'INTEGRATION_KEY', $environment);

$tx = (new Transaction(200.00, $ref))->creditCard(/* ... */);

$cart = $tx->antifraud($environment);
$cart->consumer('Jane Roe', 'jane@example.com', '11111111111')
    ->setGender(Gender::Female)
    ->phone('11', '999998888')
    ->addDocument('RG', '111111111');
$cart->address(AddressTarget::Both)
    ->setAddress('Av. Paulista')->setNumber('1000')
    ->setCity('São Paulo')->setState('SP')->setZipCode('01310-100');
$cart->addItem((new Item('sku-1', 1, ItemType::Physical))->setAmount(20000)->setDescription('TV'));

$tx = (new eRede($store))->create($tx);
$tx->getAntifraud(); // score, riskLevel, recommendation
```

More: **[docs/antifraud.md](docs/antifraud.md)**.

---

## Pix

```php
use Rede\NotificationUrl;
use Rede\PixNotification;

// 1. Request a QR Code (kind=pix). Expiration is YYYY-MM-DDThh:mm:ss, max 15 days ahead.
$tx = (new Transaction(39.00, 'pix-' . time()))->pix('2025-12-31T23:59:59');
$tx = $erede->create($tx);

$qr = $tx->getQrCodeResponse();
$qr->getQrCodeImage();  // base64 PNG
$qr->getQrCodeData();   // EMV copy-and-paste string
$qr->getStatus();       // Pending | Approved | Canceled

// 2. Register the status webhook (once per CNPJ):
$erede->notificationUrl(
    (new NotificationUrl('https://shop.test/webhooks/pix'))->withAuthorization('bearer', 'BEARER xxx')
);

// 3. Parse an inbound webhook:
$event = PixNotification::fromJson($rawRequestBody);
if ($event->isPayment()) { /* query by $event->qrCode / TID for details */ }
```

More: **[docs/pix.md](docs/pix.md)**.

---

## Tokenization

```php
use Rede\CardTokenization;
use Rede\TokenManagement;

// 1. Tokenize a card:
$result = $erede->tokenizeCard(
    new CardTokenization('buyer@example.com', '5448280000000007', '12', '2030')
);
$tokenizationId = $result->getTokenizationId();

// 2. Query / manage:
$erede->queryToken($tokenizationId);
$erede->manageToken($tokenizationId, new TokenManagement(
    TokenManagement::STATUS_SUSPEND,
    TokenManagement::REASON_FRAUD_SUSPICION
));

// 3. Pay with a token (routed to the v2 endpoint automatically):
$tx = (new Transaction(49.90, $ref))->cardToken($tokenizationId);
$tx = $erede->create($tx);
```

More: **[docs/tokenization.md](docs/tokenization.md)**.

---

## Enums

Constants are now backed enums under `Rede\Enum\`:

| Enum | Cases |
| --- | --- |
| `TransactionKind` | `Credit`, `Debit`, `Pix` |
| `TransactionOrigin` | `Erede` (1), `VisaCheckout` (4), `Masterpass` (6) |
| `ItemType` | `Physical` (1) … `Airline` (4) |
| `PhoneType` | `Cellphone` (1) … `Other` (4) |
| `Gender` | `Male` (`M`), `Female` (`F`) |
| `UrlKind` | `Callback`, `ThreeDSecureSuccess`, `ThreeDSecureFailure` |
| `Mpi` | `Rede`, `ThirdParty` |
| `OnFailure` | `Continue`, `Decline` |
| `ResidenceType` | `Apartment` (1) … `Other` (4) |
| `AddressTarget` | `Billing`, `Shipping`, `Both` |

Full table + values: **[docs/enums.md](docs/enums.md)**.

---

## Upgrading from 3.x → 4.0

The headline breaking changes:

1. **Class constants → enums** (`Transaction::CREDIT` → `TransactionKind::Credit`, etc.).
2. **PSR-18** — install an HTTP client; constructors accept an optional `Rede\Http\HttpClient`.
3. **Dates** — getters like `getDateTime()` return `DateTimeImmutable`.
4. **`Cart::address()`** takes a `AddressTarget` enum.

Step-by-step: **[docs/migration-4.0.md](docs/migration-4.0.md)**.

---

## Testing

The suite uses PHPUnit 11 over a PSR-18 mock client (no network):

```bash
composer install
vendor/bin/phpunit
```

## Documentation

- [Configuration](docs/configuration.md) · [Transactions](docs/transactions.md) ·
  [3D Secure 2.0](docs/3ds.md) · [Antifraud](docs/antifraud.md) ·
  [Pix](docs/pix.md) · [Tokenization](docs/tokenization.md) ·
  [Enums](docs/enums.md) · [4.0 migration](docs/migration-4.0.md)

## License

MIT.
