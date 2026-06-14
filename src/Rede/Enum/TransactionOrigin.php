<?php

declare(strict_types=1);

namespace Rede\Enum;

/**
 * Identifies the origin/channel of a transaction.
 */
enum TransactionOrigin: int
{
    case Erede = 1;
    case VisaCheckout = 4;
    case Masterpass = 6;
}
