<?php

declare(strict_types=1);

namespace Rede;

/**
 * Token management request body (PUT /token-service/oauth/v2/tokenization/{id}):
 * delete, suspend or reactivate a token.
 */
class TokenManagement implements RedeSerializable
{
    use SerializeTrait;

    public const STATUS_DELETE = 'delete';
    public const STATUS_SUSPEND = 'suspend';
    public const STATUS_REACTIVATE = 'reactivate';

    public const REASON_CUSTOMER_REQUEST = 1;
    public const REASON_FRAUD_SUSPICION = 2;

    public function __construct(
        private string $tokenizationStatus,
        private int $reason = self::REASON_CUSTOMER_REQUEST,
    ) {
    }

    public function getTokenizationStatus(): string
    {
        return $this->tokenizationStatus;
    }

    public function getReason(): int
    {
        return $this->reason;
    }
}
