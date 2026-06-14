# Pix

[← Back to README](../README.md)

Pix reuses the transaction endpoint (`kind=pix`) plus a webhook for status updates. Pix is only
available for Itaú account holders.

> ⚠️ **Sandbox-confirm caveats.** A few details in Rede's manual are ambiguous and should be
> verified against the sandbox before going live:
> 1. The QR Code expiration JSON key (this SDK sends `dateTimeExpiration`; override
>    `Rede\QrCode::EXPIRATION_KEY` if Rede expects a different key).
> 2. The exact token-service host (inferred from the OAuth hosts).
> 3. Whether a Pix partial refund accepts the standard `{ "amount": ... }` body.

## 1. Request a QR Code

```php
use Rede\Transaction;
use Rede\eRede;

$erede = new eRede($store);

// Expiration: YYYY-MM-DDThh:mm:ss, at most 15 days ahead.
$tx = (new Transaction(39.00, 'pix-' . time()))->pix('2025-12-31T23:59:59');
$tx = $erede->create($tx);

$qr = $tx->getQrCodeResponse();
$qr->getStatus();         // Pending | Approved | Canceled
$qr->getQrCodeImage();    // base64 PNG — render directly in an <img src="data:image/png;base64,...">
$qr->getQrCodeData();     // EMV "copy and paste" string
$qr->getTid();
$qr->getExpirationQrCode(); // ?DateTimeImmutable
```

## 2. Register the status webhook

Register once per CNPJ. In production the URL is registered via Rede support; the SDK posts the
sandbox registration request:

```php
use Rede\NotificationUrl;

$erede->notificationUrl(
    (new NotificationUrl('https://shop.test/webhooks/pix'))
        ->withAuthorization('bearer', 'BEARER your-token')   // optional; 'bearer' or 'basic'
);
// returns bool — true when Rede accepts the registration.
```

## 3. Receive notifications

Rede calls your URL on `PV.UPDATE_TRANSACTION_PIX` (paid) and `PV.REFUND_PIX` (refunded).

```php
use Rede\PixNotification;

$event = PixNotification::fromJson($rawRequestBody);

$event->isPayment();   // PV.UPDATE_TRANSACTION_PIX
$event->isRefund();    // PV.REFUND_PIX
$event->id;
$event->merchantId;
$event->qrCode;        // the qrcode/id referenced by the event
```

After a notification, query the transaction by TID for full details (wait ~10 minutes for
consistency, per Rede's guidance).

## Query & refund

Pix transactions are queried and refunded exactly like cards — see
[Transactions](transactions.md#query) and [cancel/refund](transactions.md#cancel--refund).
A paid Pix query returns `authorization` / `capture` / `refunds` blocks; a pending one returns
the `qrCodeResponse`.
