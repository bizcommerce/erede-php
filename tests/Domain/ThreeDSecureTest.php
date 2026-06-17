<?php

declare(strict_types=1);

namespace Rede\Tests\Domain;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rede\Device;
use Rede\eRede;
use Rede\Enum\Mpi;
use Rede\Enum\OnFailure;
use Rede\ThreeDSecure;

#[CoversClass(ThreeDSecure::class)]
#[CoversClass(Device::class)]
final class ThreeDSecureTest extends TestCase
{
    private ?string $originalUserAgent;

    protected function setUp(): void
    {
        $this->originalUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    }

    protected function tearDown(): void
    {
        if ($this->originalUserAgent === null) {
            unset($_SERVER['HTTP_USER_AGENT']);
        } else {
            $_SERVER['HTTP_USER_AGENT'] = $this->originalUserAgent;
        }
    }

    #[Test]
    public function it_defaults_the_user_agent_to_the_sdk_when_no_request_header_exists(): void
    {
        unset($_SERVER['HTTP_USER_AGENT']);

        self::assertSame(eRede::USER_AGENT, (new ThreeDSecure())->getUserAgent());
    }

    #[Test]
    public function it_adopts_the_incoming_request_user_agent_when_present(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Test Browser)';

        self::assertSame('Mozilla/5.0 (Test Browser)', (new ThreeDSecure())->getUserAgent());
    }

    #[Test]
    public function mpi_rede_is_embedded_by_default(): void
    {
        self::assertTrue((new ThreeDSecure())->isEmbedded());
    }

    #[Test]
    public function a_third_party_mpi_is_not_embedded(): void
    {
        self::assertFalse((new ThreeDSecure(mpi: Mpi::ThirdParty))->isEmbedded());
    }

    #[Test]
    public function setters_are_fluent_and_typed(): void
    {
        $threeDSecure = (new ThreeDSecure())
            ->setOnFailure(OnFailure::Continue)
            ->setCavv('cavv-value')
            ->setEci('05')
            ->setDirectoryServerTransactionId('ds-tx-1');

        self::assertSame(OnFailure::Continue, $threeDSecure->getOnFailure());
        self::assertSame('cavv-value', $threeDSecure->getCavv());
        self::assertSame('05', $threeDSecure->getEci());
        self::assertSame('ds-tx-1', $threeDSecure->getDirectoryServerTransactionId());
    }

    #[Test]
    public function the_three_d_indicator_defaults_to_2(): void
    {
        $threeDSecure = (new ThreeDSecure())->setThreeDIndicator(3);

        self::assertSame(3, $threeDSecure->getThreeDIndicator());
    }

    #[Test]
    public function it_carries_a_device_and_serializes_it(): void
    {
        unset($_SERVER['HTTP_USER_AGENT']);
        $device = (new Device())->setColorDepth('24')->setScreenHeight(900)->setScreenWidth(1440);
        $threeDSecure = (new ThreeDSecure($device))->setOnFailure(OnFailure::Decline);

        self::assertSame($device, $threeDSecure->getDevice());

        $serialized = json_decode(json_encode($threeDSecure), true);
        self::assertTrue($serialized['embedded']);
        self::assertSame('decline', $serialized['onFailure']);
        self::assertSame(eRede::USER_AGENT, $serialized['userAgent']);
        self::assertSame('24', $serialized['device']['colorDepth']);
        self::assertSame(900, $serialized['device']['screenHeight']);
    }

    #[Test]
    public function device_defaults_language_and_timezone(): void
    {
        $device = new Device();

        self::assertSame('BR', $device->getLanguage());
        self::assertSame(3, $device->getTimeZoneOffset());
    }
}
