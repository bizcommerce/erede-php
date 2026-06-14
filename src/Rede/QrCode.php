<?php

declare(strict_types=1);

namespace Rede;

/**
 * The "qrCode" request group attached to a Pix transaction. Carries the QR Code
 * expiration (YYYY-MM-DDThh:mm:ss, max 15 days ahead).
 *
 * NOTE: confirm the exact JSON key against the sandbox — the manual renders it
 * ambiguously; this uses the camelCase form. Change {@see EXPIRATION_KEY} only.
 */
class QrCode implements RedeSerializable
{
    public const EXPIRATION_KEY = 'dateTimeExpiration';

    public function __construct(private string $dateTimeExpiration)
    {
    }

    public function getDateTimeExpiration(): string
    {
        return $this->dateTimeExpiration;
    }

    public function setDateTimeExpiration(string $dateTimeExpiration): static
    {
        $this->dateTimeExpiration = $dateTimeExpiration;

        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return [self::EXPIRATION_KEY => $this->dateTimeExpiration];
    }
}
