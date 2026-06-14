<?php

declare(strict_types=1);

namespace Rede;

use DateTimeImmutable;
use stdClass;

/**
 * Tokenization response (request / query / management). Flattens the nested
 * "brand" and "token" groups into typed accessors.
 */
class Tokenization
{
    public const STATUS_PENDING = 'Pending';
    public const STATUS_ACTIVE = 'Active';

    private ?string $returnCode = null;

    private ?string $returnMessage = null;

    private ?string $tokenizationId = null;

    private ?string $affiliation = null;

    private ?string $tokenizationStatus = null;

    private ?string $bin = null;

    private ?string $last4 = null;

    private ?DateTimeImmutable $lastModifiedDate = null;

    private ?string $brandName = null;

    private ?string $brandTokenStatus = null;

    private ?string $brandTid = null;

    private ?string $tokenCode = null;

    private ?string $tokenExpirationDate = null;

    public static function fromResponse(stdClass $data): self
    {
        $tokenization = new self();

        $tokenization->returnCode = self::str($data->returnCode ?? null);
        $tokenization->returnMessage = self::str($data->returnMessage ?? null);
        $tokenization->tokenizationId = self::str($data->tokenizationId ?? null);
        $tokenization->affiliation = self::str($data->affiliation ?? null);
        $tokenization->tokenizationStatus = self::str($data->tokenizationStatus ?? null);
        $tokenization->bin = self::str($data->bin ?? null);
        $tokenization->last4 = self::str($data->last4 ?? null);

        if (isset($data->lastModifiedDate)) {
            $tokenization->lastModifiedDate = new DateTimeImmutable((string) $data->lastModifiedDate);
        }

        if (isset($data->brand) && is_object($data->brand)) {
            $tokenization->brandName = self::str($data->brand->name ?? null);
            $tokenization->brandTokenStatus = self::str($data->brand->tokenstatus ?? null);
            $tokenization->brandTid = self::str($data->brand->brandTid ?? null);
        }

        if (isset($data->token) && is_object($data->token)) {
            $tokenization->tokenCode = self::str($data->token->code ?? null);
            $tokenization->tokenExpirationDate = self::str($data->token->expirationDate ?? null);
        }

        return $tokenization;
    }

    private static function str(mixed $value): ?string
    {
        return $value === null ? null : (string) $value;
    }

    public function isSuccessful(): bool
    {
        return $this->returnCode === '00';
    }

    public function getReturnCode(): ?string
    {
        return $this->returnCode;
    }

    public function getReturnMessage(): ?string
    {
        return $this->returnMessage;
    }

    public function getTokenizationId(): ?string
    {
        return $this->tokenizationId;
    }

    public function getAffiliation(): ?string
    {
        return $this->affiliation;
    }

    public function getTokenizationStatus(): ?string
    {
        return $this->tokenizationStatus;
    }

    public function getBin(): ?string
    {
        return $this->bin;
    }

    public function getLast4(): ?string
    {
        return $this->last4;
    }

    public function getLastModifiedDate(): ?DateTimeImmutable
    {
        return $this->lastModifiedDate;
    }

    public function getBrandName(): ?string
    {
        return $this->brandName;
    }

    public function getBrandTokenStatus(): ?string
    {
        return $this->brandTokenStatus;
    }

    public function getBrandTid(): ?string
    {
        return $this->brandTid;
    }

    public function getTokenCode(): ?string
    {
        return $this->tokenCode;
    }

    public function getTokenExpirationDate(): ?string
    {
        return $this->tokenExpirationDate;
    }
}
