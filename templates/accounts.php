<?php $this->layout('layout'); ?>

<nav class="grid">
  <ol aria-label="breadcrumb" class="breadcrumb">
    <li><a href="<?= $this->route('get_menu') ?>">Menu</a></li>
    <li><a href="<?= $this->route('get_accounts') ?>" aria-current="page">List Accounts</a></li>
  </ol>
</nav>

<h1 class="center">Accounts</h1>

<table role="grid">
  <thead>
    <tr>
      <th>Name</th>
      <th colspan="2"></th>
    </tr>
  </thead>
  <tbody>
<?php foreach ($accounts as $account): ?>
    <tr>
      <td><?= $this->e($account->getName()) ?></td>
      <td class="right">
        <a href="<?= $this->route('get_account_summary', ['accountId' => $account->getId()]) ?>">Summary</a>
      </td>
      <td class="right">
        <a href="<?= $this->route('edit_account', ['accountId' => $account->getId()]) ?>">Edit</a>
      </td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table>
