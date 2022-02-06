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
<h2 class="center">Summary</h2>
<table role="grid" class="small">
  <thead>
    <tr>
      <th>Date</th>
      <th class="center">Count</th>
      <th class="center">Total</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($summary->getRows() as $row): ?>
    <tr>
      <td><?= $this->formatDate($row->getDate()) ?></td>
      <td class="right"><?= $row->getCount() ?></td>
      <td class="right"><?= $this->formatAmount($row->getTotal()) ?></td>
    </tr>
  <?php endforeach; ?>
    <tr>
      <td><strong>Total</strong></td>
      <td class="right"><?= $summary->getCount() ?></td>
      <td class="right"><?= $this->formatAmount($summary->getTotal()) ?></td>
    </tr>
  <tbody>
</table>

<h2 class="center">Listing</h2>
  <?php foreach ($transactions as $transaction): ?>
<section>
  <article>
    <a class="edit" href="<?= $this->route('edit_transaction', ['transactionId' => $transaction->getId()]) ?>">Edit</a>
    <strong><?= $this->formatAmount($transaction->getAmount()) ?></strong><br>
    <?= $this->formatDate($transaction->getDate()) ?><br>
    <?php if ($transaction->getDescription()): ?>
    <em><?= $this->e($transaction->getDescription()) ?></em><br>
    <?php endif; ?>
    From: <a href="<?= $this->route('get_account_summary', ['accountId' => $transaction->getDebitAccount()->getId()]) ?>">
      <?= $this->e($transaction->getDebitAccount()->getName()) ?>
    </a><br>
    To: <a href="<?= $this->route('get_account_summary', ['accountId' => $transaction->getCreditAccount()->getId()]) ?>">
      <?= $this->e($transaction->getCreditAccount()->getName()) ?>
    </a><br>
  </article>
</section>
  <?php endforeach; ?>
<?php endif; ?>
