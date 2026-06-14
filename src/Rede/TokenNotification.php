<?php

declare(strict_types=1);

namespace Rede;

use InvalidArgumentException;

/**
 * Parses an inbound brand-tokenization webhook (PV.TOKENIZACAO-BANDEIRA). After
 * receiving one, query the tokenization by id to learn what changed.
 */
final class TokenNotification
{
    public const EVENT = 'PV.TOKENIZACAO-BANDEIRA';

    /**
     * @param string[] $events
     */
    private function __construct(
        public readonly ?string $id,
        public readonly ?string $merchantId,
        public readonly array $events,
        public readonly ?string $tokenizationId,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            $payload['id'] ?? null,
            $payload['merchantId'] ?? $payload['merchant_id'] ?? null,
            $payload['events'] ?? [],
            $payload['data']['tokenizationId'] ?? null,
        );
    }

    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);

        if (!is_array($data)) {
            throw new InvalidArgumentException('Invalid tokenization notification payload.');
        }

        return self::fromArray($data);
    }

    public function hasEvent(string $event): bool
    {
        return in_array($event, $this->events, true);
    }
}
