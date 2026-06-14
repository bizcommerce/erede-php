<?php

declare(strict_types=1);

namespace Rede;

trait SerializeTrait
{
    /**
     * Serializes the object's non-null properties. Backed enums are emitted as
     * their scalar value by json_encode automatically.
     */
    public function jsonSerialize(): mixed
    {
        return array_filter(get_object_vars($this), static fn ($value): bool => $value !== null);
    }
}
