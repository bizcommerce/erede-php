<?php

declare(strict_types=1);

namespace Rede;

use Rede\Enum\Mpi;
use Rede\Enum\OnFailure;

class ThreeDSecure implements RedeSerializable
{
    use CreateTrait;
    use SerializeTrait;

    public const DATA_ONLY = 'DATA_ONLY';

    private ?string $cavv = null;

    private ?string $eci = null;

    private ?string $url = null;

    private ?string $xid = null;

    private int $threeDIndicator = 2;

    private ?string $directoryServerTransactionId = null;

    private string $userAgent;

    private bool $embedded;

    private ?string $returnCode = null;

    private ?string $returnMessage = null;

    private ?string $challengePreference = null;

    public function __construct(
        private ?Device $device = null,
        private OnFailure $onFailure = OnFailure::Decline,
        Mpi $mpi = Mpi::Rede,
        ?string $userAgent = null,
    ) {
        if ($userAgent === null) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? eRede::USER_AGENT;
        }

        $this->embedded = $mpi === Mpi::Rede;
        $this->userAgent = $userAgent;
    }

    public function getReturnCode(): ?string
    {
        return $this->returnCode;
    }

    public function getReturnMessage(): ?string
    {
        return $this->returnMessage;
    }

    public function getDevice(): ?Device
    {
        return $this->device;
    }

    public function setDevice(Device $device): static
    {
        $this->device = $device;

        return $this;
    }

    public function getThreeDIndicator(): int
    {
        return $this->threeDIndicator;
    }

    public function setThreeDIndicator(int $threeDIndicator): static
    {
        if ($threeDIndicator < 2) {
            // 3DS 1 was discontinued on 2022-10-15.
            trigger_error(
                'Support for 3DS 1 and all related technology is discontinued.',
                E_USER_DEPRECATED
            );
        }

        $this->threeDIndicator = $threeDIndicator;

        return $this;
    }

    public function getDirectoryServerTransactionId(): ?string
    {
        return $this->directoryServerTransactionId;
    }

    public function setDirectoryServerTransactionId(string $directoryServerTransactionId): static
    {
        $this->directoryServerTransactionId = $directoryServerTransactionId;

        return $this;
    }

    public function getCavv(): ?string
    {
        return $this->cavv;
    }

    public function setCavv(string $cavv): static
    {
        $this->cavv = $cavv;

        return $this;
    }

    public function getEci(): ?string
    {
        return $this->eci;
    }

    public function setEci(string $eci): static
    {
        $this->eci = $eci;

        return $this;
    }

    public function getOnFailure(): OnFailure
    {
        return $this->onFailure;
    }

    public function setOnFailure(OnFailure $onFailure): static
    {
        $this->onFailure = $onFailure;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    public function setUserAgent(string $userAgent): static
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    public function getXid(): ?string
    {
        return $this->xid;
    }

    public function setXid(string $xid): static
    {
        $this->xid = $xid;

        return $this;
    }

    public function isEmbedded(): bool
    {
        return $this->embedded;
    }

    public function setEmbedded(bool $embedded): static
    {
        $this->embedded = $embedded;

        return $this;
    }

    public function getChallengePreference(): ?string
    {
        return $this->challengePreference;
    }

    public function setChallengePreference(?string $challengePreference): static
    {
        $this->challengePreference = $challengePreference;

        return $this;
    }
}
