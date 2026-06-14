<?php

declare(strict_types=1);

namespace Rede\Enum;

/**
 * Type of a cart item (used by the antifraud cart).
 */
enum ItemType: int
{
    case Physical = 1;
    case Digital = 2;
    case Service = 3;
    case Airline = 4;
}
