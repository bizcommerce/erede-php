<?php

declare(strict_types=1);

namespace Rede;

/**
 * Card-brand details returned with a transaction when the
 * "Transaction-Response: brand-return-opened" header is sent.
 */
class Brand
{
    use CreateTrait;

    private ?string $name = null;

    private ?string $returnCode = null;

    private ?string $returnMessage = null;

    private ?string $merchantAdviceCode = null;

    private ?string $authorizationCode = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getReturnCode(): ?string
    {
        return $this->returnCode;
    }

    public function setReturnCode(?string $returnCode): static
    {
        $this->returnCode = $returnCode;

        return $this;
    }

    public function getReturnMessage(): ?string
    {
        return $this->returnMessage;
    }

    public function setReturnMessage(?string $returnMessage): static
    {
        $this->returnMessage = $returnMessage;

        return $this;
    }

    public function getMerchantAdviceCode(): ?string
    {
        return $this->merchantAdviceCode;
    }

    public function getAuthorizationCode(): ?string
    {
        return $this->authorizationCode;
    }
}
