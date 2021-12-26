<?php $this->layout('layout'); ?>

<nav class="grid">
  <ol aria-label="breadcrumb" class="breadcrumb">
    <li><a href="<?= $this->route('get_menu') ?>">Menu</a></li>
    <li><a href="<?= $this->route('get_accounts') ?>">List Accounts</a></li>
    <li><a href="<?= $this->route('get_account', ['accountId' => $account->getId()]) ?>" aria-current="page"><?= $this->e($account->getName()) ?></a></li>
  </ol>
</nav>

<h1 class="center">Transactions</h1>

<?php foreach ($this->transactionsByDate($transactions) as $date => $transactionsForDate): ?>
<section>
  <h2 class="center"><?= $date ?></h2>
  <?php foreach ($transactionsForDate as $transaction): ?>
  <article>
    <a class="transaction-edit-link" href="<?= $this->route('edit_transaction', ['transactionId' => $transaction->getId()]) ?>">Edit</a>
    <strong><?= number_format($transaction->getAmount(), 2) ?></strong>
    <?php if ($transaction->getDebitAccount()->getId() === $account->getId()): ?>
        <?php $factor = 1; ?>
    &rarr;
    <a href="<?= $this->route('get_account', ['accountId' => $transaction->getCreditAccount()->getId()]) ?>">
      <?= $this->e($transaction->getCreditAccount()->getName()) ?>
    </a>
    <?php else: ?>
        <?php $factor = -1; ?>
    &larr;
    <a href="<?= $this->route('get_account', ['accountId' => $transaction->getDebitAccount()->getId()]) ?>">
      <?= $this->e($transaction->getDebitAccount()->getName()) ?>
    </a>
    <?php endif; ?>
    <?php if ($transaction->getDescription()): ?>
      <br>
      <em><?= $this->e($transaction->getDescription()) ?></em>
    <?php endif; ?>
    <br>
    Balance: <?= number_format($balance, 2) ?>
  </article>
    <?php $balance += $factor * $transaction->getAmount(); ?>
  <?php endforeach; ?>
</section>
<?php endforeach; ?>
