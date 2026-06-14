<?php

declare(strict_types=1);

namespace Rede;

use DateTimeImmutable;

class Refund
{
    use CreateTrait;

    private ?int $amount = null;

    private ?DateTimeImmutable $refundDateTime = null;

    private ?string $refundId = null;

    private ?string $status = null;

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getRefundDateTime(): ?DateTimeImmutable
    {
        return $this->refundDateTime;
    }

    public function setRefundDateTime(string $refundDateTime): static
    {
        $this->refundDateTime = new DateTimeImmutable($refundDateTime);

        return $this;
    }

    public function getRefundId(): ?string
    {
        return $this->refundId;
    }

    public function setRefundId(string $refundId): static
    {
        $this->refundId = $refundId;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
