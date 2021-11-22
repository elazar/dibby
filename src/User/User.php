<?php

namespace Elazar\Dibby\User;

use DateTimeImmutable;
use Elazar\Dibby\Immutable;

class User
{
    use Immutable;

    private ?string $id = null;

    private ?string $password = null;

    private ?string $passwordHash = null;

    private ?string $resetToken = null;

    private ?DateTimeImmutable $resetTokenExpiration = null;

    public function __construct(
        private string $email,
    ) { }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function withPassword(string $password): static
    {
        return $this->with('password', $password);
    }

    public function getPasswordHash(): ?string
    {
        return $this->passwordHash;
    }

    public function withPasswordHash(string $passwordHash): static
    {
        return $this->with('passwordHash', $passwordHash);
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function withId(string $id): static
    {
        return $this->with('id', $id);
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function withResetToken(?string $resetToken): static
    {
        return $this->with('resetToken', $resetToken);
    }

    public function getResetTokenExpiration(): ?DateTimeImmutable
    {
        return $this->resetTokenExpiration;
    }

    public function withResetTokenExpiration(?DateTimeImmutable $resetTokenExpiration): static
    {
        return $this->with('resetTokenExpiration', $resetTokenExpiration);
    }

    /**
     * @param array<string, string> $data
     */
    public static function fromArray(array $data): self
    {
        $user = new self((string) $data['email'] ?: '');

        if (isset($data['id'])) {
            $user = $user->withId((string) $data['id']);
        }

        if (isset($data['password'])) {
            $user = $user->withPassword($data['password']);
        }

        if (isset($data['password_hash'])) {
            $user = $user->withPasswordHash($data['password_hash']);
        }

        if (isset($data['reset_token'])) {
            $user = $user->withResetToken($data['reset_token']);
        }

        if (isset($data['reset_token_expiration'])) {
            $user = $user->withResetTokenExpiration(
                new DateTimeImmutable($data['reset_token_expiration']),
            );
        }

        return $user;
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'email' => $this->email,
            'password_hash' => $this->passwordHash,
            'reset_token' => $this->resetToken,
            'reset_token_expiration' => $this->resetTokenExpiration?->format(DateTimeImmutable::RFC7231),
        ]);
    }
}
