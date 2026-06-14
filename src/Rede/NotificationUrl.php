<?php

declare(strict_types=1);

namespace Rede;

/**
 * Registers a webhook URL that receives Pix status notifications.
 */
class NotificationUrl implements RedeSerializable
{
    use SerializeTrait;

    /**
     * @var array{type: string, token: string}|null
     */
    private ?array $authorization = null;

    public function __construct(private string $URL)
    {
    }

    public function getUrl(): string
    {
        return $this->URL;
    }

    public function setUrl(string $url): static
    {
        $this->URL = $url;

        return $this;
    }

    /**
     * Attaches the credentials Rede should use when calling the webhook.
     *
     * @param string $type "bearer" or "basic"
     */
    public function withAuthorization(string $type, string $token): static
    {
        $this->authorization = ['type' => $type, 'token' => $token];

        return $this;
    }
}
