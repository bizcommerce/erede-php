<?php

declare(strict_types=1);

namespace Rede\Service;

use Psr\Log\LoggerInterface;
use Rede\Http\HttpClient;
use Rede\NotificationUrl;
use Rede\Store;

/**
 * Registers a Pix status-notification webhook URL via POST /v2/transactions/notification-URL.
 *
 * WARNING: per the e.Rede manual the Pix webhook URL is normally registered by Rede's
 * call center (CNPJ + PV + email + URL), not through the API. This endpoint is NOT
 * verified against a live environment (sandbox returns HTTP 403 for the standard
 * e-commerce token scope). Use only if Rede has confirmed API registration for your PV.
 */
class NotificationUrlService extends AbstractService
{
    public function __construct(
        Store $store,
        private readonly NotificationUrl $notificationUrl,
        ?HttpClient $http = null,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($store, $http, $logger);
    }

    public function execute(): bool
    {
        return $this->sendRequest(json_encode($this->notificationUrl->jsonSerialize()), self::POST);
    }

    protected function getService(): string
    {
        return 'transactions/notification-URL';
    }

    protected function parseResponse(string $response, int $statusCode): bool
    {
        $data = json_decode($response);

        return $statusCode < 400 && is_object($data) && ($data->returnCode ?? null) === '00';
    }
}
