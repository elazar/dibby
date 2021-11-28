<?php

$nav = [
  'get_transactions' => 'Register',
  'get_transaction' => 'Add Transaction',
  'get_templates' => 'Templates',
];

?>

<nav class="hidden md:block">
<ul class="justify-center items-center space-x-10 flex mb-10">
  <?php foreach ($nav as $route => $label): ?>
  <li class="inline"><a href="<?= $this->e($this->route($route)) ?>" class="rounded-md py-1 px-3 border border-gray-800 <?php if ($route === $activeRoute): ?>font-bold bg-gray-50<?php else: ?>hover:bg-gray-50 hover:border-opacity-100 border-opacity-0<?php endif; ?>"><?= $this->e($label) ?></a></li>
  <?php endforeach; ?>
</ul>
</nav>
