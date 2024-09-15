<?php

use Elazar\Dibby\Importer\{
    ChaseCreditImporter,
    ImportedTransaction,
};

beforeEach(function () {
    $this->importer = new ChaseCreditImporter;
    $this->data = file_get_contents(__DIR__ . '/_files/ChaseActivityMar02.csv');
});

it('parses a Chase Credit export', function () {
    $transactions = $this->importer->import($this->data);

    expect($transactions)
        ->toBeArray()
        ->toHaveCount(21)
        ->each->toBeInstanceOf(ImportedTransaction::class);

    $transaction = $transactions[0];
    expect($transaction->getDate()->format('m/d/Y'))->toBe('03/01/2024');
    expect($transaction->getDescription())->toBe('Amazon.com*RZ16J5E52');
    expect($transaction->getAmount())->toBe(-16.45);
});

it('detects compatibility with a Chase export', function () {
    $compatible = $this->importer->isCompatible($this->data);
    expect($compatible)->toBe(true);
});
