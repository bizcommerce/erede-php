<?php

declare(strict_types=1);

namespace Rede\Tests\Domain;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rede\Authorization;
use Rede\CreateTrait;
use Rede\Enum\TransactionKind;

/**
 * CreateTrait is exercised through Authorization, a representative consumer.
 */
#[CoversClass(CreateTrait::class)]
#[CoversClass(Authorization::class)]
final class CreateTraitTest extends TestCase
{
    #[Test]
    public function it_hydrates_known_properties_from_a_std_class(): void
    {
        $authorization = Authorization::create((object) [
            'amount' => 2500,
            'status' => 'Authorized',
            'authorizationCode' => '123456',
        ]);

        self::assertSame(2500, $authorization->getAmount());
        self::assertSame('Authorized', $authorization->getStatus());
        self::assertSame('123456', $authorization->getAuthorizationCode());
    }

    #[Test]
    public function it_ignores_unknown_response_fields(): void
    {
        $authorization = Authorization::create((object) [
            'amount' => 100,
            'totallyUnknownField' => 'whatever',
        ]);

        self::assertSame(100, $authorization->getAmount());
        self::assertFalse(property_exists($authorization, 'totallyUnknownField'));
    }

    #[Test]
    public function it_converts_date_fields_to_immutable_datetimes(): void
    {
        $authorization = Authorization::create((object) ['dateTime' => '2024-01-15T10:30:00+00:00']);

        self::assertInstanceOf(DateTimeImmutable::class, $authorization->getDateTime());
        self::assertSame('2024-01-15 10:30:00', $authorization->getDateTime()->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_coerces_scalars_and_enums_to_the_declared_type(): void
    {
        // The gateway may send "origin" as int and "kind" as a string.
        $authorization = Authorization::create((object) ['origin' => 1, 'kind' => 'credit']);

        self::assertSame('1', $authorization->getOrigin());
        self::assertSame(TransactionKind::Credit, $authorization->getKind());
    }
}
