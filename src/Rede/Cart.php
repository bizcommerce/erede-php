<?php

declare(strict_types=1);

namespace Rede;

use Rede\Enum\AddressTarget;

class Cart implements RedeSerializable
{
    use SerializeTrait;

    private ?Address $billing = null;

    private ?Consumer $consumer = null;

    private ?Environment $environment = null;

    private ?Iata $iata = null;

    /**
     * @var array<int, Item>|null
     */
    private ?array $items = null;

    /**
     * Rede supports more than one shipping address, so this slot is an array.
     *
     * @var array<int, Address>|null
     */
    private ?array $shipping = null;

    public function address(AddressTarget $target = AddressTarget::Both): Address
    {
        $address = new Address();

        if ($target->fillsBilling()) {
            $this->setBillingAddress($address);
        }

        if ($target->fillsShipping()) {
            $this->setShippingAddress($address);
        }

        return $address;
    }

    public function addItem(Item $item): static
    {
        $this->items[] = $item;

        return $this;
    }

    public function addShippingAddress(Address $shippingAddress): static
    {
        $this->shipping[] = $shippingAddress;

        return $this;
    }

    public function consumer(string $name, string $email, string $cpf): Consumer
    {
        $consumer = new Consumer($name, $email, $cpf);

        $this->setConsumer($consumer);

        return $consumer;
    }

    public function getBillingAddress(): ?Address
    {
        return $this->billing;
    }

    public function getConsumer(): ?Consumer
    {
        return $this->consumer;
    }

    public function getIata(): ?Iata
    {
        return $this->iata;
    }

    /**
     * @return array<int, Address>|null
     */
    public function getShippingAddress(): ?array
    {
        return $this->shipping;
    }

    /**
     * @return array<int, Address>|null
     */
    public function getShippingAddresses(): ?array
    {
        return $this->shipping;
    }

    public function setBillingAddress(Address $address): static
    {
        $this->billing = $address;

        return $this;
    }

    public function setConsumer(Consumer $consumer): static
    {
        $this->consumer = $consumer;

        return $this;
    }

    public function setIata(Flight $flight): static
    {
        $this->iata = new Iata();
        $this->iata->setFlight($flight);

        return $this;
    }

    public function setEnvironment(Environment $environment): static
    {
        $this->environment = $environment;

        return $this;
    }

    public function setShippingAddress(Address $address): static
    {
        $this->shipping = [$address];

        return $this;
    }
}
