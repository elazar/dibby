<?php

namespace Elazar\Dibby\Importer;

use DateTimeImmutable;

class CitiImporter implements Importer
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
                $amount = (float) ($row[3] ?: $row[4]);
                $description = $row[2];
                return new ImportedTransaction(
                    date: $date,
                    amount: $amount,
                    description: $description,
                );
            },
            $rows
        );

        return $transactions;
    }

    public function isCompatible(string $data): bool
    {
        return strpos($data, 'Status,Date,Description,Debit,Credit') === 0;
    }
}
