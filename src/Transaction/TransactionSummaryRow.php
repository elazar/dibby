<?php

namespace Elazar\Dibby\Transaction;

use DateTimeImmutable;

class TransactionSummaryRow
{
    public function __construct(
        private ?DateTimeImmutable $date,
        private int $count,
        private float $total,
    ) { }

    public function getDate(): ?DateTimeImmutable
    {
        return $this->date;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getTotal(): float
    {
        return $this->total;
    }
}
