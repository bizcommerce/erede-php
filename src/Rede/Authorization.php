<?php

declare(strict_types=1);

namespace Rede;

use DateTimeImmutable;
use Rede\Enum\TransactionKind;

class Authorization
{
    use CreateTrait;

    private ?string $affiliation = null;

    private ?int $amount = null;

    private ?string $authorizationCode = null;

    private ?string $cardBin = null;

    private ?string $cardHolderName = null;

    private ?DateTimeImmutable $dateTime = null;

    private ?int $installments = null;

    private ?TransactionKind $kind = null;

    private ?string $last4 = null;

    private ?string $nsu = null;

    private ?string $origin = null;

    private ?string $reference = null;

    private ?string $returnCode = null;

    private ?string $returnMessage = null;

    private ?string $status = null;

    private ?string $subscription = null;

    private ?string $tid = null;

    public function getAffiliation(): ?string
    {
        return $this->affiliation;
    }

    public function setAffiliation(string $affiliation): static
    {
        $this->affiliation = $affiliation;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getAuthorizationCode(): ?string
    {
        return $this->authorizationCode;
    }

    public function setAuthorizationCode(string $authorizationCode): static
    {
        $this->authorizationCode = $authorizationCode;

        return $this;
    }

    public function getCardBin(): ?string
    {
        return $this->cardBin;
    }

    public function setCardBin(string $cardBin): static
    {
        $this->cardBin = $cardBin;

        return $this;
    }

    public function getCardHolderName(): ?string
    {
        return $this->cardHolderName;
    }

    public function setCardHolderName(string $cardHolderName): static
    {
        $this->cardHolderName = $cardHolderName;

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

    public function getInstallments(): ?int
    {
        return $this->installments;
    }

    public function setInstallments(int $installments): static
    {
        $this->installments = $installments;

        return $this;
    }

    public function getKind(): ?TransactionKind
    {
        return $this->kind;
    }

    public function setKind(TransactionKind $kind): static
    {
        $this->kind = $kind;

        return $this;
    }

    public function getLast4(): ?string
    {
        return $this->last4;
    }

    public function setLast4(string $last4): static
    {
        $this->last4 = $last4;

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

    public function getOrigin(): ?string
    {
        return $this->origin;
    }

    public function setOrigin(string $origin): static
    {
        $this->origin = $origin;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getReturnCode(): ?string
    {
        return $this->returnCode;
    }

    public function setReturnCode(string $returnCode): static
    {
        $this->returnCode = $returnCode;

        return $this;
    }

    public function getReturnMessage(): ?string
    {
        return $this->returnMessage;
    }

    public function setReturnMessage(string $returnMessage): static
    {
        $this->returnMessage = $returnMessage;

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

    public function getSubscription(): ?string
    {
        return $this->subscription;
    }

    public function setSubscription(string $subscription): static
    {
        $this->subscription = $subscription;

        return $this;
    }

    public function getTid(): ?string
    {
        return $this->tid;
    }

    public function setTid(string $tid): static
    {
        $this->tid = $tid;

        return $this;
    }
}
