<?php

declare(strict_types=1);

namespace Rede\Tests\Domain;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rede\Address;
use Rede\Antifraud;
use Rede\Enum\ItemType;
use Rede\Enum\PhoneType;
use Rede\Enum\ResidenceType;
use Rede\Enum\UrlKind;
use Rede\Item;
use Rede\Phone;
use Rede\Url;

#[CoversClass(Address::class)]
#[CoversClass(Antifraud::class)]
#[CoversClass(Item::class)]
#[CoversClass(Phone::class)]
#[CoversClass(Url::class)]
final class SimpleValueObjectsTest extends TestCase
{
    #[Test]
    public function address_setters_are_fluent_and_typed(): void
    {
        $address = (new Address())
            ->setAddress('Av. Paulista')
            ->setNumber('1000')
            ->setCity('São Paulo')
            ->setState('SP')
            ->setZipCode('01310-100')
            ->setType(ResidenceType::Commercial);

        self::assertSame('Av. Paulista', $address->getAddress());
        self::assertSame('1000', $address->getNumber());
        self::assertSame('São Paulo', $address->getCity());
        self::assertSame('SP', $address->getState());
        self::assertSame('01310-100', $address->getZipCode());
        self::assertSame(ResidenceType::Commercial, $address->getType());
    }

    #[Test]
    public function item_constructor_defaults_to_a_physical_type(): void
    {
        $item = new Item('sku-1', 3);

        self::assertSame('sku-1', $item->getId());
        self::assertSame(3, $item->getQuantity());
        self::assertSame(ItemType::Physical, $item->getType());
    }

    #[Test]
    public function item_setters_are_fluent_and_serialize_cleanly(): void
    {
        $item = (new Item('sku-1', 1, ItemType::Digital))
            ->setAmount(2500)
            ->setDescription('A widget')
            ->setFreight(0)
            ->setDiscount(0);

        self::assertSame(2500, $item->getAmount());
        self::assertSame('A widget', $item->getDescription());
        self::assertSame(ItemType::Digital, $item->getType());
        // Zero freight/discount survive serialization (not null).
        self::assertSame(0, $item->jsonSerialize()['freight']);
        self::assertSame(0, $item->jsonSerialize()['discount']);
        // The enum serializes to its int value through json_encode.
        self::assertSame(2, json_decode(json_encode($item), true)['type']);
    }

    #[Test]
    public function phone_constructor_defaults_to_cellphone(): void
    {
        $phone = new Phone('11', '999998888');

        self::assertSame('11', $phone->getDdd());
        self::assertSame('999998888', $phone->getNumber());
        self::assertSame(PhoneType::Cellphone, $phone->getType());
    }

    #[Test]
    public function url_constructor_defaults_to_the_callback_kind(): void
    {
        $url = new Url('https://example.test/cb');

        self::assertSame('https://example.test/cb', $url->getUrl());
        self::assertSame(UrlKind::Callback, $url->getKind());
        self::assertSame('callback', json_decode(json_encode($url), true)['kind']);
    }

    #[Test]
    public function url_accepts_an_explicit_kind(): void
    {
        $url = new Url('https://example.test/ok', UrlKind::ThreeDSecureSuccess);

        self::assertSame(UrlKind::ThreeDSecureSuccess, $url->getKind());
    }

    #[Test]
    public function antifraud_defaults_to_unsuccessful(): void
    {
        self::assertFalse((new Antifraud())->isSuccess());
    }

    #[Test]
    public function antifraud_setters_are_fluent(): void
    {
        $antifraud = (new Antifraud())
            ->setSuccess(true)
            ->setScore(42)
            ->setRiskLevel('low')
            ->setRecommendation('accept');

        self::assertTrue($antifraud->isSuccess());
        self::assertSame(42, $antifraud->getScore());
        self::assertSame('low', $antifraud->getRiskLevel());
        self::assertSame('accept', $antifraud->getRecommendation());
    }
}
