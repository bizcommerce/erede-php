<?php

declare(strict_types=1);

namespace Rede;

use Rede\Enum\UrlKind;

class Url implements RedeSerializable
{
    use SerializeTrait;

    public function __construct(
        private string $url,
        private UrlKind $kind = UrlKind::Callback,
    ) {
    }

    public function getKind(): UrlKind
    {
        return $this->kind;
    }

    public function setKind(UrlKind $kind): static
    {
        $this->kind = $kind;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }
}
