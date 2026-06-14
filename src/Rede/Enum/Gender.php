<?php

declare(strict_types=1);

namespace Rede\Enum;

/**
 * Consumer gender, as expected by the antifraud payload.
 */
enum Gender: string
{
    case Male = 'M';
    case Female = 'F';
}
