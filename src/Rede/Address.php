<?php

declare(strict_types=1);

namespace Rede;

use Rede\Enum\ResidenceType;

class Address implements RedeSerializable
{
    use SerializeTrait;

    private ?string $address = null;

    private ?string $addresseeName = null;

    private ?string $city = null;

    private ?string $complement = null;

    private ?string $neighbourhood = null;

    private ?string $number = null;

    private ?string $state = null;

    private ?ResidenceType $type = null;

    private ?string $zipCode = null;

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getAddresseeName(): ?string
    {
        return $this->addresseeName;
    }

    public function setAddresseeName(string $addresseeName): static
    {
        $this->addresseeName = $addresseeName;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getComplement(): ?string
    {
        return $this->complement;
    }

    public function setComplement(string $complement): static
    {
        $this->complement = $complement;

        return $this;
    }

    public function getNeighbourhood(): ?string
    {
        return $this->neighbourhood;
    }

    public function setNeighbourhood(string $neighbourhood): static
    {
        $this->neighbourhood = $neighbourhood;

        return $this;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getType(): ?ResidenceType
    {
        return $this->type;
    }

    public function setType(ResidenceType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): static
    {
        $this->zipCode = $zipCode;

        return $this;
    }
}
