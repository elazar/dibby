<?php

$this->layout('layout', [
    'title' => 'Menu',
    'activeRoute' => 'get_menu',
    'user' => $user,
]);

$link = function (string $route, string $label) {
?>
<a class="p-1 border-dashed border-0 border-b-2 border-gray-500" href="<?= $this->route($route) ?>"><?= $this->e($label) ?></a>
<?php
};
?>

<div class="container mx-auto flex justify-center">
  <nav>
    <ul class="list-disc mt-4">
      <li class="mb-10">
        <?php $link('get_transactions', 'Transactions') ?>
        <ul class="list-disc ml-5">
          <li class="mt-3"><?php $link('get_transaction', 'Add Transaction') ?></li>
          <li class="mt-3"><?php $link('get_templates', 'Templates') ?></li>
        </ul>
      </li>
      <li class="mb-10">
        <?php $link('get_accounts', 'Accounts') ?>
      </li>
      <li class="mb-10">
        <?php $link('get_activity', 'Activity') ?>
      </li>
      <li class="mb-10">
        <?php $link('get_users', 'Users') ?>
        <ul class="list-disc ml-5">
          <li class="mt-3"><?php $link('add_user', 'Add User') ?></li>
        </ul>
      </li>
      <li>
        <?php $link('get_help', 'Help') ?>
      </li>
    </ul>
  </nav>
</div>
