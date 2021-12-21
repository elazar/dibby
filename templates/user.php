<?php

if (isset($id)) {
    $title = 'Edit User';
    $route = 'edit_user';
    $routeParams = ['userId' => $id];
} else {
    $title = 'Add User';
    $route = 'add_user';
    $routeParams = null;
}

$this->layout('layout');

?>

<nav class="grid">
  <ol aria-label="breadcrumb" class="breadcrumb">
    <li><a href="<?= $this->route('get_menu') ?>">Menu</a></li>
    <li><a href="<?= $this->route($route, $routeParams) ?>" aria-current="page"><?= $title ?></a></li>
  </ol>
  <ul aria-label="subnavigation" class="subnavigation">
    <li><a href="<?= $this->route('get_users') ?>">List Users</a></li>
    <?php if (isset($id)): ?>
    <li><a href="<?= $this->route('add_user') ?>">Add User</a></li>
    <?php endif; ?>
  </ul>
</nav>

<h1 class="center"><?= $title ?></h1>

<?php if (isset($error)): ?>
<p><strong><?= $this->e($error) ?></strong></p>
<?php endif; ?>

<form method="post" action="<?= $this->route('post_users') ?>">
  <?php if (isset($id)): ?>
  <input type="hidden" name="id" value="<?= $this->e($id) ?>">
  <?php endif; ?>

  <label for="name">Name</label>
  <input type="text" id="name" name="name" value="<?= $this->e($name ?? '') ?>" required autofocus>

  <label for="email">E-mail</label>
  <input type="email" id="email" name="email" value="<?= $this->e($email ?? '') ?>" required>

  <button type="submit"><?= isset($id) ? 'Update' : 'Add' ?> User</button>
</form>
