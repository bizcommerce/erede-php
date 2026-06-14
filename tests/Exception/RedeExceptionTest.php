<?php

declare(strict_types=1);

namespace Rede\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rede\Exception\RedeException;
use RuntimeException;

#[CoversClass(RedeException::class)]
final class RedeExceptionTest extends TestCase
{
    #[Test]
    public function it_is_a_runtime_exception(): void
    {
        self::assertInstanceOf(RuntimeException::class, new RedeException('boom'));
    }

    #[Test]
    public function it_carries_the_message_code_and_previous_cause(): void
    {
        $previous = new RuntimeException('json broke');
        $exception = new RedeException('Transação negada', 56, $previous);

        self::assertSame('Transação negada', $exception->getMessage());
        self::assertSame(56, $exception->getCode());
        self::assertSame($previous, $exception->getPrevious());
    }
}
