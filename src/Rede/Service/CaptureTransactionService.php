<?php

declare(strict_types=1);

namespace Rede\Service;

use Rede\Transaction;

class CaptureTransactionService extends AbstractTransactionsService
{
    public function execute(): Transaction
    {
        return $this->sendRequest(json_encode($this->transaction), AbstractService::PUT);
    }

    protected function getService(): string
    {
        return sprintf('%s/%s', parent::getService(), $this->transaction->getTid());
    }
}
