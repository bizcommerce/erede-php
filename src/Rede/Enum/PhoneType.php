<?php

declare(strict_types=1);

namespace Rede\Enum;

/**
 * Type of a phone number (used by consumers and passengers).
 */
enum PhoneType: int
{
    case Cellphone = 1;
    case Home = 2;
    case Work = 3;
    case Other = 4;
}
