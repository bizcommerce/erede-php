<?php

declare(strict_types=1);

namespace Rede\Tests\Integration;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rede\Environment;
use Rede\Service\CaptureTransactionService;
use Rede\Service\CreateTransactionService;
use Rede\Service\GetTransactionService;
use Rede\Store;
use Rede\Tests\Support\Fixtures;
use Rede\Tests\Support\MockTransport;
use Rede\Transaction;

/**
 * End-to-end style coverage: real Store + real services + real Transaction
 * (de)serialization over a PSR-18 mock transport. Exercises the authorize ->
 * capture -> query path the way a caller would.
 */
final class TransactionFlowTest extends TestCase
{
    use Fixtures;

    private function store(): Store
    {
        $store = new Store('PV-123', 'secret-key', Environment::sandbox());
        MockTransport::seedToken($store);

        return $store;
    }

    #[Test]
    public function it_authorizes_then_captures_then_queries_a_transaction(): void
    {
        $store = $this->store();

        // 1) Authorize.
        $createTransport = new MockTransport();
        $createTransport->queue(200, self::fixture('transaction_authorized.json'));
        $transaction = new Transaction(25.0, 'pedido-1234');
        $authorized = (new CreateTransactionService($store, $transaction, $createTransport->http))->execute();

        self::assertSame('100120000000000001', $authorized->getTid());
        self::assertSame('00', $authorized->getReturnCode());
        self::assertSame('Authorized', $authorized->getAuthorization()->getStatus());

        // 2) Capture (PUT to the tid-scoped endpoint).
        $captureTransport = new MockTransport();
        $captureTransport->queue(200, self::fixture('transaction_authorized.json'));
        (new CaptureTransactionService($store, $authorized, $captureTransport->http))->execute();
        self::assertSame('PUT', $captureTransport->lastRequest()->getMethod());
        self::assertStringEndsWith('/v2/transactions/100120000000000001', (string) $captureTransport->lastRequest()->getUri());

        // 3) Query by tid.
        $getTransport = new MockTransport();
        $getTransport->queue(200, self::fixture('transaction_authorized.json'));
        $get = new GetTransactionService($store, null, $getTransport->http);
        $get->setTid('100120000000000001');
        $queried = $get->execute();
        self::assertSame('GET', $getTransport->lastRequest()->getMethod());
        self::assertSame('100120000000000001', $queried->getTid());
    }

    #[Test]
    public function a_recovered_401_still_yields_the_authorized_transaction(): void
    {
        // First call 401 -> token refresh (OAuth) -> retry succeeds. All three
        // requests flow through the same transport.
        $transport = new MockTransport();
        $transport->queue(401, '{"returnCode":"401","returnMessage":"expired"}');
        $transport->queue(200, self::fixture('oauth_token.json'));
        $transport->queue(200, self::fixture('transaction_authorized.json'));

        $service = new CreateTransactionService($this->store(), new Transaction(25.0, 'pedido-1234'), $transport->http);
        $result = $service->execute();

        self::assertSame('100120000000000001', $result->getTid());
        self::assertCount(3, $transport->requests(), 'service 401 + OAuth refresh + service retry');
    }

    #[Test]
    public function it_lists_refunds_for_a_transaction(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, self::fixture('transaction_with_refunds.json'));
        $get = new GetTransactionService($this->store(), null, $transport->http);
        $get->setTid('100120000000000001')->setRefund(true);

        $result = $get->execute();

        self::assertStringEndsWith('/v2/transactions/100120000000000001/refunds', (string) $transport->lastRequest()->getUri());
        self::assertCount(2, $result->getRefunds());
        self::assertSame('refund-aaa-111', $result->getRefunds()[0]->getRefundId());
    }
}
