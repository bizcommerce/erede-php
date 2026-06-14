<?php

declare(strict_types=1);

namespace Rede;

/**
 * Browser/device fingerprint sent with a 3DS 2.0 authentication.
 */
class Device implements RedeSerializable
{
    use CreateTrait;
    use SerializeTrait;

    public function __construct(
        private ?string $ColorDepth = null,
        private ?string $DeviceType3ds = null,
        private ?bool $JavaEnabled = null,
        private string $Language = 'BR',
        private ?int $ScreenHeight = null,
        private ?int $ScreenWidth = null,
        private ?int $TimeZoneOffset = 3,
    ) {
    }

    public function getColorDepth(): ?string
    {
        return $this->ColorDepth;
    }

    public function setColorDepth(string $colorDepth): static
    {
        $this->ColorDepth = $colorDepth;

        return $this;
    }

    public function getDeviceType3ds(): ?string
    {
        return $this->DeviceType3ds;
    }

    public function setDeviceType3ds(string $deviceType3ds): static
    {
        $this->DeviceType3ds = $deviceType3ds;

        return $this;
    }

    public function getJavaEnabled(): ?bool
    {
        return $this->JavaEnabled;
    }

    public function setJavaEnabled(bool $javaEnabled = true): static
    {
        $this->JavaEnabled = $javaEnabled;

        return $this;
    }

    public function getLanguage(): string
    {
        return $this->Language;
    }

    public function setLanguage(string $language): static
    {
        $this->Language = $language;

        return $this;
    }

    public function getScreenHeight(): ?int
    {
        return $this->ScreenHeight;
    }

    public function setScreenHeight(int $screenHeight): static
    {
        $this->ScreenHeight = $screenHeight;

        return $this;
    }

    public function getScreenWidth(): ?int
    {
        return $this->ScreenWidth;
    }

    public function setScreenWidth(int $screenWidth): static
    {
        $this->ScreenWidth = $screenWidth;

        return $this;
    }

    public function getTimeZoneOffset(): ?int
    {
        return $this->TimeZoneOffset;
    }

    public function setTimeZoneOffset(int $timeZoneOffset): static
    {
        $this->TimeZoneOffset = $timeZoneOffset;

        return $this;
    }
}
