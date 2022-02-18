<?php

namespace Elazar\Dibby\Importer;

use DateTimeImmutable;

class ChaseImporter implements Importer
{
    public function __construct(
        private DateTimeImmutable $now,
    ) { }

    /**
     * @return ImportedTransaction[]
     */
    public function import(string $data): array
    {
        /**
         * @var string[] $lines
         */
        $lines = preg_split('/[\r\n]+/', $data);

        // Remove trailing empty string
        array_pop($lines);

        // Remove header line
        array_shift($lines);

        $rows = array_map('str_getcsv', $lines);

        $transactions = array_map(
            function (array $row): ImportedTransaction {
                $date = DateTimeImmutable::createFromFormat('m/d/Y', $row[1]) ?: null;
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

        return $transactions;
    }

    public function isCompatible(string $data): bool
    {
        return strpos($data, 'Details,Posting Date,Description,Amount,Type,Balance,Check or Slip #') === 0;
    }
}
