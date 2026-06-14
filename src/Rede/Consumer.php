<?php

declare(strict_types=1);

namespace Rede;

use ArrayIterator;
use Rede\Enum\Gender;
use Rede\Enum\PhoneType;
use stdClass;

class Consumer implements RedeSerializable
{
    use SerializeTrait;

    /**
     * @var array<int, stdClass>|null
     */
    private ?array $documents = null;

    private ?Gender $gender = null;

    private ?Phone $phone = null;

    public function __construct(
        private string $name,
        private string $email,
        private string $cpf,
    ) {
    }

    public function addDocument(string $type, string $number): static
    {
        $document = new stdClass();
        $document->type = $type;
        $document->number = $number;

        $this->documents[] = $document;

        return $this;
    }

    public function getCpf(): string
    {
        return $this->cpf;
    }

    public function setCpf(string $cpf): static
    {
        $this->cpf = $cpf;

        return $this;
    }

    /**
     * @return ArrayIterator<int, stdClass>
     */
    public function getDocumentsIterator(): ArrayIterator
    {
        return new ArrayIterator($this->documents ?? []);
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

    public function getGender(): ?Gender
    {
        return $this->gender;
    }

    public function setGender(Gender $gender): static
    {
        $this->gender = $gender;

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

    public function phone(string $ddd, string $number, PhoneType $type = PhoneType::Cellphone): static
    {
        return $this->setPhone(new Phone($ddd, $number, $type));
    }

    public function setPhone(Phone $phone): static
    {
        $this->phone = $phone;

        return $this;
    }
}
