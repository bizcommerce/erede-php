<?php

declare(strict_types=1);

namespace Rede;

use Rede\Enum\ItemType;

class Item implements RedeSerializable
{
    use SerializeTrait;

    private ?int $amount = null;

    private ?string $description = null;

    private ?int $discount = null;

    private ?int $freight = null;

    private ?string $shippingType = null;

    public function __construct(
        private string $id,
        private int $quantity,
        private ItemType $type = ItemType::Physical,
    ) {
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDiscount(): ?int
    {
        return $this->discount;
    }

    public function setDiscount(int $discount): static
    {
        $this->discount = $discount;

        return $this;
    }

    public function getFreight(): ?int
    {
        return $this->freight;
    }

    public function setFreight(int $freight): static
    {
        $this->freight = $freight;

        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getShippingType(): ?string
    {
        return $this->shippingType;
    }

    public function setShippingType(string $shippingType): static
    {
        $this->shippingType = $shippingType;

        return $this;
    }

    public function getType(): ItemType
    {
        return $this->type;
    }

    public function setType(ItemType $type): static
    {
        $this->type = $type;

        return $this;
    }
}
