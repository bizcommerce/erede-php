# Card transactions

[← Back to README](../README.md)

All operations go through the `Rede\eRede` facade and return a hydrated `Rede\Transaction`.

```php
use Rede\Store;
use Rede\Transaction;
use Rede\eRede;

$erede = new eRede(new Store('PV', 'INTEGRATION_KEY'));
```

> **Amounts** are given in the major unit and stored as cents: `new Transaction(25.90)` →
> `getAmount() === 2590`. Rounding is applied so `25.01` → `2501` (no float truncation).

## Authorize

By default a transaction is **captured automatically**. Call `capture(false)` to authorize only.

```php
use Rede\Enum\TransactionKind;

$tx = (new Transaction(99.90, 'order-123'))
    ->creditCard('5448280000000007', '123', '12', '2030', 'JOHN SNOW')
    ->capture(false)               // authorize only
    ->setInstallments(3)
    ->setSoftDescriptor('MY STORE');

$tx = $erede->create($tx);         // alias: $erede->authorize($tx)

$tx->getReturnCode();              // '00' on success
$tx->getTid();
$tx->getAuthorization()->getStatus();
$tx->getKind();                    // TransactionKind::Credit
```

Debit cards are always captured and require 3DS — see [3D Secure](3ds.md):

```php
$tx = (new Transaction(50.00, $ref))->debitCard('5277696455399733', '123', '01', '2030', 'JOHN SNOW');
```

## Capture

```php
// Full capture of a prior authorization:
$erede->capture((new Transaction(99.90))->setTid($tid));

// Partial capture: pass the lower amount.
$erede->capture((new Transaction(40.00))->setTid($tid));
```

## Cancel / refund

```php
$cancelled = $erede->cancel((new Transaction(99.90))->setTid($tid));
// Success return code for a cancellation is '359'.
```

## Query

```php
$erede->get($tid);              // by transaction id
$erede->getById($tid);         // alias
$erede->getByReference($ref);  // by your order reference
$erede->getRefunds($tid);      // refund/cancellation history -> getRefunds(): Refund[]
```

Response dates are `DateTimeImmutable`:

```php
$tx = $erede->get($tid);
$tx->getDateTime();                       // ?DateTimeImmutable
$tx->getAuthorization()?->getDateTime();  // ?DateTimeImmutable
```

## Zero-dollar (card validation)

Validates a card without charging (recommended before storing it). The original amount and
capture flag are restored on the returned transaction.

```php
$tx = (new Transaction(0, $ref))->creditCard('5448280000000007', '123', '12', '2030', 'JOHN SNOW');
$tx = $erede->zero($tx);
```

## IATA / airline

```php
$tx = (new Transaction(800.00, $ref))
    ->creditCard(/* ... */)
    ->iata('AIRLINE-CODE', '50');   // code, departure tax
```

## Sub-acquirers, marketplaces & dynamic MCC

```php
use Rede\SubMerchant;

$tx->mcc('5912', 'Sao Paulo', 'BR');                 // shorthand for setSubMerchant(new SubMerchant(...))
$tx->setSubMerchant(new SubMerchant('5912', 'Sao Paulo', 'BR'));
$tx->setPaymentFacilitatorID('FACILITATOR-ID');
$tx->additional()->setGateway(10)->setModule(20);    // additional routing data
```

## Brand return data

When present, the card-brand block is parsed into `Rede\Brand` (the SDK always asks Rede to
return it):

```php
$tx->getBrand()?->getName();         // e.g. "Visa"
$tx->getBrand()?->getReturnCode();
$tx->getBrandTid();
```
