<?php

use Elazar\Dibby\Importer\{
    ChaseImporter,
    ImportedTransaction,
};

beforeEach(function () {
    $this->importer = new ChaseImporter(new DateTimeImmutable);
    $this->data = file_get_contents(__DIR__ . '/_files/Chase9989_Activity_20220212.CSV');
});

it('parses a Chase export', function () {
    $transactions = $this->importer->import($this->data);

    expect($transactions)
        ->toBeArray()
        ->toHaveCount(214)
        ->each->toBeInstanceOf(ImportedTransaction::class);

    $transaction = $transactions[0];
    expect($transaction->getDate()->format('m/d/Y'))->toBe('02/11/2022');
    expect($transaction->getDescription())->toBe('SQ *REVE COFFEE ROASTER Lafayette LA 02/11');
    expect($transaction->getAmount())->toBe(-18.06);
});

it('detects compatibility with a Chase export', function () {
    $compatible = $this->importer->isCompatible($this->data);
    expect($compatible)->toBe(true);
});
