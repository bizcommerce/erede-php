<?php

declare(strict_types=1);

namespace Rede\Tests\Domain;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rede\Address;
use Rede\SerializeTrait;

/**
 * SerializeTrait is exercised through Address, a representative consumer.
 */
#[CoversClass(SerializeTrait::class)]
#[CoversClass(Address::class)]
final class SerializeTraitTest extends TestCase
{
    #[Test]
    public function it_omits_null_properties(): void
    {
        $address = (new Address())->setCity('São Paulo')->setState('SP');

        self::assertSame(['city' => 'São Paulo', 'state' => 'SP'], $address->jsonSerialize());
    }

    #[Test]
    public function an_untouched_object_serializes_to_an_empty_array(): void
    {
        self::assertSame([], (new Address())->jsonSerialize());
    }

    #[Test]
    public function it_keeps_falsey_but_non_null_values(): void
    {
        // '0' is falsey yet not null and must survive the filter.
        $address = (new Address())->setNumber('0');

        self::assertSame(['number' => '0'], $address->jsonSerialize());
    }
}
