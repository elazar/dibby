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
    <li><a href="<?= $this->route('add_transaction') ?>">Add Transaction</a></li>
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
  <input id="amount" name="amount" type="number" value="<?= $this->e($amount ?? '') ?>" step="0.01" required autofocus placeholder="e.g. 1.99">

  <?php $this->insert('accounts-datalist', ['accounts' => $accounts]); ?>

  <label for="debit_account">From Account</label>
  <input id="debit_account" name="debit_account" list="accounts" type="text" value="<?= $this->e($debitAccount ?? '') ?>" placeholder="e.g. Checking" required>

  <label for="credit_account">To Account</label>
  <input id="credit_account" name="credit_account" list="accounts" type="text" value="<?= $this->e($creditAccount ?? '') ?>" placeholder="e.g. Groceries" required>

  <label for="description">Description</label>
  <input id="description" name="description" type="text" value="<?= $this->e($description ?? '') ?>" placeholder="(Optional)">

  <label for="date">Date</label>
  <input id="date" name="date" type="date" value="<?= $this->e($date ?? '') ?>">

  <?php if (isset($id)): ?>
  <input type="submit" id="update_transaction_button" name="action" value="Update Transaction">
  <input type="submit" id="delete_transaction_button" name="action" value="Delete Transaction">
  <?php else: ?>
  <input type="submit" id="add_transaction_button" name="action" value="Add Transaction">
  <?php endif; ?>
</form>

<script>
  <?php if (isset($id)): ?>
  lockButtonOnSubmit("update_transaction_button", "Updating Transaction...")
  lockButtonOnSubmit("delete_transaction_button", "Deleting Transaction...")
  document.getElementById("delete_transaction_button").addEventListener("click", (evt) => {
    if (!confirm("Delete this transaction?")) {
      evt.preventDefault();
    }
  });
  <?php else: ?>
  lockButtonOnSubmit("add_transaction_button", "Adding Transaction...")
  <?php endif; ?>
</script>
