<?php

declare(strict_types=1);

namespace Rede;

use Psr\Log\LoggerInterface;
use Rede\Http\HttpClient;
use Rede\Service\AbstractTransactionsService;
use Rede\Service\CancelTransactionService;
use Rede\Service\CaptureTransactionService;
use Rede\Service\CreateTransactionService;
use Rede\Service\CardTokenizationRequestService;
use Rede\Service\GetTransactionService;
use Rede\Service\NotificationUrlService;
use Rede\Service\TokenizationQueryService;
use Rede\Service\TokenManagementService;

class eRede
{
    public const USER_AGENT = 'eRede/4.0 (SDK; PHP;)';

    private HttpClient $http;

    public function __construct(
        private readonly Store $store,
        ?HttpClient $http = null,
        private readonly ?LoggerInterface $logger = null,
    ) {
        $this->http = $http ?? new HttpClient();
    }

    /**
     * @see eRede::create()
     */
    public function authorize(Transaction $transaction): Transaction
    {
        return $this->create($transaction);
    }

    public function create(Transaction $transaction): Transaction
    {
        return $this->transactionService(CreateTransactionService::class, $transaction)->execute();
    }

    public function cancel(Transaction $transaction): Transaction
    {
        return $this->transactionService(CancelTransactionService::class, $transaction)->execute();
    }

    public function capture(Transaction $transaction): Transaction
    {
        return $this->transactionService(CaptureTransactionService::class, $transaction)->execute();
    }

    /**
     * @see eRede::get()
     */
    public function getById(string $tid): Transaction
    {
        return $this->get($tid);
    }

    public function get(string $tid): Transaction
    {
        $service = $this->transactionService(GetTransactionService::class);
        $service->setTid($tid);

        return $service->execute();
    }

    public function getByReference(string $reference): Transaction
    {
        $service = $this->transactionService(GetTransactionService::class);
        $service->setReference($reference);

        return $service->execute();
    }

    public function getRefunds(string $tid): Transaction
    {
        $service = $this->transactionService(GetTransactionService::class);
        $service->setTid($tid);
        $service->setRefund(true);

        return $service->execute();
    }

    /**
     * Registers the webhook URL that receives Pix status notifications.
     */
    public function notificationUrl(NotificationUrl $notificationUrl): bool
    {
        return (new NotificationUrlService($this->store, $notificationUrl, $this->http, $this->logger))->execute();
    }

    /**
     * Requests a card token (Card / Brand Tokenization).
     */
    public function tokenizeCard(CardTokenization $card): Tokenization
    {
        return (new CardTokenizationRequestService($this->store, $card, $this->http, $this->logger))->execute();
    }

    /**
     * Queries a tokenization by its id.
     */
    public function queryToken(string $tokenizationId): Tokenization
    {
        return (new TokenizationQueryService($this->store, $tokenizationId, $this->http, $this->logger))->execute();
    }

    /**
     * Deletes, suspends or reactivates a token.
     */
    public function manageToken(string $tokenizationId, TokenManagement $management): Tokenization
    {
        return (new TokenManagementService($this->store, $tokenizationId, $management, $this->http, $this->logger))->execute();
    }

    /**
     * Runs a zero-dollar authorization (card validation / tokenization) and
     * restores the caller's original amount and capture flag afterwards.
     */
    public function zero(Transaction $transaction): Transaction
    {
        $amount = (int) $transaction->getAmount();
        $capture = (bool) $transaction->getCapture();

        $transaction->setAmount(0);
        $transaction->capture();

        $transaction = $this->create($transaction);

        // getAmount() returns cents while setAmount() expects the major unit (it
        // multiplies by 100), so divide to restore the original value instead of
        // scaling it a second time (2500 cents -> 25.00 -> 2500).
        $transaction->setAmount($amount / 100);
        $transaction->capture($capture);

        return $transaction;
    }

    /**
     * Builds the transaction service the facade delegates to. Centralised so the
     * wiring (store + transport + logger) lives in one place and tests can
     * intercept which service a facade method dispatches to.
     *
     * @template T of AbstractTransactionsService
     *
     * @param class-string<T> $serviceClass
     *
     * @return T
     */
    protected function transactionService(string $serviceClass, ?Transaction $transaction = null): AbstractTransactionsService
    {
        return new $serviceClass($this->store, $transaction, $this->http, $this->logger);
    }
}
