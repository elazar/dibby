<?php

use Elazar\Dibby\Transaction\Transaction;

$this->layout('layout');

$transactionsByDate = array_reduce(
    $transactions,
    function (array $byDate, Transaction $transaction) {
        $date = $transaction->getDate()->format('D, M j, Y');
        if (!isset($byDate[$date])) {
            $byDate[$date] = [];
        }
        $byDate[$date][] = $transaction;
        return $byDate;
    },
    [],
);

?>

<nav class="grid">
  <ol aria-label="breadcrumb" class="breadcrumb">
    <li><a href="<?= $this->route('get_menu') ?>">Menu</a></li>
    <li><a href="<?= $this->route('get_transactions') ?>" aria-current="page">List Transactions</a></li>
  </ol>
  <ul aria-label="subnavigation" class="subnavigation">
    <li><a href="<?= $this->route('add_transaction') ?>">Add Transaction</a></li>
  </ul>
</nav>

<h1 class="center">Transactions</h1>

<?php if (count($transactions) === 0): ?>
<div class="center">
  <p>Looks like you don't have any transactions yet.</p>
  <p>Want to <a href="<?= $this->route('add_transaction') ?>">add some</a>?</p>
</div>
<?php else: ?>
  <?php foreach ($transactionsByDate as $date => $transactionsForDate): ?>
    <section>
      <h2 class="center"><?= $date ?></h2>
      <?php foreach ($transactionsForDate as $transaction): ?>
      <article>
        <a class="transaction-edit-link" href="<?= $this->route('edit_transaction', ['transactionId' => $transaction->getId()]) ?>">Edit</a>
        <strong><?= number_format($transaction->getAmount(), 2) ?></strong><br>
        <a href="<?= $this->route('get_account', ['accountId' => $transaction->getDebitAccount()->getId()]) ?>">
          <?= $this->e($transaction->getDebitAccount()->getName()) ?>
        </a>
        &rarr;
        <a href="<?= $this->route('get_account', ['accountId' => $transaction->getCreditAccount()->getId()]) ?>">
          <?= $this->e($transaction->getCreditAccount()->getName()) ?>
        </a>
        <br>
        <em><?= $this->e($transaction->getDescription()) ?></em>
      </article>
      <?php endforeach; ?>
    </section>
  <?php endforeach; ?>
<?php endif; ?>
