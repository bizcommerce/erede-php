<?php

declare(strict_types=1);

namespace Rede;

interface RedeUnserializable
{
    public function jsonUnserialize(string $serialized): mixed;
}
