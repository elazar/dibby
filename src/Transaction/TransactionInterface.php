<?php

namespace Elazar\Dibby\Transaction;

use DateTimeImmutable;

interface TransactionInterface
{
    public function getAmount(): float;

    public function getDate(): ?DateTimeImmutable;

    public function getDescription(): ?string;

    public function isPending(): bool;
}
