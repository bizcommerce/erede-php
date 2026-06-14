<?php

declare(strict_types=1);

namespace Rede\Tests\Support;

use Rede\Service\GetTransactionService;
use Rede\Transaction;

/**
 * A transaction service whose execute() short-circuits to a canned result (no
 * network), while keeping the real setTid/setReference/setRefund + getService()
 * logic. Used by the eRede facade test to assert dispatch + endpoint shaping.
 */
final class RecordingTransactionService extends GetTransactionService
{
    public ?Transaction $result = null;

    public function execute(): Transaction
    {
        return $this->result ??= new Transaction();
    }

    /**
     * Exposes the protected endpoint builder for assertions.
     */
    public function service(): string
    {
        return $this->getService();
    }
}
