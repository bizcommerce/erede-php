<?php

declare(strict_types=1);

namespace Rede\Enum;

/**
 * The role of a URL attached to a transaction (callback / 3DS redirect targets).
 */
enum UrlKind: string
{
    case Callback = 'callback';
    case ThreeDSecureFailure = 'threeDSecureFailure';
    case ThreeDSecureSuccess = 'threeDSecureSuccess';
}
