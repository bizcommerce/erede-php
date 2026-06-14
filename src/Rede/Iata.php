<?php

declare(strict_types=1);

namespace Rede;

use ArrayIterator;

class Iata implements RedeSerializable
{
    use SerializeTrait;

    private ?string $code = null;

    private ?string $departureTax = null;

    /**
     * @var array<int, Flight>|null
     */
    private ?array $flight = null;

    public function addFlight(Flight $flight): static
    {
        $this->flight[] = $flight;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getDepartureTax(): ?string
    {
        return $this->departureTax;
    }

    public function setDepartureTax(string $departureTax): static
    {
        $this->departureTax = $departureTax;

        return $this;
    }

    /**
     * @return ArrayIterator<int, Flight>
     */
    public function getFlightIterator(): ArrayIterator
    {
        return new ArrayIterator($this->flight ?? []);
    }

    public function setFlight(Flight $flight): static
    {
        $this->flight = [];

        return $this->addFlight($flight);
    }
}
