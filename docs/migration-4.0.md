# Upgrading from 3.x to 4.0

[← Back to README](../README.md)

4.0 is a deliberate breaking release. The changes below are everything you need to migrate a
3.x integration.

## 1. Install a PSR-18 HTTP client

The SDK no longer bundles curl. Add a client (auto-discovered):

```bash
composer require guzzlehttp/guzzle
# or: composer require symfony/http-client nyholm/psr7
```

Without one, the first request throws a discovery error. To inject explicitly see
[Configuration → HTTP client](configuration.md#http-client).

## 2. Constructor signatures changed

`eRede` and the service classes now take an optional `Rede\Http\HttpClient` **before** the
logger:

```php
// 3.x
new eRede($store, $logger);

// 4.0
new eRede($store);                 // HTTP client auto-discovered
new eRede($store, $httpClient);    // inject a client
new eRede($store, null, $logger);  // keep auto-discovery, add a PSR-3 logger
```

## 3. Constants are now enums

Replace every class constant with the matching `Rede\Enum\` case
(full list in [enums.md](enums.md)):

| 3.x constant | 4.0 enum case |
| --- | --- |
| `Transaction::CREDIT` / `::DEBIT` | `TransactionKind::Credit` / `::Debit` |
| `Transaction::ORIGIN_EREDE` / `_VISA_CHECKOUT` / `_MASTERPASS` | `TransactionOrigin::Erede` / `VisaCheckout` / `Masterpass` |
| `Item::PHYSICAL` … `::AIRLINE` | `ItemType::Physical` … `Airline` |
| `Phone::CELLPHONE` … `::OTHER` | `PhoneType::Cellphone` … `Other` |
| `Consumer::MALE` / `::FEMALE` | `Gender::Male` / `Female` |
| `Url::CALLBACK` / `::THREE_D_SECURE_SUCCESS` / `_FAILURE` | `UrlKind::Callback` / `ThreeDSecureSuccess` / `ThreeDSecureFailure` |
| `Address::APARTMENT` … `::OTHER` | `ResidenceType::Apartment` … `Other` |
| `Address::BILLING` / `::SHIPPING` / `::BOTH` | `AddressTarget::Billing` / `Shipping` / `Both` |
| `ThreeDSecure::CONTINUE_ON_FAILURE` / `::DECLINE_ON_FAILURE` | `OnFailure::Continue` / `Decline` |

Typed setters follow suit, e.g. `setKind(TransactionKind $kind)`,
`setOrigin(TransactionOrigin $origin)`, `Address::setType(ResidenceType $type)`,
`Consumer::setGender(Gender $gender)`.

## 4. `Cart::address()` takes an enum

```php
// 3.x
$cart->address(Address::BOTH);

// 4.0
$cart->address(\Rede\Enum\AddressTarget::Both);
```

`getShippingAddress()` returns an **array** of `Address` (alias: `getShippingAddresses()`).

## 5. 3D Secure constructor / helper changed

```php
// 3.x
$tx->threeDSecure(ThreeDSecure::DECLINE_ON_FAILURE, true);

// 4.0
$tx->threeDSecure($device, OnFailure::Decline, Mpi::Rede); // device + enums; embedded derived from MPI
```

See [3D Secure 2.0](3ds.md). The new `Rede\Device` class is required for the browser fingerprint.

## 6. Dates are `DateTimeImmutable`

Getters that returned strings/`DateTime` now return `?DateTimeImmutable`
(`Transaction::getDateTime()`, `Authorization::getDateTime()`, `Refund::getRefundDateTime()`, …).

## 7. New return-data getters

`getBrand()` / `getBrandTid()` expose card-brand data (the SDK now sends
`Transaction-Response: brand-return-opened`). `getCapture()` may return a `Rede\Capture` object on
responses (or `bool` on requests).

## What did **not** change

- The facade method names (`create`, `authorize`, `capture`, `cancel`, `get`, `getById`,
  `getByReference`, `getRefunds`, `zero`) and their behavior.
- Amounts are still given in the major unit and stored as cents.
- OAuth is still automatic and cached.

## New in 4.0

[Pix](pix.md), [Tokenization](tokenization.md), full [3DS 2.0](3ds.md), and sub-acquirer /
marketplace / dynamic-MCC fields on [transactions](transactions.md).
