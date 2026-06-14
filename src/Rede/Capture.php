<?php

declare(strict_types=1);

namespace Rede;

use DateTimeImmutable;

class Capture
{
    use CreateTrait;

    private ?int $amount = null;

    private ?DateTimeImmutable $dateTime = null;

    private ?string $nsu = null;

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDateTime(): ?DateTimeImmutable
    {
        return $this->dateTime;
    }

    public function setDateTime(string $dateTime): static
    {
        $this->dateTime = new DateTimeImmutable($dateTime);

        return $this;
    }

    public function getNsu(): ?string
    {
        return $this->nsu;
    }

    public function setNsu(string $nsu): static
    {
        $this->nsu = $nsu;

        return $this;
    }
}
