<?php

namespace Elazar\Dibby\Importer;

use Elazar\Dibby\Exception;

class CompositeImporter implements Importer
{
    /** @var Importer[] */
    private array $importers;

    public function __construct(
        Importer... $importers,
    ) {
        $this->importers = $importers;
    }

    /**
     * @return ImportedTransaction[]
     */
    public function import(string $data): array
    {
        foreach ($this->importers as $importer) {
            if ($importer->isCompatible($data)) {
                return $importer->import($data);
            }
        }
        throw Exception::noCompatibleImporter();
    }

    public function isCompatible(string $data): bool
    {
        foreach ($this->importers as $importer) {
            if ($importer->isCompatible($data)) {
                return true;
            }
        }
        return false;
    }
}
