<?php

namespace Elazar\Dibby\Importer;

use Elazar\Dibby\Transaction\TransactionInterface;

use DateTimeImmutable;

class ImportedTransaction implements TransactionInterface
{
    public function __construct(
        private float $amount,
        private ?DateTimeImmutable $date,
        private string $description,
    ) { }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getDate(): ?DateTimeImmutable
    {
        return $this->date;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function isPending(): bool
    {
        return $this->date === null;
    }
}
