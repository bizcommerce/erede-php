<?php

declare(strict_types=1);

namespace Rede\Enum;

/**
 * What to do with a transaction when 3DS authentication fails.
 */
enum OnFailure: string
{
    case Continue = 'continue';
    case Decline = 'decline';
}
