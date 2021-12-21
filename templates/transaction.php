<?php

$this->layout('layout');

$title = (isset($id) ? 'Edit' : 'Add') . ' Transaction';

?>

<nav class="grid">
  <ol aria-label="breadcrumb" class="breadcrumb">
    <li><a href="<?= $this->route('get_menu') ?>">Menu</a></li>
    <li><a href="<?= $this->route('get_transactions') ?>" aria-current="page"><?= $title ?></a></li>
  </ol>
  <ul aria-label="subnavigation" class="subnavigation">
    <li><a href="<?= $this->route('get_transactions') ?>">List Transactions</a></li>
    <?php if (isset($id)): ?>
    <li><a href="<?= $this->route('get_transaction') ?>">Add Transaction</a></li>
    <?php endif; ?>
  </ul>
</nav>

<h1 class="center"><?= $title ?></h1>

<?php if (isset($error)): ?>
<p><?= $this->e($error) ?></p>
<?php endif; ?>

<form method="post" action="<?= $this->route('post_transactions') ?>">
  <?php if (isset($id)): ?>
  <input type="hidden" name="id" value="<?= $this->e($id) ?>">
  <?php endif; ?>

  <label for="amount">Amount</label>
  <input id="amount" name="amount" type="number" min="0.01" step="0.01" autofocus placeholder="e.g. 1.99">

  <datalist id="accounts">
    <?php foreach ($accounts as $account): ?>
    <option value="<?= $this->e($account->getName()) ?>">
    <?php endforeach; ?>
  </datalist>

  <label for="debit_account">Debit Account</label>
  <input id="debit_account" name="debit_account" list="accounts" type="text" placeholder="e.g. Checking">

  <label for="credit_account">Credit Account</label>
  <input id="credit_account" name="credit_account" list="accounts" type="text" placeholder="e.g. Groceries">

  <label for="description">Description</label>
  <input id="description" name="description" type="text" placeholder="(Optional)">

  <label for="date">Date</label>
  <input id="date" name="date" type="date">

  <button type="submit"><?php if (isset($id)): ?>Update<?php else: ?>Add<?php endif; ?> Transaction</button>
</form>

<?php if (!isset($id)): ?>
<script>
  const now = new Date()
  const offset = now.getTimezoneOffset()
  const local = new Date(now.getTime() - (offset * 60000))
  document.getElementById("date").valueAsDate = local
</script>
<?php endif; ?>
