<?php

declare(strict_types=1);

namespace Rede;

use Rede\Enum\PhoneType;

class Phone implements RedeSerializable
{
    use SerializeTrait;

    public function __construct(
        private string $ddd,
        private string $number,
        private PhoneType $type = PhoneType::Cellphone,
    ) {
    }

    public function getDdd(): string
    {
        return $this->ddd;
    }

    public function setDdd(string $ddd): static
    {
        $this->ddd = $ddd;

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

    public function getType(): PhoneType
    {
        return $this->type;
    }

    public function setType(PhoneType $type): static
    {
        $this->type = $type;

        return $this;
    }
}
