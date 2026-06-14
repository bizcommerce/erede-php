<?php

declare(strict_types=1);

namespace Rede\Service;

use Psr\Log\LoggerInterface;
use Rede\CardTokenization;
use Rede\Http\HttpClient;
use Rede\Store;
use Rede\Tokenization;

/**
 * POST /token-service/oauth/v2/tokenization — requests a card token.
 */
class CardTokenizationRequestService extends AbstractTokenizationService
{
    public function __construct(
        Store $store,
        private readonly CardTokenization $card,
        ?HttpClient $http = null,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($store, $http, $logger);
    }

    public function execute(): Tokenization
    {
        return $this->sendRequest(json_encode($this->card->jsonSerialize()), self::POST);
    }
}
