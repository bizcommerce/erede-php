<?php

declare(strict_types=1);

namespace Rede\Enum;

/**
 * The kind of place an address refers to (apartment, house, ...).
 */
enum ResidenceType: int
{
    case Apartment = 1;
    case House = 2;
    case Commercial = 3;
    case Other = 4;
}
