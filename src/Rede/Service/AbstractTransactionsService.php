<?php

declare(strict_types=1);

namespace Rede\Service;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Rede\Exception\RedeException;
use Rede\Http\HttpClient;
use Rede\Store;
use Rede\Transaction;

abstract class AbstractTransactionsService extends AbstractService
{
    private ?string $tid = null;

    public function __construct(
        Store $store,
        protected ?Transaction $transaction = null,
        ?HttpClient $http = null,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($store, $http, $logger);
    }

    /**
     * @throws InvalidArgumentException|\RuntimeException|RedeException
     */
    public function execute(): Transaction
    {
        return $this->sendRequest(json_encode($this->transaction->jsonSerialize()), AbstractService::POST);
    }

    protected function getService(): string
    {
        return 'transactions';
    }

    public function getTid(): ?string
    {
        return $this->tid;
    }

    public function setTid(?string $tid): static
    {
        $this->tid = $tid;

        return $this;
    }

    /**
     * @throws RedeException|InvalidArgumentException
     */
    protected function parseResponse(string $response, int $statusCode): Transaction
    {
        $previous = null;

        if ($this->transaction === null) {
            $this->transaction = new Transaction();
        }

        try {
            $this->transaction->jsonUnserialize($response);
        } catch (InvalidArgumentException $e) {
            $previous = $e;
        }

        if ($statusCode >= 400) {
            throw new RedeException(
                (string) $this->transaction->getReturnMessage(),
                (int) $this->transaction->getReturnCode(),
                $previous
            );
        }

        return $this->transaction;
    }
}
