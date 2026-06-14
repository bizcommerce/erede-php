<?php

declare(strict_types=1);

namespace Rede\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rede\Environment;
use Rede\Service\CaptureTransactionService;
use Rede\Store;
use Rede\Tests\Support\MockTransport;
use Rede\Transaction;

#[CoversClass(CaptureTransactionService::class)]
final class CaptureTransactionServiceTest extends TestCase
{
    private function capture(Transaction $transaction): MockTransport
    {
        $store = new Store('PV-123', 'secret', Environment::sandbox());
        MockTransport::seedToken($store);
        $transport = new MockTransport();
        $transport->queue(200, '{"returnCode":"00"}');

        (new CaptureTransactionService($store, $transaction, $transport->http))->execute();

        return $transport;
    }

    #[Test]
    public function it_captures_with_a_put_to_the_tid_scoped_endpoint(): void
    {
        $transport = $this->capture((new Transaction(50.0))->setTid('999'));

        $request = $transport->lastRequest();
        self::assertSame('PUT', $request->getMethod());
        self::assertStringEndsWith('/v1/transactions/999', (string) $request->getUri());
    }

    /**
     * Regression guard for the JsonSerializable fix: the capture amount must
     * reach Rede rather than being dropped as "{}".
     */
    #[Test]
    public function it_sends_the_capture_amount_in_the_body(): void
    {
        $transaction = (new Transaction(50.0))->setTid('999');
        $transport = $this->capture($transaction);

        self::assertSame(['amount' => 5000], json_decode((string) $transport->lastRequest()->getBody(), true));
        self::assertSame(json_encode($transaction), (string) $transport->lastRequest()->getBody());
    }
}
