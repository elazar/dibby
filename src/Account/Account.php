<?php

namespace Elazar\Dibby\Account;

use Elazar\Dibby\Immutable;

class Account
{
    use Immutable;

    public function __construct(
        private string $name,
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

    public function getId(): ?string
    {
        return $this->id;
    }

    public function withId(string $id): static
    {
        return $this->with('id', $id);
    }
    /**
     * @param array<string, string> $data
     */
    public static function fromArray(array $data): self
    {
        $account = new self((string) $data['name']);

        if (isset($data['id'])) {
            $account = $account->withId((string) $data['id']);
        }

        return $account;
    }

    /**
     * @return array{id?: non-empty-string, name?: non-empty-string}
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
        ]);
    }
}
