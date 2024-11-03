<?php

namespace Elazar\Dibby\Importer;

use DateTimeImmutable;

class CapitalOneImporter implements Importer
{
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
                $date = DateTimeImmutable::createFromFormat('Y-m-d', $row[1]) ?: null;
                $date = $date->setTime(0, 0, 0, 0);
                $amount = strlen($row[6]) === 0 ? -1 * ((float) $row[5]) : (float) $row[6];
                $description = preg_replace('/\s{2,}/', ' ', $row[3]);
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
        return strpos($data, 'Transaction Date,Posted Date,Card No.,Description,Category,Debit,Credit') === 0;
    }
}

