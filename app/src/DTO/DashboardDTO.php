<?php

declare(strict_types=1);

namespace App\DTO;

class DashboardDTO
{
    private ?int $nbCommits;

    public function __construct()
    {
        $this->nbCommits = null;
    }

    public function getNbCommits(): ?int
    {
        return $this->nbCommits;
    }

    public function setNbCommits(?int $nbCommits): void
    {
        $this->nbCommits = $nbCommits;
    }
}
