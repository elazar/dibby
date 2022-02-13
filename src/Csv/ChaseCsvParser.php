<?php

namespace Elazar\Dibby\Csv;

use DateTimeImmutable;

class ChaseCsvParser implements CsvParser
{
    /**
     * @return CsvTransaction[]
     */
    public function parseString(string $csv): array
    {
        /**
         * @var string[] $lines
         */
        $lines = preg_split('/[\r\n]+/', $csv);

        // Remove header line
        array_shift($lines);

        // Remove trailing empty string
        array_pop($lines);

        $rows = array_map('str_getcsv', $lines);

        $transactions = array_map(
            function (array $row): CsvTransaction {
                /** @var DateTimeImmutable $date */
                $date = DateTimeImmutable::createFromFormat('m/d/Y', $row[1]) ?: new DateTimeImmutable();
                $amount = (float) $row[3];
                $description = preg_replace('/\s{2,}/', ' ', $row[2]);
                return new CsvTransaction(
                    date: $date,
                    amount: $amount,
                    description: $description,
                );
            },
            $rows
        );

        return $transactions;
    }

    public function isCompatible(string $csv): bool
    {
        return strpos($csv, 'Details,Posting Date,Description,Amount,Type,Balance,Check or Slip #') === 0;
    }
}
