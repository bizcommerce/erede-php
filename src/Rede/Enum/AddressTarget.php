<?php

declare(strict_types=1);

namespace Rede\Enum;

/**
 * Which slot(s) an address built through {@see \Rede\Cart::address()} fills.
 *
 * Replaces the former bit-flag integer constants (BILLING=1, SHIPPING=2,
 * BOTH=3): targeting is now decided by an explicit match, so it can never fall
 * victim to the operator-precedence bug the bitwise test had.
 */
enum AddressTarget
{
    case Billing;
    case Shipping;
    case Both;

    public function fillsBilling(): bool
    {
        return $this === self::Billing || $this === self::Both;
    }

    public function fillsShipping(): bool
    {
        return $this === self::Shipping || $this === self::Both;
    }
}
