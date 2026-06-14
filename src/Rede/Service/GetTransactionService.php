<?php

declare(strict_types=1);

namespace Rede\Service;

use Rede\Transaction;

class GetTransactionService extends AbstractTransactionsService
{
    private ?string $reference = null;

    private bool $refund = false;

    public function execute(): Transaction
    {
        return $this->sendRequest(null, AbstractService::GET);
    }

    protected function getService(): string
    {
        if ($this->reference !== null) {
            return sprintf('%s?reference=%s', parent::getService(), $this->reference);
        }

        if ($this->refund) {
            return sprintf('%s/%s/refunds', parent::getService(), $this->getTid());
        }

        return sprintf('%s/%s', parent::getService(), $this->getTid());
    }

    public function setReference(?string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function setRefund(bool $refund = true): static
    {
        $this->refund = $refund;

        return $this;
    }
}
