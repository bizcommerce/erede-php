<?php

declare(strict_types=1);

namespace Rede;

use JsonSerializable;

/**
 * Marks objects that serialize themselves into the JSON payload sent to Rede.
 *
 * Extending the native {@see JsonSerializable} is essential: it is what makes
 * json_encode() call jsonSerialize() on these objects (and on nested ones such
 * as Cart, ThreeDSecure, Url and Iata). Without it, json_encode() would only
 * see their (private) properties and emit "{}", silently dropping the data.
 */
interface RedeSerializable extends JsonSerializable
{
}
