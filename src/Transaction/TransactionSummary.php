<?php

namespace Elazar\Dibby\Transaction;

class TransactionSummary
{
    /**
     * @param TransactionSummaryRow[] $rows
     */
    public function __construct(
        private array $rows,
        private int $count,
        private float $total,
    ) { }

    /**
     * @return TransactionSummaryRow[]
     */
    public function getRows(): array
    {
        return $this->rows;
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
