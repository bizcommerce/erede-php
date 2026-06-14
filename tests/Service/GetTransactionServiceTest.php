<?php

declare(strict_types=1);

namespace Rede\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rede\Environment;
use Rede\Service\GetTransactionService;
use Rede\Store;
use Rede\Tests\Support\MockTransport;

#[CoversClass(GetTransactionService::class)]
final class GetTransactionServiceTest extends TestCase
{
    private function service(MockTransport $transport): GetTransactionService
    {
        $store = new Store('PV-123', 'secret', Environment::sandbox());
        MockTransport::seedToken($store);
        $transport->queue(200, '{"tid":"123"}');

        return new GetTransactionService($store, null, $transport->http);
    }

    #[Test]
    public function it_gets_a_transaction_by_tid(): void
    {
        $transport = new MockTransport();
        $this->service($transport)->setTid('123')->execute();

        $request = $transport->lastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('', (string) $request->getBody());
        self::assertStringEndsWith('/v1/transactions/123', (string) $request->getUri());
    }

    #[Test]
    public function it_gets_a_transaction_by_reference(): void
    {
        $transport = new MockTransport();
        $this->service($transport)->setReference('pedido-9')->execute();

        self::assertStringEndsWith('/v1/transactions?reference=pedido-9', (string) $transport->lastRequest()->getUri());
    }

    #[Test]
    public function reference_takes_precedence_over_tid(): void
    {
        $transport = new MockTransport();
        $service = $this->service($transport);
        $service->setTid('123')->setReference('pedido-9')->execute();

        self::assertStringEndsWith('/v1/transactions?reference=pedido-9', (string) $transport->lastRequest()->getUri());
    }

    #[Test]
    public function it_gets_refunds_for_a_tid(): void
    {
        $transport = new MockTransport();
        $service = $this->service($transport);
        $service->setTid('123')->setRefund(true)->execute();

        self::assertStringEndsWith('/v1/transactions/123/refunds', (string) $transport->lastRequest()->getUri());
    }
}
