<?php

namespace Elazar\Dibby\Account;

use Elazar\Dibby\Immutable;

class Account
{
    use Immutable;

    public function __construct(
        private string $name,
        private ?float $creditLimit = null,
        private ?string $id = null,
    ) { }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function withName(string $name): static
    {
        return $this->with('name', $name);
    }

    public function getCreditLimit(): ?float
    {
        return $this->creditLimit;
    }

    public function withCreditLimit(float $creditLimit): static
    {
        return $this->with('creditLimit', $creditLimit);
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function withId(string $id): static
    {
        return $this->with('id', $id);
    }

    public static function fromArray(array $data): self
    {
        $account = new self((string) $data['name']);

        if (isset($data['credit_limit'])) {
            $account = $account->withCreditLimit((float) $data['credit_limit']);
        }

        if (isset($data['id'])) {
            $account = $account->withId((string) $data['id']);
        }

        return $account;
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'credit_limit' => $this->creditLimit,
        ]);
    }
}
