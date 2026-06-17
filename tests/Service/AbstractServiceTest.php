<?php

declare(strict_types=1);

namespace Rede\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rede\Environment;
use Rede\Service\AbstractService;
use Rede\Store;
use Rede\Tests\Support\ArrayLogger;
use Rede\Tests\Support\MockTransport;
use Rede\Tests\Support\RecordingService;
use RuntimeException;

#[CoversClass(AbstractService::class)]
final class AbstractServiceTest extends TestCase
{
    private function service(MockTransport $transport, ?ArrayLogger $logger = null): RecordingService
    {
        return new RecordingService(new Store('PV-123', 'secret-key', Environment::sandbox()), $transport->http, $logger);
    }

    #[Test]
    public function it_sends_the_bearer_token_to_the_resolved_endpoint(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, '{"ok":true}');
        $service = $this->service($transport);

        $result = $service->send(null, AbstractService::GET);

        $request = $transport->lastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertStringEndsWith('/v2/transactions', (string) $request->getUri());
        self::assertSame('Bearer stub-token', $request->getHeaderLine('Authorization'));
        self::assertSame('application/json', $request->getHeaderLine('Accept'));
        self::assertSame('brand-return-opened', $request->getHeaderLine('Transaction-Response'));
        self::assertSame(['response' => '{"ok":true}', 'statusCode' => 200], $result);
    }

    #[Test]
    public function it_adds_a_json_content_type_when_a_body_is_present(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, '{}');
        $service = $this->service($transport);

        $service->send('{"amount":100}', AbstractService::POST);

        $request = $transport->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('{"amount":100}', (string) $request->getBody());
        self::assertStringContainsString('application/json', $request->getHeaderLine('Content-Type'));
    }

    #[Test]
    public function a_401_refreshes_the_token_and_retries_exactly_once(): void
    {
        $transport = new MockTransport();
        $transport->queue(401, '{"error":"expired"}');
        $transport->queue(200, '{"ok":true}');
        $service = $this->service($transport);

        $result = $service->send('{}', AbstractService::POST);

        self::assertCount(2, $transport->requests(), 'Exactly one retry is expected after a 401.');
        self::assertSame(1, $service->auth->refreshCount);
        self::assertSame(200, $result['statusCode']);
    }

    #[Test]
    public function it_does_not_retry_more_than_once_even_if_the_retry_also_fails(): void
    {
        $transport = new MockTransport();
        $transport->queue(401, '{"error":"expired"}');
        $transport->queue(401, '{"error":"still expired"}');
        $service = $this->service($transport);

        $result = $service->send('{}', AbstractService::POST);

        self::assertCount(2, $transport->requests(), 'A persistent 401 must not loop forever.');
        self::assertSame(1, $service->auth->refreshCount);
        self::assertSame(401, $result['statusCode']);
    }

    #[Test]
    public function it_propagates_a_transport_error(): void
    {
        $transport = new MockTransport();
        $transport->queueTransportError('Failed to connect');
        $service = $this->service($transport);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Rede HTTP transport error');

        $service->send('{}', AbstractService::POST);
    }

    #[Test]
    public function it_redacts_secrets_from_the_request_log(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, '{"ok":true}');
        $logger = new ArrayLogger();
        $service = $this->service($transport, $logger);

        $body = '{"cardnumber":"5448280000000007","securitycode":"123","amount":100}';
        $service->send($body, AbstractService::POST);

        $dump = $logger->dump();
        self::assertStringContainsString('Authorization: ***', $dump);
        self::assertStringNotContainsString('Bearer stub-token', $dump);
        self::assertStringContainsString('"cardnumber":"***"', $dump);
        self::assertStringContainsString('"securitycode":"***"', $dump);
        self::assertStringNotContainsString('5448280000000007', $dump);
    }
}
