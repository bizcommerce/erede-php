<?php

declare(strict_types=1);

namespace Rede\Service;

class CancelTransactionService extends AbstractTransactionsService
{
    protected function getService(): string
    {
        return sprintf('%s/%s/refunds', parent::getService(), $this->transaction->getTid());
    }
}
