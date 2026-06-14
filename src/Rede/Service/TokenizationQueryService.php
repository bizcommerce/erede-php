<?php

declare(strict_types=1);

namespace Rede\Service;

use Psr\Log\LoggerInterface;
use Rede\Http\HttpClient;
use Rede\Store;
use Rede\Tokenization;

/**
 * GET /token-service/oauth/v2/tokenization/{tokenizationId} — queries a token.
 */
class TokenizationQueryService extends AbstractTokenizationService
{
    public function __construct(
        Store $store,
        string $tokenizationId,
        ?HttpClient $http = null,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($store, $http, $logger);

        $this->path = $tokenizationId;
    }

    public function execute(): Tokenization
    {
        return $this->sendRequest(null, self::GET);
    }
}
