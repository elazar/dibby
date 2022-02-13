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
<?php $this->insert('transaction-summary', ['summary' => $summary]); ?>

<h2 class="center">Listing</h2>
  <?php foreach ($transactions as $transaction): ?>
    <?php $this->insert('transaction-listing', ['transaction' => $transaction]); ?>
  <?php endforeach; ?>
<?php endif; ?>
