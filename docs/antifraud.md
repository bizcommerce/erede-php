# Antifraud

[← Back to README](../README.md)

Antifraud (Cyber Source) enriches a transaction with cart, consumer and address data. Start from
`Transaction::antifraud()`, which returns a `Rede\Cart` and flags the transaction.

```php
use Rede\Store;
use Rede\Transaction;
use Rede\Environment;
use Rede\Enum\AddressTarget;
use Rede\Enum\Gender;
use Rede\Enum\ItemType;
use Rede\Enum\PhoneType;
use Rede\Item;
use Rede\eRede;

$environment = Environment::production()
    ->setIp('203.0.113.7')
    ->setSessionId('store-session-id');

$store = new Store('PV', 'INTEGRATION_KEY', $environment);

$tx = (new Transaction(200.00, $ref))
    ->creditCard('5448280000000007', '123', '12', '2030', 'JANE ROE');

$cart = $tx->antifraud($environment);

// Consumer
$cart->consumer('Jane Roe', 'jane@example.com', '11111111111')
    ->setGender(Gender::Female)
    ->phone('11', '999998888', PhoneType::Cellphone)
    ->addDocument('RG', '111111111');

// Address (Billing, Shipping or Both)
$cart->address(AddressTarget::Both)
    ->setAddresseeName('Jane Roe')
    ->setAddress('Av. Paulista')
    ->setNumber('1000')
    ->setNeighbourhood('Bela Vista')
    ->setCity('São Paulo')
    ->setState('SP')
    ->setZipCode('01310-100')
    ->setType(\Rede\Enum\ResidenceType::Commercial);

// Items
$cart->addItem(
    (new Item('sku-1', 1, ItemType::Physical))
        ->setAmount(20000)            // cents
        ->setDescription('Television')
        ->setFreight(199)
        ->setDiscount(0)
        ->setShippingType('Sedex')
);

$tx = (new eRede($store))->create($tx);

if ($tx->getReturnCode() === '00') {
    $antifraud = $tx->getAntifraud();
    $antifraud->isSuccess();
    $antifraud->getScore();
    $antifraud->getRiskLevel();
    $antifraud->getRecommendation();
}
```

## `Cart::address()` targeting

`AddressTarget` decides which slot the returned `Address` fills:

| Target | Billing | Shipping |
| --- | --- | --- |
| `AddressTarget::Billing` | ✅ | — |
| `AddressTarget::Shipping` | — | ✅ |
| `AddressTarget::Both` (default) | ✅ | ✅ |

Shipping supports multiple addresses, so `getShippingAddress()` (and its alias
`getShippingAddresses()`) returns an **array** of `Address`.

## Airline / IATA antifraud

For airlines, attach flight + passenger data to the cart:

```php
use Rede\Flight;
use Rede\Passenger;
use Rede\Phone;

$cart->setIata(
    (new Flight('FL123', 'GRU', 'JFK', '2025-02-15T10:54:45-03:00'))
        ->setPassenger(
            (new Passenger('Arya Stark', 'arya@example.com', 'TICKET-1'))
                ->setPhone(new Phone('11', '912341234'))
        )
);
```

See also the simpler [IATA on a transaction](transactions.md#iata--airline).
