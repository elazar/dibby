<?php

$this->layout('layout', [
    'title' => 'Transactions',
    'activeRoute' => 'get_transactions',
    'userName' => $userName,
]);

$nav = [
  $this->route('get_transactions') => 'Register',
  $this->route('get_transaction') => 'Add Transaction',
  $this->route('get_templates') => 'Templates',
];

?>

<div class="container mx-auto text-center">
  <nav class="hidden md:block">
    <ul class="justify-center items-center space-x-10 flex mb-10">
      <?php foreach ($nav as $path => $label): ?>
      <li class="inline"><a href="<?= $path ?>" class="rounded-md py-1 px-3 border border-gray-800 <?php if ($path === '/'): ?>font-bold bg-gray-50<?php else: ?>hover:bg-gray-50 hover:border-opacity-100 border-opacity-0<?php endif; ?>"><?= $this->e($label) ?></a></li>
      <?php endforeach; ?>
    </ul>
  </nav>
  <?php if (count($transactions) === 0): ?>
  <p>Looks like you don't have any transactions yet.</p>
  <p class="mt-5">Would you like to <a href="<?= $this->route('get_transaction') ?>" class="font-bold p-1 border-dashed border-0 border-b-2 border-gray-500">add some</a>?</p>
  <?php else: ?>
  <?php endif; ?>
</div>
