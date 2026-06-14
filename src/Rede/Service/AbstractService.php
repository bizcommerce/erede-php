<?php

declare(strict_types=1);

namespace Rede\Service;

use Psr\Log\LoggerInterface;
use Rede\eRede;
use Rede\Http\HttpClient;
use Rede\Store;

abstract class AbstractService
{
    public const GET = 'GET';
    public const POST = 'POST';
    public const PUT = 'PUT';

    protected HttpClient $http;

    private ?AuthenticationService $authenticationService = null;

    public function __construct(
        protected readonly Store $store,
        ?HttpClient $http = null,
        private readonly ?LoggerInterface $logger = null,
    ) {
        $this->http = $http ?? new HttpClient();
    }

    protected function getAuthenticationService(): AuthenticationService
    {
        if ($this->authenticationService === null) {
            $this->authenticationService = new AuthenticationService($this->store, $this->http, $this->logger);
        }

        return $this->authenticationService;
    }

    /**
     * @throws \InvalidArgumentException|\RuntimeException|\Rede\Exception\RedeException
     */
    abstract public function execute(): mixed;

    /**
     * @throws \RuntimeException
     */
    protected function sendRequest(?string $body = null, string $method = self::GET, bool $isRetry = false): mixed
    {
        $token = $this->getAuthenticationService()->getToken();
        $url = $this->getServiceUrl();

        $headers = [
            'User-Agent' => eRede::USER_AGENT . ' ' . php_uname(),
            'Accept' => 'application/json',
            'Authorization' => $token->getAuthorizationHeader(),
            // Ask Rede to include card-brand details in the response.
            'Transaction-Response' => 'brand-return-opened',
        ];

        if ($body !== null) {
            $headers['Content-Type'] = 'application/json; charset=utf8';
        }

        if ($this->logger !== null) {
            $this->logger->debug($this->describeRequest($method, $url, $headers, $body));
        }

        $response = $this->http->send($method, $url, $headers, $body);
        $statusCode = $response->getStatusCode();
        $responseBody = (string) $response->getBody();

        if ($this->logger !== null) {
            $this->logger->debug(sprintf("Response Rede\nStatus Code: %s\n\n%s", $statusCode, $responseBody));
        }

        // The cached token may have expired server-side; refresh once and retry.
        if ($statusCode === 401 && !$isRetry) {
            $this->logger?->debug('Rede respondeu 401; renovando o token OAuth e repetindo a requisição.');

            $this->getAuthenticationService()->refreshToken();

            return $this->sendRequest($body, $method, true);
        }

        return $this->parseResponse($responseBody, $statusCode);
    }

    /**
     * The fully-qualified URL the request is sent to. Override to target a base
     * other than the default transaction endpoint (e.g. the token service).
     */
    protected function getServiceUrl(): string
    {
        return $this->store->getEnvironment()->getEndpoint($this->getService());
    }

    /**
     * Builds the (secret-redacted) debug line for an outgoing request.
     *
     * @param array<string, string> $headers
     */
    private function describeRequest(string $method, string $url, array $headers, ?string $body): string
    {
        $renderedHeaders = [];

        foreach ($headers as $name => $value) {
            $renderedHeaders[] = strcasecmp($name, 'Authorization') === 0
                ? 'Authorization: ***'
                : sprintf('%s: %s', $name, $value);
        }

        $safeBody = preg_replace('/"(cardnumber|securitycode)":"[^"]+"/i', '"\1":"***"', (string) $body);

        return trim(sprintf(
            "Request Rede\n%s %s\n%s\n\n%s",
            $method,
            $url,
            implode("\n", $renderedHeaders),
            $safeBody
        ));
    }

    /**
     * @return string Gets the service that will be used on the request
     */
    abstract protected function getService(): string;

    /**
     * Parses the HTTP response from Rede.
     */
    abstract protected function parseResponse(string $response, int $statusCode): mixed;
}
