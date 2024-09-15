<?php

$this->layout('layout');

$title = 'Reconcile';

?>

<nav class="grid">
  <ol aria-label="breadcrumb" class="breadcrumb">
    <li><a href="<?= $this->route('get_menu') ?>">Menu</a></li>
    <li><a href="<?= $this->route('get_reconcile') ?>" aria-current="page"><?= $title ?></a></li>
  </ol>
  <ul aria-label="subnavigation" class="subnavigation">
    <li><a href="<?= $this->route('get_transactions') ?>">List Transactions</a></li>
    <li><a href="<?= $this->route('add_transaction') ?>">Add Transaction</a></li>
  </ul>
</nav>

<h1 class="center"><?= $title ?></h1>

<form method="post" action="<?= $this->route('post_reconcile') ?>" enctype="multipart/form-data">

  <label for="upload">File</label>
  <input type="file" name="import" required>

  <?php $this->insert('accounts-datalist', ['accounts' => $accounts]); ?>

  <label for="account">Account</label>
  <input id="account" name="account" list="accounts" type="text" value="<?= $this->e($account ?? '') ?>" placeholder="e.g. Checking" required>

  <input type="submit" value="Reconcile">

</form>

<?php if (isset($summary)): ?>

  <?php if (count($summary->getDibbyTransactionsMissingFromImport())): ?>
    <h2 class="center">Missing from Upload</h2>
    <?php foreach ($summary->getDibbyTransactionsMissingFromImport() as $dibbyTransaction): ?>
      <?php $this->insert('transaction-listing', ['transaction' => $dibbyTransaction]); ?>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php if (count($summary->getImportTransactionsMissingFromDibby())): ?>
    <h2 class="center">Missing from Dibby</h2>
    <?php foreach ($summary->getImportTransactionsMissingFromDibby() as $importTransaction): ?>
      <?php $this->insert('import-transaction-listing', ['transaction' => $importTransaction]); ?>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php if (count($summary->getImportTransactionsWithDifferingCounts())): ?>
    <h2 class="center">Different Count in Upload</h2>
    <?php foreach ($summary->getImportTransactionsWithDifferingCounts() as $importTransaction): ?>
      <?php $this->insert('import-transaction-listing', ['transaction' => $importTransaction]); ?>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php if (count($summary->getDibbyTransactionsWithDifferentCounts())): ?>
    <h2 class="center">Different Count in Dibby</h2>
    <?php foreach ($summary->getDibbyTransactionsWithDifferentCounts() as $dibbyTransaction): ?>
      <?php $this->insert('transaction-listing', ['transaction' => $dibbyTransaction]); ?>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php if (
    empty($summary->getDibbyTransactionsMissingFromImport())
    && empty($summary->getImportTransactionsMissingFromDibby())
    && empty($summary->getImportTransactionsWithDifferingCounts())
    && empty($summary->getDibbyTransactionsWithDifferentCounts())
  ): ?>
  <h2 class="center">All Good!</h2>
  <p class="center">Congratulations, your account is reconciled!</p>
  <?php endif; ?>

<?php endif; ?>
