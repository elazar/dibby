<?php

namespace Elazar\Dibby\Transaction;

use DateTimeImmutable;
use Elazar\Dibby\{
    Exception,
    Immutable,
};

class TransactionCriteria implements \JsonSerializable
{
    use Immutable;

    public function __construct(
        private ?string $description = null,
        private ?float $amountStart = null,
        private ?float $amountEnd = null,
        private ?string $debitAccountId = null,
        private ?string $creditAccountId = null,
        private ?DateTimeImmutable $dateStart = null,
        private ?DateTimeImmutable $dateEnd = null,
    ) { } 

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function withDescription(string $description): static
    {
        return $this->with('description', $description);
    }

    public function getAmountStart(): ?float
    {
        return $this->amountStart;
    }

    public function withAmountStart(float $amountStart): static
    {
        return $this->with('amountStart', $amountStart);
    }

    public function getAmountEnd(): ?float
    {
        return $this->amountEnd;
    }

    public function withAmountEnd(float $amountEnd): static
    {
        return $this->with('amountEnd', $amountEnd);
    }

    public function getDebitAccountId(): ?string
    {
        return $this->debitAccountId;
    }

    public function withDebitAccountId(string $debitAccountId): static
    {
        return $this->with('debitAccountId', $debitAccountId);
    }

    public function getCreditAccountId(): ?string
    {
        return $this->creditAccountId;
    }

    public function withCreditAccountId(string $creditAccountId): static
    {
        return $this->with('creditAccountId', $creditAccountId);
    }

    public function getDateStart(): ?DateTimeImmutable
    {
        return $this->dateStart;
    }

    public function withDateStart(DateTimeImmutable $dateStart): static
    {
        return $this->with('dateStart', $dateStart);
    }

    public function getDateEnd(): ?DateTimeImmutable
    {
        return $this->dateEnd;
    }

    public function withDateEnd(DateTimeImmutable $dateEnd): static
    {
        return $this->with('dateEnd', $dateEnd);
    }

    public function toArray(): array
    {
        return array_filter([
            'description' => $this->description,
            'amount_start' => $this->amountStart,
            'amount_end' => $this->amountEnd,
            'debit_account_id' => $this->debitAccountId,
            'credit_account_id' => $this->creditAccountId,
            'date_start' => $this->dateStart?->format(DateTimeImmutable::RFC7231),
            'date_end' => $this->dateEnd?->format(DateTimeImmutable::RFC7231),
        ]);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function isEmpty(): bool
    {
        return count($this->toArray()) === 0;
    }

    public static function fromArray(array $data): self
    {
        $criteria = new self;
        if (isset($data['description'])) {
            $criteria = $criteria->withDescription($data['description']);
        }
        if (isset($data['amount_start'])) {
            $criteria = $criteria->withAmountStart((float) $data['amount_start']);
        }
        if (isset($data['amount_end'])) {
            $criteria = $criteria->withAmountEnd((float) $data['amount_end']);
        }
        if (isset($data['debit_account_id'])) {
            $criteria = $criteria->withDebitAccountId($data['debit_account_id']);
        }
        if (isset($data['credit_account_id'])) {
            $criteria = $criteria->withCreditAccountId($data['credit_account_id']);
        }
        if (isset($data['date_start'])) {
            try {
                $criteria = $criteria->withDateStart(new DateTimeImmutable($data['date_start']));
            } catch (\Exception $error) {
                throw Exception::invalidInput('Start Date is invalid: ' . $error->getMessage());
            }
        }
        if (isset($data['date_end'])) {
            try {
                $criteria = $criteria->withDateEnd(new DateTimeImmutable($data['date_end']));
            } catch (\Exception $error) {
                throw Exception::invalidInput('End Date is invalid: ' . $error->getMessage());
            }
        }
        return $criteria;
    }
}
