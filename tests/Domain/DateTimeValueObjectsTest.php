<?php

declare(strict_types=1);

namespace Rede\Tests\Domain;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rede\Authorization;
use Rede\Capture;
use Rede\Refund;

/**
 * The response value objects parse date strings into DateTimeImmutable instances.
 */
#[CoversClass(Authorization::class)]
#[CoversClass(Capture::class)]
#[CoversClass(Refund::class)]
final class DateTimeValueObjectsTest extends TestCase
{
    #[Test]
    public function authorization_parses_its_datetime(): void
    {
        $authorization = (new Authorization())->setDateTime('2024-01-15T10:30:00+00:00');

        self::assertInstanceOf(DateTimeImmutable::class, $authorization->getDateTime());
        self::assertSame('2024-01-15 10:30:00', $authorization->getDateTime()->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function capture_parses_its_datetime_and_keeps_other_setters_fluent(): void
    {
        $capture = (new Capture())->setAmount(2500)->setNsu('000123')->setDateTime('2024-01-16T08:00:00+00:00');

        self::assertSame(2500, $capture->getAmount());
        self::assertSame('000123', $capture->getNsu());
        self::assertInstanceOf(DateTimeImmutable::class, $capture->getDateTime());
    }

    #[Test]
    public function refund_parses_its_refund_datetime(): void
    {
        $refund = (new Refund())
            ->setAmount(500)
            ->setRefundId('rf-1')
            ->setStatus('PENDING')
            ->setRefundDateTime('2024-01-17T09:15:00+00:00');

        self::assertSame(500, $refund->getAmount());
        self::assertSame('rf-1', $refund->getRefundId());
        self::assertSame('PENDING', $refund->getStatus());
        self::assertInstanceOf(DateTimeImmutable::class, $refund->getRefundDateTime());
    }
}
