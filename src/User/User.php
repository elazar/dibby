<?php

namespace Elazar\Dibby\User;

use Elazar\Dibby\Immutable;

class User
{
    use Immutable;

    public function __construct(
        private string $email,
        private string $passwordHash,
        private ?string $id = null,
    ) { }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function withId(string $id): static
    {
        return $this->with('id', $id);
    }
}
