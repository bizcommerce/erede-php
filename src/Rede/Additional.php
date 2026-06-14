<?php

declare(strict_types=1);

namespace Rede;

/**
 * Additional routing data (gateway/module) sent with a transaction.
 */
class Additional implements RedeSerializable
{
    use SerializeTrait;

    private ?int $gateway = null;

    private ?int $module = null;

    public function getGateway(): ?int
    {
        return $this->gateway;
    }

    public function setGateway(int $gateway): static
    {
        $this->gateway = $gateway;

        return $this;
    }

    public function getModule(): ?int
    {
        return $this->module;
    }

    public function setModule(int $module): static
    {
        $this->module = $module;

        return $this;
    }
}
