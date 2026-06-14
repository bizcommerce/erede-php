<?php

declare(strict_types=1);

namespace Rede\Service;

use Psr\Log\LoggerInterface;
use Rede\Http\HttpClient;
use Rede\Store;
use Rede\TokenManagement;
use Rede\Tokenization;

/**
 * PUT /token-service/oauth/v2/tokenization/{tokenizationId} — delete, suspend or
 * reactivate a token.
 */
class TokenManagementService extends AbstractTokenizationService
{
    public function __construct(
        Store $store,
        string $tokenizationId,
        private readonly TokenManagement $management,
        ?HttpClient $http = null,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($store, $http, $logger);

        $this->path = $tokenizationId;
    }

    public function execute(): Tokenization
    {
        return $this->sendRequest(json_encode($this->management->jsonSerialize()), self::PUT);
    }
}
