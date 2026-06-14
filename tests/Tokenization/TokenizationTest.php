<?php

declare(strict_types=1);

namespace Rede\Tests\Tokenization;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rede\CardTokenization;
use Rede\eRede;
use Rede\Environment;
use Rede\Service\CardTokenizationRequestService;
use Rede\Service\CreateTransactionService;
use Rede\Service\TokenManagementService;
use Rede\Service\TokenizationQueryService;
use Rede\Store;
use Rede\Tests\Support\Fixtures;
use Rede\Tests\Support\MockTransport;
use Rede\TokenManagement;
use Rede\Tokenization;
use Rede\TokenNotification;
use Rede\Transaction;

#[CoversClass(CardTokenization::class)]
#[CoversClass(Tokenization::class)]
#[CoversClass(TokenManagement::class)]
#[CoversClass(TokenNotification::class)]
#[CoversClass(CardTokenizationRequestService::class)]
#[CoversClass(TokenizationQueryService::class)]
#[CoversClass(TokenManagementService::class)]
final class TokenizationTest extends TestCase
{
    use Fixtures;

    private function store(): Store
    {
        $store = new Store('PV-123', 'secret', Environment::sandbox());
        MockTransport::seedToken($store);

        return $store;
    }

    private const TOKEN_SERVICE = 'https://rl7-sandbox-api.useredecloud.com.br/token-service/oauth/v2/tokenization';

    #[Test]
    public function it_requests_a_card_token(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, '{"returnCode":"00","returnMessage":"Success","tokenizationId":"tok-123"}');

        $card = new CardTokenization('buyer@example.test', '5448280000000007', '12', '2030', CardTokenization::COF_NOT_STORED);
        $result = (new CardTokenizationRequestService($this->store(), $card, $transport->http))->execute();

        $request = $transport->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame(self::TOKEN_SERVICE, (string) $request->getUri());
        $body = json_decode((string) $request->getBody(), true);
        self::assertSame('buyer@example.test', $body['email']);
        self::assertSame('5448280000000007', $body['cardNumber']);
        self::assertSame(0, $body['storageCard']);

        self::assertTrue($result->isSuccessful());
        self::assertSame('tok-123', $result->getTokenizationId());
    }

    #[Test]
    public function it_queries_a_token_and_flattens_brand_and_token_groups(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, self::fixture('tokenization_query.json'));

        $result = (new TokenizationQueryService($this->store(), '0c299dab', $transport->http))->execute();

        self::assertSame('GET', $transport->lastRequest()->getMethod());
        self::assertSame(self::TOKEN_SERVICE . '/0c299dab', (string) $transport->lastRequest()->getUri());
        self::assertSame('Active', $result->getTokenizationStatus());
        self::assertSame('Visa', $result->getBrandName());
        self::assertSame('BRTID123', $result->getBrandTid());
        self::assertSame('5448280000000007', $result->getTokenCode());
        self::assertSame('12/2030', $result->getTokenExpirationDate());
        self::assertNotNull($result->getLastModifiedDate());
    }

    #[Test]
    public function it_manages_a_token_with_a_put(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, '{"returnCode":"00","tokenizationId":"tok-123"}');

        $management = new TokenManagement(TokenManagement::STATUS_SUSPEND, TokenManagement::REASON_FRAUD_SUSPICION);
        (new TokenManagementService($this->store(), 'tok-123', $management, $transport->http))->execute();

        $request = $transport->lastRequest();
        self::assertSame('PUT', $request->getMethod());
        self::assertSame(self::TOKEN_SERVICE . '/tok-123', (string) $request->getUri());
        $body = json_decode((string) $request->getBody(), true);
        self::assertSame('suspend', $body['tokenizationStatus']);
        self::assertSame(2, $body['reason']);
    }

    #[Test]
    public function a_token_based_transaction_is_routed_to_the_v2_endpoint(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, self::fixture('transaction_authorized.json'));

        $transaction = (new Transaction(25.0, 'ref-1'))->cardToken('tok-123');
        (new CreateTransactionService($this->store(), $transaction, $transport->http))->execute();

        $request = $transport->lastRequest();
        self::assertStringEndsWith('/v2/transactions', (string) $request->getUri());
        $body = json_decode((string) $request->getBody(), true);
        self::assertSame('tok-123', $body['cardToken']);
        self::assertSame(2, $body['storageCard']);
    }

    #[Test]
    public function the_facade_exposes_the_tokenization_operations(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, self::fixture('tokenization_query.json'));
        $erede = new eRede($this->store(), $transport->http);

        $result = $erede->queryToken('0c299dab');

        self::assertSame('Visa', $result->getBrandName());
    }

    #[Test]
    public function it_parses_a_brand_tokenization_webhook(): void
    {
        $notification = TokenNotification::fromJson(
            '{"id":"123456","merchantId":"123415678","events":["PV.TOKENIZACAO-BANDEIRA"],"data":{"tokenizationId":"0c299dab"}}'
        );

        self::assertTrue($notification->hasEvent(TokenNotification::EVENT));
        self::assertSame('0c299dab', $notification->tokenizationId);
        self::assertSame('123415678', $notification->merchantId);
    }
}
