<?php $this->layout('layout'); ?>

<nav class="grid">
  <ol aria-label="breadcrumb" class="breadcrumb">
    <li><a href="<?= $this->route('get_menu') ?>">Menu</a></li>
    <li><a href="<?= $this->route('get_accounts_reports') ?>" aria-current="page">View Reports</a></li>
  </ol>
</nav>

<h1 class="center">View Reports</h1>

<?php if (count($accounts) === 0): ?>
<div class="center">
  <p>Looks like you don't have any accounts yet.</p>
  <p>They'll be created automatically when you <a href="<?= $this->route('add_transaction') ?>">create a transaction</a>!</p>
</div>
<?php else: ?>
  <?php if (count($creditAccounts) > 0): ?>
<h2 class="center">Available Credit</h2>
<table role="grid">
  <thead>
    <tr>
      <th>Name</th>
      <th class="right">Available</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($creditAccounts as $account): ?>
    <tr>
      <td>
        <a href="<?= $this->route('get_account_summary', ['accountId' => $account->getId()]) ?>"><?= $this->e($account->getName()) ?></a>
      </td>
      <td class="right">
        <?= number_format($account->getCreditLimit() + $balances[$account->getId()], 2) ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
  <?php endif; ?>

<h2 class="center">Balances</h2>
<table role="grid">
  <thead>
    <tr>
      <th>Name</th>
      <th class="right">Balance</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($accounts as $account): ?>
    <tr>
      <td>
        <a href="<?= $this->route('get_account_summary', ['accountId' => $account->getId()]) ?>"><?= $this->e($account->getName()) ?></a>
      </td>
      <td class="right">
        <?= number_format($balances[$account->getId()], 2) ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
