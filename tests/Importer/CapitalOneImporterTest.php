<?php

use Elazar\Dibby\Importer\{
    CapitalOneImporter,
    ImportedTransaction,
};

beforeEach(function () {
    $this->importer = new CapitalOneImporter;
    $this->data = file_get_contents(__DIR__ . '/_files/CapitalOneTransactionsNov3.csv');
});

it('parses a Capital One export', function () {
    $transactions = $this->importer->import($this->data);

    expect($transactions)
        ->toBeArray()
        ->toHaveCount(2)
        ->each->toBeInstanceOf(ImportedTransaction::class);

    $transaction = $transactions[0];
    expect($transaction->getDate()->format('Y-m-d'))->toBe('2024-11-01');
    expect($transaction->getDescription())->toBe('CAPITAL ONE AUTOPAY PYMT');
    expect($transaction->getAmount())->toBe(27.00);

    $transaction = $transactions[1];
    expect($transaction->getDate()->format('Y-m-d'))->toBe('2024-10-15');
    expect($transaction->getDescription())->toBe('INTEREST CHARGE:PURCHASES');
    expect($transaction->getAmount())->toBe(-18.85);
});

it('detects compatibility with a Capital One export', function () {
    $compatible = $this->importer->isCompatible($this->data);
    expect($compatible)->toBe(true);
});
