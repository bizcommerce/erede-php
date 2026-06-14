<?php

declare(strict_types=1);

namespace Rede;

class Passenger implements RedeSerializable
{
    use SerializeTrait;

    private ?Phone $phone = null;

    public function __construct(
        private string $name,
        private string $email,
        private string $ticket,
    ) {
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPhone(): ?Phone
    {
        return $this->phone;
    }

    public function setPhone(Phone $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getTicket(): string
    {
        return $this->ticket;
    }

    public function setTicket(string $ticket): static
    {
        $this->ticket = $ticket;

        return $this;
    }
}
