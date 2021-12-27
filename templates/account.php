<?php $this->layout('layout'); ?>

<nav class="grid">
  <ol aria-label="breadcrumb" class="breadcrumb">
    <li><a href="<?= $this->route('get_menu') ?>">Menu</a></li>
    <li><a href="<?= $this->route('edit_account', ['accountId' => $id]) ?>" aria-current="page">Edit Account</a></li>
  </ol>
  <ul aria-label="subnavigation" class="subnavigation">
    <li><a href="<?= $this->route('get_accounts') ?>">List Accounts</a></li>
  </ul>
</nav>

<h1 class="center">Edit Account</h1>

<?php if (isset($error)): ?>
<p><strong><?= $this->e($error) ?></strong></p>
<?php endif; ?>

<form method="post" action="<?= $this->route('post_accounts') ?>">
  <input type="hidden" name="id" value="<?= $this->e($id) ?>">

  <label for="name">Name</label>
  <input type="text" id="name" name="name" value="<?= $this->e($name) ?>" required autofocus>

  <button id="account_button" type="submit">Update Account</button>
</form>

<script>
  lockButtonOnSubmit("account_button", "Updating Account...")
</script>
