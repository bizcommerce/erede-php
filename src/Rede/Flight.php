<?php

declare(strict_types=1);

namespace Rede;

class Flight implements RedeSerializable
{
    use SerializeTrait;

    /**
     * @var array<int, Passenger>|null
     */
    private ?array $passenger = null;

    public function __construct(
        private string $number,
        private string $from,
        private string $to,
        private string $date,
    ) {
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function setDate(string $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function setFrom(string $from): static
    {
        $this->from = $from;

        return $this;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function setNumber(string $number): static
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @return array<int, Passenger>|null
     */
    public function getPassenger(): ?array
    {
        return $this->passenger;
    }

    public function setPassenger(Passenger $passenger): static
    {
        $this->passenger = [];

        return $this->addPassenger($passenger);
    }

    public function addPassenger(Passenger $passenger): static
    {
        $this->passenger[] = $passenger;

        return $this;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function setTo(string $to): static
    {
        $this->to = $to;

        return $this;
    }
}
