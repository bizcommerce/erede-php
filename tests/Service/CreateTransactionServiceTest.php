<?php

declare(strict_types=1);

namespace Rede\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rede\Environment;
use Rede\Service\CreateTransactionService;
use Rede\Store;
use Rede\Tests\Support\MockTransport;
use Rede\Transaction;

#[CoversClass(CreateTransactionService::class)]
final class CreateTransactionServiceTest extends TestCase
{
    #[Test]
    public function it_posts_the_serialized_transaction_to_the_transactions_collection(): void
    {
        $store = new Store('PV-123', 'secret', Environment::sandbox());
        MockTransport::seedToken($store);
        $transport = new MockTransport();
        $transport->queue(200, '{"returnCode":"00"}');
        $transaction = new Transaction(25.0, 'ref-1');

        (new CreateTransactionService($store, $transaction, $transport->http))->execute();

        $request = $transport->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertStringEndsWith('/v2/transactions', (string) $request->getUri());
        self::assertSame(json_encode($transaction->jsonSerialize()), (string) $request->getBody());
    }
}
