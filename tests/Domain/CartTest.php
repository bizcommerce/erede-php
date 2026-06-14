<?php

declare(strict_types=1);

namespace Rede\Tests\Domain;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rede\Address;
use Rede\Cart;
use Rede\Consumer;
use Rede\Enum\AddressTarget;
use Rede\Item;

#[CoversClass(Cart::class)]
final class CartTest extends TestCase
{
    #[Test]
    public function add_item_lazily_initialises_and_appends(): void
    {
        $cart = new Cart();
        $returned = $cart->addItem(new Item('sku-1', 1));
        $cart->addItem(new Item('sku-2', 2));

        self::assertSame($cart, $returned);
        self::assertCount(2, $cart->jsonSerialize()['items']);
    }

    #[Test]
    public function consumer_builds_attaches_and_returns_a_consumer(): void
    {
        $cart = new Cart();

        $consumer = $cart->consumer('Jane Roe', 'jane@example.test', '00000000000');

        self::assertInstanceOf(Consumer::class, $consumer);
        self::assertSame($consumer, $cart->getConsumer());
        self::assertSame('Jane Roe', $consumer->getName());
    }

    #[Test]
    public function set_billing_address_round_trips(): void
    {
        $cart = new Cart();
        $address = (new Address())->setCity('Rio');

        $cart->setBillingAddress($address);

        self::assertSame($address, $cart->getBillingAddress());
    }

    #[Test]
    public function shipping_address_is_stored_and_returned_as_an_array(): void
    {
        $cart = new Cart();
        $address = (new Address())->setCity('Rio');

        $cart->setShippingAddress($address);

        self::assertSame([$address], $cart->getShippingAddress());
        self::assertSame([$address], $cart->getShippingAddresses());
    }

    #[Test]
    public function address_billing_attaches_to_the_billing_slot_only(): void
    {
        $cart = new Cart();

        $address = $cart->address(AddressTarget::Billing);

        self::assertSame($address, $cart->getBillingAddress());
        self::assertNull($cart->getShippingAddress());
    }

    #[Test]
    public function address_shipping_attaches_to_the_shipping_slot_only(): void
    {
        $cart = new Cart();

        $address = $cart->address(AddressTarget::Shipping);

        self::assertNull($cart->getBillingAddress());
        self::assertSame([$address], $cart->getShippingAddress());
    }

    #[Test]
    public function address_both_attaches_to_both_slots(): void
    {
        $cart = new Cart();

        $address = $cart->address(AddressTarget::Both);

        self::assertSame($address, $cart->getBillingAddress());
        self::assertSame([$address], $cart->getShippingAddress());
    }
}
