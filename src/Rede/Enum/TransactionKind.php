<?php

declare(strict_types=1);

namespace Rede\Enum;

/**
 * The kind of transaction being processed. Drives capture rules (debit is always
 * captured) and selects the payment rail (pix).
 */
enum TransactionKind: string
{
    case Credit = 'credit';
    case Debit = 'debit';
    case Pix = 'pix';
}
