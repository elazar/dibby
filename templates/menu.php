<?php

$this->layout('layout');

?>

<h1 class="center">Menu</h1>

<aside class="center">
  <nav>
    <ul>
      <li>
        <h2>Transactions</h2>
        <ul>
          <li><a href="<?= $this->route('get_transactions') ?>">List Transactions</a></li>
          <li><a href="<?= $this->route('add_transaction') ?>">Add Transaction</a></li>
        </ul>
      </li>
      <li>
        <h2>Accounts</h2>
        <ul>
          <li><a href="<?= $this->route('get_accounts') ?>">List Accounts</a></li>
          <li><a href="<?= $this->route('get_accounts_reports') ?>">View Reports</a></li>
          <li><a href="<?= $this->route('get_reconcile') ?>">Reconcile</a></li>
        </ul>
      </li>
      <li>
        <h2>Users</h2>
        <ul>
          <li><a href="<?= $this->route('get_users') ?>">List Users</a></li>
          <li><a href="<?= $this->route('add_user') ?>">Add User</a></li>
        </ul>
      </li>
    </ul>
  </nav>
</aside>
