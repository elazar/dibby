<?php

namespace Elazar\Dibby\Importer;

interface Importer
{
    /**
     * @return ImportedTransaction[]
     */
    public function import(string $data): array;

    public function isCompatible(string $data): bool;
}
