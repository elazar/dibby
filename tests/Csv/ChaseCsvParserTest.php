<?php

use Elazar\Dibby\Csv\{
    ChaseCsvParser,
    CsvTransaction,
};

beforeEach(function () {
    $this->parser = new ChaseCsvParser;
    $this->csv = file_get_contents(__DIR__ . '/_files/Chase9989_Activity_20220212.CSV');
});

it('parses a Chase CSV file', function () {
    $transactions = $this->parser->parseString($this->csv);

    expect($transactions)
        ->toBeArray()
        ->toHaveCount(214)
        ->each->toBeInstanceOf(CsvTransaction::class);

    $transaction = $transactions[0];
    expect($transaction->getDate()->format('m/d/Y'))->toBe('02/11/2022');
    expect($transaction->getDescription())->toBe('SQ *REVE COFFEE ROASTER Lafayette LA 02/11');
    expect($transaction->getAmount())->toBe(-18.06);
    expect($transaction->getBalance())->toBe(1088.10);
});

it('detects compatibility with a Chase CSV file', function () {
    $compatible = $this->parser->isCompatible($this->csv);
    expect($compatible)->toBe(true);
});
