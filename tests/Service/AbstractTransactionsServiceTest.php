<?php

declare(strict_types=1);

namespace Rede\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rede\Environment;
use Rede\Exception\RedeException;
use Rede\Service\AbstractTransactionsService;
use Rede\Service\CreateTransactionService;
use Rede\Service\GetTransactionService;
use Rede\Store;
use Rede\Tests\Support\Fixtures;
use Rede\Tests\Support\MockTransport;
use Rede\Transaction;

#[CoversClass(AbstractTransactionsService::class)]
final class AbstractTransactionsServiceTest extends TestCase
{
    use Fixtures;

    private function store(): Store
    {
        $store = new Store('PV-123', 'secret-key', Environment::sandbox());
        MockTransport::seedToken($store);

        return $store;
    }

    #[Test]
    public function execute_posts_the_serialized_transaction_to_the_transactions_endpoint(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, self::fixture('transaction_authorized.json'));
        $transaction = new Transaction(25.0, 'ref-1');
        $service = new CreateTransactionService($this->store(), $transaction, $transport->http);

        $expectedBody = json_encode($transaction->jsonSerialize());
        $result = $service->execute();

        $request = $transport->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertStringEndsWith('/v1/transactions', (string) $request->getUri());
        self::assertSame($expectedBody, (string) $request->getBody());
        self::assertSame('00', $result->getReturnCode());
        self::assertSame('100120000000000001', $result->getTid());
    }

    #[Test]
    public function parse_response_throws_a_rede_exception_carrying_the_gateway_message_and_code(): void
    {
        $transport = new MockTransport();
        $transport->queue(400, self::fixture('error_4xx.json'));
        $service = new CreateTransactionService($this->store(), new Transaction(10.0), $transport->http);

        try {
            $service->execute();
            self::fail('A 4xx response must raise a RedeException.');
        } catch (RedeException $e) {
            self::assertSame('Transação não permitida para o cartão.', $e->getMessage());
            self::assertSame(56, $e->getCode());
        }
    }

    #[Test]
    public function parse_response_lazily_creates_a_transaction_when_none_was_supplied(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, self::fixture('transaction_authorized.json'));
        $service = new GetTransactionService($this->store(), null, $transport->http);
        $service->setTid('100120000000000001');

        $result = $service->execute();

        self::assertInstanceOf(Transaction::class, $result);
        self::assertSame('100120000000000001', $result->getTid());
    }

    #[Test]
    public function a_successful_response_with_unparseable_json_does_not_throw(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, 'definitely-not-json');
        $service = new CreateTransactionService($this->store(), new Transaction(10.0), $transport->http);

        self::assertInstanceOf(Transaction::class, $service->execute());
    }
}
