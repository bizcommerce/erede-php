<?php

declare(strict_types=1);

namespace Rede;

use InvalidArgumentException;

/**
 * Parses an inbound Pix webhook notification (PV.UPDATE_TRANSACTION_PIX /
 * PV.REFUND_PIX). After receiving one, query the transaction by TID for details.
 */
final class PixNotification
{
    public const EVENT_PAYMENT = 'PV.UPDATE_TRANSACTION_PIX';
    public const EVENT_REFUND = 'PV.REFUND_PIX';

    /**
     * @param string[] $events
     */
    private function __construct(
        public readonly ?string $id,
        public readonly ?string $merchantId,
        public readonly array $events,
        public readonly ?string $qrCode,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            $payload['id'] ?? null,
            $payload['merchantId'] ?? $payload['companyNumber'] ?? null,
            $payload['events'] ?? [],
            $payload['data']['qrcode'] ?? $payload['data']['id'] ?? null,
        );
    }

    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);

        if (!is_array($data)) {
            throw new InvalidArgumentException('Invalid Pix notification payload.');
        }

        return self::fromArray($data);
    }

    public function hasEvent(string $event): bool
    {
        return in_array($event, $this->events, true);
    }

    public function isPayment(): bool
    {
        return $this->hasEvent(self::EVENT_PAYMENT);
    }

    public function isRefund(): bool
    {
        return $this->hasEvent(self::EVENT_REFUND);
    }
}
