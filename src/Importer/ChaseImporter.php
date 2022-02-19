<?php

namespace Elazar\Dibby\Importer;

use DateTimeImmutable;

class ChaseImporter implements Importer
{
    private DateTimeImmutable $now;

    public function __construct(
        DateTimeImmutable $now,
    ) {
        $this->now = $now->setTime(0, 0, 0, 0);
    }

    /**
     * @return ImportedTransaction[]
     */
    public function import(string $data): array
    {
        $lines = preg_split('/[\r\n]+/', $data);

        // Remove trailing empty string
        array_pop($lines);

        // Remove header line
        array_shift($lines);

        $rows = array_map('str_getcsv', $lines);

        $transactions = array_map(
            function (array $row): ImportedTransaction {
                $date = DateTimeImmutable::createFromFormat('m/d/Y', $row[1]) ?: null;
                $date = $date->setTime(0, 0, 0, 0);
                $amount = (float) $row[3];
                $description = preg_replace('/\s{2,}/', ' ', $row[2]);
                return new ImportedTransaction(
                    date: $date,
                    amount: $amount,
                    description: $description,
                );
            },
            $rows
        );

        // Filter transactions with a date of today, which may show up as
        // pending in the Chase web UI
        $transactions = array_filter(
            $transactions,
            function (ImportedTransaction $transaction): bool {
                $date = $transaction->getDate();
                if ($date === null) {
                    return false;
                }
                $diff = $date->diff($this->now);
                return $diff->y > 0 || $diff->m > 0 || $diff->d > 0;
            },
        );

        return $transactions;
    }

    public function isCompatible(string $data): bool
    {
        return strpos($data, 'Details,Posting Date,Description,Amount,Type,Balance,Check or Slip #') === 0;
    }
}
