# 3D Secure 2.0

[← Back to README](../README.md)

3DS 2.0 authenticates the cardholder before authorization. Configure it with
`Transaction::threeDSecure()`, provide the redirect URLs, then handle the challenge.

```php
use Rede\Device;
use Rede\Enum\Mpi;
use Rede\Enum\OnFailure;
use Rede\Enum\UrlKind;
use Rede\Transaction;

$device = (new Device())
    ->setColorDepth('24')
    ->setScreenHeight(900)
    ->setScreenWidth(1440)
    ->setJavaEnabled(false)
    ->setLanguage('BR')
    ->setTimeZoneOffset(180);

$tx = (new Transaction(120.00, $ref))
    ->creditCard('5448280000000007', '123', '12', '2030', 'JOHN SNOW')
    ->threeDSecure($device, OnFailure::Decline, Mpi::Rede)
    ->addUrl('https://shop.test/3ds/success', UrlKind::ThreeDSecureSuccess)
    ->addUrl('https://shop.test/3ds/failure', UrlKind::ThreeDSecureFailure);

$tx = (new \Rede\eRede($store))->create($tx);
```

## Handling the challenge

A return code of **`220`** means the customer must be redirected to complete authentication:

```php
if ($tx->getReturnCode() === '220') {
    header('Location: ' . $tx->getThreeDSecure()->getUrl());
    exit;
}
```

After the customer returns via your success/failure URL, query the transaction to read the
final result.

## `threeDSecure()` signature

```php
public function threeDSecure(
    ?Device $device = null,
    OnFailure $onFailure = OnFailure::Decline,
    Mpi $mpi = Mpi::Rede,
): static
```

- **`Mpi::Rede`** — embedded flow run by Rede's MPI (`isEmbedded()` is then `true`).
- **`Mpi::ThirdParty`** — you supply authentication data (`cavv`, `eci`, `xid`,
  `directoryServerTransactionId`) from your own MPI:

```php
$tx->getThreeDSecure()
    ->setCavv('...')->setEci('05')->setXid('...')
    ->setDirectoryServerTransactionId('...');
```

- **`OnFailure::Decline`** (default) declines the transaction if authentication fails;
  `OnFailure::Continue` proceeds unauthenticated.

## Device fields

`Rede\Device` carries the browser fingerprint required by 3DS 2.0: `ColorDepth`, `ScreenHeight`,
`ScreenWidth`, `TimeZoneOffset`, `JavaEnabled`, `Language`, `DeviceType3ds`. `Language` defaults
to `BR` and `TimeZoneOffset` to `3`.

## Result fields

```php
$threeDS = $tx->getThreeDSecure();
$threeDS->getReturnCode();
$threeDS->getReturnMessage();
$threeDS->getUrl();                        // challenge URL (return code 220)
$threeDS->getDirectoryServerTransactionId();
```

## Data Only

For the Data Only flow use the `ThreeDSecure::DATA_ONLY` constant where the integration requires
it. The `threeDIndicator` defaults to `2` (3DS 2); values below 2 are deprecated.
