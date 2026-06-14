<?php

declare(strict_types=1);

namespace Rede;

use DateTimeImmutable;
use Rede\Enum\TransactionKind;

/**
 * The "qrCodeResponse" group returned for a pending Pix QR Code.
 */
class QrCodeResponse
{
    use CreateTrait;

    private ?DateTimeImmutable $dateTime = null;

    private ?string $returnCode = null;

    private ?string $returnMessage = null;

    private ?string $affiliation = null;

    private ?TransactionKind $kind = null;

    private ?string $reference = null;

    private ?int $amount = null;

    private ?string $tid = null;

    private ?string $status = null;

    private ?DateTimeImmutable $expirationQrCode = null;

    private ?string $qrCodeImage = null;

    private ?string $qrCodeData = null;

    public function getDateTime(): ?DateTimeImmutable
    {
        return $this->dateTime;
    }

    public function getReturnCode(): ?string
    {
        return $this->returnCode;
    }

    public function getReturnMessage(): ?string
    {
        return $this->returnMessage;
    }

    public function getAffiliation(): ?string
    {
        return $this->affiliation;
    }

    public function getKind(): ?TransactionKind
    {
        return $this->kind;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function getTid(): ?string
    {
        return $this->tid;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getExpirationQrCode(): ?DateTimeImmutable
    {
        return $this->expirationQrCode;
    }

    /**
     * @return string|null Base64-encoded QR Code image.
     */
    public function getQrCodeImage(): ?string
    {
        return $this->qrCodeImage;
    }

    /**
     * @return string|null EMV copy-and-paste string.
     */
    public function getQrCodeData(): ?string
    {
        return $this->qrCodeData;
    }
}
