<?php

declare(strict_types=1);

namespace Rede;

class Antifraud
{
    use CreateTrait;

    private ?string $recommendation = null;

    private ?string $riskLevel = null;

    private ?int $score = null;

    private bool $success = false;

    public function getRecommendation(): ?string
    {
        return $this->recommendation;
    }

    public function setRecommendation(string $recommendation): static
    {
        $this->recommendation = $recommendation;

        return $this;
    }

    public function getRiskLevel(): ?string
    {
        return $this->riskLevel;
    }

    public function setRiskLevel(string $riskLevel): static
    {
        $this->riskLevel = $riskLevel;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success): static
    {
        $this->success = $success;

        return $this;
    }
}
