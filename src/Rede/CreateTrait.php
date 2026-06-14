<?php

declare(strict_types=1);

namespace Rede;

use BackedEnum;
use DateTimeImmutable;
use DateTimeInterface;
use ReflectionNamedType;
use ReflectionObject;
use stdClass;

trait CreateTrait
{
    /**
     * Hydrates a new instance from a decoded gateway response, assigning only
     * properties the class declares. Scalar values are coerced to the declared
     * property type (builtin, backed enum, or date/time) so the SDK is tolerant
     * of the gateway sending, say, an "origin" as either 1 or "1".
     */
    public static function create(stdClass $data): static
    {
        $object = new static();
        $reflection = new ReflectionObject($object);

        foreach ((array) $data as $property => $value) {
            if (!$reflection->hasProperty($property)) {
                continue;
            }

            $type = $reflection->getProperty($property)->getType();

            if ($value !== null && $type instanceof ReflectionNamedType) {
                $value = self::coerce($type, $value);
            }

            $object->$property = $value;
        }

        return $object;
    }

    private static function coerce(ReflectionNamedType $type, mixed $value): mixed
    {
        $name = $type->getName();

        if (!$type->isBuiltin()) {
            if (is_subclass_of($name, BackedEnum::class)) {
                return $name::tryFrom($value);
            }

            if (is_a($name, DateTimeInterface::class, true) && is_string($value)) {
                return new DateTimeImmutable($value);
            }

            return $value;
        }

        return match ($name) {
            'int' => is_numeric($value) ? (int) $value : $value,
            'float' => is_numeric($value) ? (float) $value : $value,
            'string' => is_scalar($value) ? (string) $value : $value,
            'bool' => (bool) $value,
            default => $value,
        };
    }
}
