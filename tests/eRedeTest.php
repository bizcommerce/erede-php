<?php

declare(strict_types=1);

namespace Rede\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rede\eRede;
use Rede\Environment;
use Rede\Http\HttpClient;
use Rede\Service\AbstractTransactionsService;
use Rede\Service\CancelTransactionService;
use Rede\Service\CaptureTransactionService;
use Rede\Service\CreateTransactionService;
use Rede\Service\GetTransactionService;
use Rede\Store;
use Rede\Tests\Support\MockTransport;
use Rede\Tests\Support\RecordingTransactionService;
use Rede\Transaction;

/**
 * Spy over the facade's only seam: every dispatch is recorded and a canned-result
 * service is returned, so we can assert WHICH service each facade method
 * dispatches to and HOW it configures it — without the network.
 */
final class SpyRede extends eRede
{
    /**
     * @var array<int, array{class: string, service: RecordingTransactionService, amount: ?int, capture: mixed}>
     */
    public array $dispatched = [];

    public function __construct(private Store $spyStore, private HttpClient $spyHttp)
    {
        parent::__construct($spyStore, $spyHttp);
    }

    protected function transactionService(string $serviceClass, ?Transaction $transaction = null): AbstractTransactionsService
    {
        $service = new RecordingTransactionService($this->spyStore, $transaction, $this->spyHttp);
        $service->result = (new Transaction())->setTid('resp-1');

        $this->dispatched[] = [
            'class' => $serviceClass,
            'service' => $service,
            'amount' => $transaction?->getAmount(),
            'capture' => $transaction?->getCapture(),
        ];

        return $service;
    }

    public function last(): array
    {
        return $this->dispatched[count($this->dispatched) - 1];
    }
}

#[CoversClass(eRede::class)]
final class eRedeTest extends TestCase
{
    private function spy(): SpyRede
    {
        return new SpyRede(new Store('PV-123', 'secret', Environment::sandbox()), (new MockTransport())->http);
    }

    #[Test]
    public function create_dispatches_to_the_create_service_with_the_transaction(): void
    {
        $spy = $this->spy();

        $result = $spy->create(new Transaction(25.0, 'ref-1'));

        self::assertSame(CreateTransactionService::class, $spy->last()['class']);
        self::assertSame('resp-1', $result->getTid());
    }

    #[Test]
    public function authorize_is_an_alias_of_create(): void
    {
        $spy = $this->spy();
        $spy->authorize(new Transaction(25.0, 'ref-1'));

        self::assertSame(CreateTransactionService::class, $spy->last()['class']);
    }

    #[Test]
    public function capture_dispatches_to_the_capture_service(): void
    {
        $spy = $this->spy();
        $spy->capture((new Transaction(25.0))->setTid('999'));

        self::assertSame(CaptureTransactionService::class, $spy->last()['class']);
    }

    #[Test]
    public function cancel_dispatches_to_the_cancel_service(): void
    {
        $spy = $this->spy();
        $spy->cancel((new Transaction(25.0))->setTid('999'));

        self::assertSame(CancelTransactionService::class, $spy->last()['class']);
    }

    #[Test]
    public function get_dispatches_to_the_get_service_scoped_by_tid(): void
    {
        $spy = $this->spy();
        $spy->get('123');

        self::assertSame(GetTransactionService::class, $spy->last()['class']);
        self::assertStringEndsWith('transactions/123', $spy->last()['service']->service());
    }

    #[Test]
    public function get_by_id_is_an_alias_of_get(): void
    {
        $spy = $this->spy();
        $spy->getById('123');

        self::assertStringEndsWith('transactions/123', $spy->last()['service']->service());
    }

    #[Test]
    public function get_by_reference_configures_the_reference_query(): void
    {
        $spy = $this->spy();
        $spy->getByReference('pedido-9');

        self::assertSame(GetTransactionService::class, $spy->last()['class']);
        self::assertStringEndsWith('transactions?reference=pedido-9', $spy->last()['service']->service());
    }

    #[Test]
    public function get_refunds_scopes_to_the_refunds_subresource(): void
    {
        $spy = $this->spy();
        $spy->getRefunds('123');

        self::assertStringEndsWith('transactions/123/refunds', $spy->last()['service']->service());
    }

    #[Test]
    public function zero_dispatches_a_create_with_a_zeroed_amount_and_capture_enabled(): void
    {
        $spy = $this->spy();
        $transaction = new Transaction(25.0, 'ref-1');

        $result = $spy->zero($transaction);

        // zero() dispatches a capture-on, amount-zero authorization...
        self::assertSame(CreateTransactionService::class, $spy->last()['class']);
        self::assertSame(0, $spy->last()['amount']);
        self::assertTrue($spy->last()['capture']);
        // ...then restores the original amount (2500 cents, not 250000) on the way out.
        self::assertSame(2500, $result->getAmount());
    }
}
