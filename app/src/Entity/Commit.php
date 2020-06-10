<?php

declare(strict_types=1);

namespace App\Entity;

use Ramsey\Uuid\Nonstandard\Uuid;
use Ramsey\Uuid\UuidInterface;

class Commit
{
    private UuidInterface $id;
    private ?string $sha;
    private ?string $message;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->sha = null;
        $this->message = null;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getSha(): ?string
    {
        return $this->sha;
    }

    public function setSha(?string $sha): void
    {
        $this->sha = $sha;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }
}
