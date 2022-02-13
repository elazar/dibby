<?php

namespace Elazar\Dibby\Csv;

use Elazar\Dibby\Transaction\TransactionInterface;

use DateTimeImmutable;

class CsvTransaction implements TransactionInterface
{
    public function __construct(
        private float $amount,
        private DateTimeImmutable $date,
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
}
