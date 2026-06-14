<?php

declare(strict_types=1);

namespace Rede\Tests\Pix;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rede\eRede;
use Rede\Enum\TransactionKind;
use Rede\Environment;
use Rede\NotificationUrl;
use Rede\PixNotification;
use Rede\QrCode;
use Rede\QrCodeResponse;
use Rede\Service\CreateTransactionService;
use Rede\Service\NotificationUrlService;
use Rede\Store;
use Rede\Tests\Support\Fixtures;
use Rede\Tests\Support\MockTransport;
use Rede\Transaction;

#[CoversClass(QrCode::class)]
#[CoversClass(QrCodeResponse::class)]
#[CoversClass(NotificationUrl::class)]
#[CoversClass(NotificationUrlService::class)]
#[CoversClass(PixNotification::class)]
final class PixTest extends TestCase
{
    use Fixtures;

    private function store(): Store
    {
        $store = new Store('PV-123', 'secret', Environment::sandbox());
        MockTransport::seedToken($store);

        return $store;
    }

    #[Test]
    public function it_requests_a_pix_qr_code_and_parses_the_qr_response(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, self::fixture('pix_qrcode_response.json'));

        $transaction = (new Transaction(39.0, 'pix310723140848'))->pix('2023-09-30T13:15:59');
        $result = (new CreateTransactionService($this->store(), $transaction, $transport->http))->execute();

        // Request body carries kind=pix and the qrCode group.
        $body = json_decode((string) $transport->lastRequest()->getBody(), true);
        self::assertSame('pix', $body['kind']);
        self::assertSame(3900, $body['amount']);
        self::assertSame(['dateTimeExpiration' => '2023-09-30T13:15:59'], $body['qrCode']);

        // Response is parsed into a QrCodeResponse.
        $qr = $result->getQrCodeResponse();
        self::assertInstanceOf(QrCodeResponse::class, $qr);
        self::assertSame('Pending', $qr->getStatus());
        self::assertSame(TransactionKind::Pix, $qr->getKind());
        self::assertNotNull($qr->getQrCodeImage());
        self::assertStringStartsWith('00020101', $qr->getQrCodeData());
    }

    #[Test]
    public function it_registers_a_pix_notification_url(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, '{"returnCode":"00","returnMessage":"Success"}');
        $erede = new eRede($this->store(), $transport->http);

        $registered = $erede->notificationUrl(
            (new NotificationUrl('https://example.test/webhook'))->withAuthorization('bearer', 'BEARER 123')
        );

        self::assertTrue($registered);
        self::assertStringEndsWith('/v1/transactions/notification-URL', (string) $transport->lastRequest()->getUri());
        $body = json_decode((string) $transport->lastRequest()->getBody(), true);
        self::assertSame('https://example.test/webhook', $body['URL']);
        self::assertSame(['type' => 'bearer', 'token' => 'BEARER 123'], $body['authorization']);
    }

    #[Test]
    public function it_parses_a_pix_payment_webhook(): void
    {
        $notification = PixNotification::fromJson(self::fixture('pix_webhook.json'));

        self::assertTrue($notification->isPayment());
        self::assertFalse($notification->isRefund());
        self::assertSame('90104480', $notification->merchantId);
        self::assertSame('41412312010933570004', $notification->qrCode);
        self::assertSame('937e77dd-f330-4b05-895c-60750763d397', $notification->id);
    }
}
