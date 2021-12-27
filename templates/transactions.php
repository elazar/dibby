<?php $this->layout('layout'); ?>

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
  <?php foreach ($this->transactionsByDate($transactions) as $date => $transactionsForDate): ?>
    <section>
      <h2 class="center"><?= $date ?></h2>
      <?php foreach ($transactionsForDate as $transaction): ?>
      <article>
        <a class="edit" href="<?= $this->route('edit_transaction', ['transactionId' => $transaction->getId()]) ?>">Edit</a>
        <strong><?= number_format($transaction->getAmount(), 2) ?></strong>
        <em><?= $this->e($transaction->getDescription()) ?></em><br>
        From: <a href="<?= $this->route('get_account_summary', ['accountId' => $transaction->getDebitAccount()->getId()]) ?>">
          <?= $this->e($transaction->getDebitAccount()->getName()) ?>
        </a><br>
        To: <a href="<?= $this->route('get_account_summary', ['accountId' => $transaction->getCreditAccount()->getId()]) ?>">
          <?= $this->e($transaction->getCreditAccount()->getName()) ?>
        </a><br>
      </article>
      <?php endforeach; ?>
    </section>
  <?php endforeach; ?>
<?php endif; ?>
