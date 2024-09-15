<?php

$this->layout('layout');

?>

<nav class="grid">
  <ol aria-label="breadcrumb" class="breadcrumb">
    <li><a href="<?= $this->route('get_menu') ?>">Menu</a></li>
    <li><a href="<?= $this->route('get_users') ?>" aria-current="page">List Users</a></li>
  </ol>
  <ul aria-label="subnavigation" class="subnavigation">
    <li><a href="<?= $this->route('add_user') ?>">Add User</a></li>
  </ul>
</nav>

<h1 class="center">List Users</h1>

<table role="grid">
  <thead>
    <tr>
      <th>Name</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($users as $user): ?>
    <tr>
      <td><?= $this->e($user->getName()) ?></td>
      <td class="right"><a href="<?= $this->route('edit_user', ['userId' => $user->getId()]) ?>">Edit</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
