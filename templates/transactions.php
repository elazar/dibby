<?php

$this->layout('layout', [
    'title' => 'Transactions',
    'activeRoute' => 'get_transactions',
    'user' => $user,
]);

?>

<div class="container mx-auto text-center">
  <?php $this->insert('transactions-nav', ['activeRoute' => 'get_transactions']) ?>
  <?php if (count($transactions) === 0): ?>
  <p>Looks like you don't have any transactions yet.</p>
  <p class="mt-5">Want to <a href="<?= $this->route('get_transaction') ?>" class="font-bold p-1 border-dashed border-0 border-b-2 border-gray-500">add some</a>?</p>
  <?php else: ?>
  <?php endif; ?>
</div>
