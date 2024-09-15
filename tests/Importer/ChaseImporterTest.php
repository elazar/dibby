<?php

use Elazar\Dibby\Importer\{
    ChaseImporter,
    ImportedTransaction,
};

function get_import_file($name): string
{
    return file_get_contents(__DIR__ . "/_files/$name");
}

beforeEach(function () {
    $this->importer = new ChaseImporter(
        new DateTimeImmutable('2022-02-18'),
    );
});

it('parses a Chase export', function () {
    $data = get_import_file('Chase9989_Activity_20220212.CSV');
    $transactions = $this->importer->import($data);

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
    $data = get_import_file('Chase9989_Activity_20220212.CSV');
    $compatible = $this->importer->isCompatible($data);
    expect($compatible)->toBe(true);
});

it('excludes transactions dated today', function () {
    $data = get_import_file('Chase9989_Activity_20220218.CSV');
    $transactions = $this->importer->import($data);

    expect($transactions)
        ->toBeArray()
        ->toHaveCount(247)
        ->each->toBeInstanceOf(ImportedTransaction::class);
});
