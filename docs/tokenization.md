# Tokenization

[← Back to README](../README.md)

Tokenization replaces card data with a token you can reuse for future purchases (Card on File).
Card and Brand tokenization share one API on the **token-service** host; token-based transactions
hit the **v2** transaction endpoint.

## 1. Request a token

```php
use Rede\CardTokenization;
use Rede\eRede;

$erede = new eRede($store);

$card = new CardTokenization(
    email: 'buyer@example.com',
    cardNumber: '5448280000000007',
    expirationMonth: '12',
    expirationYear: '2030',
    storageCard: CardTokenization::COF_NOT_STORED,   // 0 = not stored, 2 = already stored
);
$card->setSecurityCode('123')
     ->setCardholderName('JANE ROE')
     ->setEmbeddedZeroDollar(true);   // perform a zero-dollar validation (recommended)

$result = $erede->tokenizeCard($card);

$result->isSuccessful();           // returnCode === '00'
$tokenizationId = $result->getTokenizationId();
```

> The brand token is generated asynchronously. Query the tokenization (or wait for the webhook)
> before relying on the brand token.

## 2. Query a token

```php
$token = $erede->queryToken($tokenizationId);

$token->getTokenizationStatus();   // Pending | Active | Inactive | Suspended | Failed | Deleted
$token->getBin();
$token->getLast4();
$token->getBrandName();            // e.g. "Visa"
$token->getBrandTokenStatus();
$token->getBrandTid();
$token->getTokenCode();
$token->getTokenExpirationDate();  // MM/YYYY
$token->getLastModifiedDate();     // ?DateTimeImmutable
```

## 3. Manage a token

```php
use Rede\TokenManagement;

$erede->manageToken($tokenizationId, new TokenManagement(
    TokenManagement::STATUS_SUSPEND,            // STATUS_DELETE | STATUS_SUSPEND | STATUS_REACTIVATE
    TokenManagement::REASON_FRAUD_SUSPICION,    // REASON_CUSTOMER_REQUEST (1) | REASON_FRAUD_SUSPICION (2)
));
```

## 4. Pay with a token

Set the token on a transaction; the SDK routes it to the **v2** endpoint and marks the card as
stored (`storageCard = 2`) automatically.

```php
use Rede\Transaction;

$tx = (new Transaction(49.90, $ref))->cardToken($tokenizationId);
$tx = $erede->create($tx);
```

## 5. Token update webhook

Rede notifies `PV.TOKENIZACAO-BANDEIRA` whenever a token is created or changes:

```php
use Rede\TokenNotification;

$event = TokenNotification::fromJson($rawRequestBody);

$event->hasEvent(TokenNotification::EVENT);
$event->tokenizationId;     // query this id to see what changed
$event->merchantId;
```
