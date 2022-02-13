<?php

namespace Elazar\Dibby\Csv;

interface CsvParser
{
    /**
     * @return CsvTransaction[]
     */
    public function parseString(string $csv): array;

    public function isCompatible(string $csv): bool;
}
