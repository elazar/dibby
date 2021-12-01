<?php

namespace Elazar\Dibby\Transaction;

use DateTimeImmutable;
use Elazar\Dibby\{
    Account\Account,
    Immutable,
};

class Transaction
{
    use Immutable;

    private ?string $id = null;

    private ?string $description = null;

    public function __construct(
        private float $amount,
        private Account $debitAccount,
        private Account $creditAccount,
        private DateTimeImmutable $date,
    ) { }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function withAmount(float $amount): static
    {
        return $this->with('amount', $amount);
    }

    public function getDebitAccount(): Account
    {
        return $this->debitAccount;
    }

    public function withDebitAccount(Account $debitAccount): static
    {
        return $this->with('debitAccount', $debitAccount);
    }

    public function getCreditAccount(): Account
    {
        return $this->creditAccount;
    }

    public function withCreditAccount(Account $creditAccount): static
    {
        return $this->with('creditAccount', $creditAccount);
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function withDate(DateTimeImmutable $date): static
    {
        return $this->with('date', $date);
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function withId(string $id): static
    {
        return $this->with('id', $id);
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function withDescription(?string $description): static
    {
        return $this->with('description', $description);
    }

    /**
     * @return array<string, float|string|bool>
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'amount' => $this->amount,
            'debit_account_id' => $this->debitAccount->getId(),
            'credit_account_id' => $this->creditAccount->getId(),
            'date' => $this->date->format(DateTimeImmutable::RFC7231),
            'description' => $this->description,
        ]);
    }
}
