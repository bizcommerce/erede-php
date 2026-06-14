<?php

declare(strict_types=1);

namespace Rede\Service;

use Psr\Log\LoggerInterface;
use Rede\Http\HttpClient;
use Rede\NotificationUrl;
use Rede\Store;

/**
 * Registers a Pix status-notification webhook URL.
 * POST /v1/transactions/notification-URL
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
