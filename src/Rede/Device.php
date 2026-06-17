<?php

declare(strict_types=1);

namespace Rede;

/**
 * Browser/device fingerprint sent with a 3DS 2.0 authentication.
 *
 * Property names intentionally match the camelCase JSON keys required by the
 * eRede 3DS 2.0 (MPI Rede) API — see the request parameter table at
 * https://developer.userede.com.br/e-rede (threeDSecure/device/*). The
 * SerializeTrait emits property names verbatim, so the casing here IS the wire
 * casing. The PascalCase forms (ColorDepth, ScreenHeight, …) only ever appear
 * in the API's error-message labels, never as request keys.
 */
class Device implements RedeSerializable
{
    use CreateTrait;
    use SerializeTrait;

    public function __construct(
        private ?string $colorDepth = null,
        private ?string $deviceType3ds = null,
        private ?bool $javaEnabled = null,
        private string $language = 'BR',
        private ?int $screenHeight = null,
        private ?int $screenWidth = null,
        private ?int $timeZoneOffset = 3,
    ) {
    }

    public function getColorDepth(): ?string
    {
        return $this->colorDepth;
    }

    public function setColorDepth(string $colorDepth): static
    {
        $this->colorDepth = $colorDepth;

        return $this;
    }

    public function getDeviceType3ds(): ?string
    {
        return $this->deviceType3ds;
    }

    public function setDeviceType3ds(string $deviceType3ds): static
    {
        $this->deviceType3ds = $deviceType3ds;

        return $this;
    }

    public function getJavaEnabled(): ?bool
    {
        return $this->javaEnabled;
    }

    public function setJavaEnabled(bool $javaEnabled = true): static
    {
        $this->javaEnabled = $javaEnabled;

        return $this;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): static
    {
        $this->language = $language;

        return $this;
    }

    public function getScreenHeight(): ?int
    {
        return $this->screenHeight;
    }

    public function setScreenHeight(int $screenHeight): static
    {
        $this->screenHeight = $screenHeight;

        return $this;
    }

    public function getScreenWidth(): ?int
    {
        return $this->screenWidth;
    }

    public function setScreenWidth(int $screenWidth): static
    {
        $this->screenWidth = $screenWidth;

        return $this;
    }

    public function getTimeZoneOffset(): ?int
    {
        return $this->timeZoneOffset;
    }

    public function setTimeZoneOffset(int $timeZoneOffset): static
    {
        $this->timeZoneOffset = $timeZoneOffset;

        return $this;
    }
}
