<?php

declare(strict_types=1);

namespace Rede;

use Rede\Enum\TransactionKind;

/**
 * Card Tokenization request body (POST /token-service/oauth/v2/tokenization).
 */
class CardTokenization implements RedeSerializable
{
    use SerializeTrait;

    public const COF_NOT_STORED = 0;
    public const COF_ALREADY_STORED = 2;

    private ?string $cardholderName = null;

    private ?string $securityCode = null;

    private ?TransactionKind $kind = null;

    private ?bool $embeddedZeroDollar = null;

    public function __construct(
        private string $email,
        private string $cardNumber,
        private string $expirationMonth,
        private string $expirationYear,
        private int $storageCard = self::COF_NOT_STORED,
    ) {
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getCardNumber(): string
    {
        return $this->cardNumber;
    }

    public function getStorageCard(): int
    {
        return $this->storageCard;
    }

    public function setStorageCard(int $storageCard): static
    {
        $this->storageCard = $storageCard;

        return $this;
    }

    public function setCardholderName(string $cardholderName): static
    {
        $this->cardholderName = $cardholderName;

        return $this;
    }

    public function setSecurityCode(string $securityCode): static
    {
        $this->securityCode = $securityCode;

        return $this;
    }

    public function setKind(TransactionKind $kind): static
    {
        $this->kind = $kind;

        return $this;
    }

    public function setEmbeddedZeroDollar(bool $embeddedZeroDollar): static
    {
        $this->embeddedZeroDollar = $embeddedZeroDollar;

        return $this;
    }
}
