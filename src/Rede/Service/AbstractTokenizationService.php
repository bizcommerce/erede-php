<?php

declare(strict_types=1);

namespace Rede\Service;

use Rede\Tokenization;
use stdClass;

/**
 * Base for the token-service operations. They share the OAuth + 401-retry
 * machinery of {@see AbstractService} but target the token-service host
 * (/token-service/oauth/v2/tokenization) instead of the transaction endpoint.
 */
abstract class AbstractTokenizationService extends AbstractService
{
    protected string $path = '';

    protected function getServiceUrl(): string
    {
        return $this->store->getEnvironment()->getTokenizationEndpoint($this->path);
    }

    protected function getService(): string
    {
        return 'tokenization';
    }

    protected function parseResponse(string $response, int $statusCode): Tokenization
    {
        $data = json_decode($response);

        return Tokenization::fromResponse($data instanceof stdClass ? $data : new stdClass());
    }
}
