<?php

declare(strict_types=1);

namespace Rede\Tests\Support;

use RuntimeException;

/**
 * Loads canned JSON request/response bodies from tests/fixtures.
 */
trait Fixtures
{
    protected static function fixture(string $name): string
    {
        $path = __DIR__ . '/../fixtures/' . $name;
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException(sprintf('Fixture "%s" could not be read.', $name));
        }

        return $contents;
    }
}
