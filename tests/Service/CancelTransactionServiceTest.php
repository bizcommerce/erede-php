<?php

declare(strict_types=1);

namespace Rede\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rede\Environment;
use Rede\Service\CancelTransactionService;
use Rede\Store;
use Rede\Tests\Support\MockTransport;
use Rede\Transaction;

#[CoversClass(CancelTransactionService::class)]
final class CancelTransactionServiceTest extends TestCase
{
    #[Test]
    public function it_cancels_with_a_post_to_the_refunds_endpoint(): void
    {
        $store = new Store('PV-123', 'secret', Environment::sandbox());
        MockTransport::seedToken($store);
        $transport = new MockTransport();
        $transport->queue(200, '{"returnCode":"00"}');
        $transaction = (new Transaction(50.0))->setTid('999');

        (new CancelTransactionService($store, $transaction, $transport->http))->execute();

        $request = $transport->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertStringEndsWith('/v2/transactions/999/refunds', (string) $request->getUri());
    }
}
