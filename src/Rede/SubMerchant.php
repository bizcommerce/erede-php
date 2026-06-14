<?php

declare(strict_types=1);

namespace Rede;

/**
 * Sub-merchant data for sub-acquirers / marketplaces (Circular 3978).
 */
class SubMerchant implements RedeSerializable
{
    use SerializeTrait;

    public function __construct(
        private string $mcc,
        private string $city,
        private string $country,
    ) {
    }

    public function getMcc(): string
    {
        return $this->mcc;
    }

    public function setMcc(string $mcc): static
    {
        $this->mcc = $mcc;

        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }
}
