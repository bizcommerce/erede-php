<?php

declare(strict_types=1);

namespace Rede\Tests\Support;

use Psr\Log\AbstractLogger;
use Stringable;

/**
 * A minimal PSR-3 logger that keeps every record in memory so tests can assert
 * on what the SDK logged (e.g. that secrets were redacted).
 */
final class ArrayLogger extends AbstractLogger
{
    /**
     * @var array<int, array{level: mixed, message: string, context: array}>
     */
    public array $records = [];

    public function log($level, string|Stringable $message, array $context = []): void
    {
        $this->records[] = [
            'level' => $level,
            'message' => (string) $message,
            'context' => $context,
        ];
    }

    /**
     * All logged messages joined together, for convenient substring assertions.
     */
    public function dump(): string
    {
        return implode("\n", array_column($this->records, 'message'));
    }
}
