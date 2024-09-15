<?php $this->layout('layout'); ?>

<h1 class="center">Reset Password</h1>

<?php if (isset($error)): ?>
<p><strong><?= $this->e($error) ?></strong></p>
<p><a href="<?= $this->route('get_reset') ?>">Try again.</a></p>

<?php elseif (isset($success)): ?>
<p>Password reset successfully.</p>
<p><a href="<?= $this->route('get_login') ?>">Proceed to log in.</a></p>

<?php else: ?>
<form method="post" action="<?= $this->route('post_reset') ?>">
  <input type="hidden" name="user" value="<?= $this->e($user) ?>">
  <input type="hidden" name="token" value="<?= $this->e($token) ?>">

  <label for="password">New Password</label>
  <input type="password" id="password" name="password" required autofocus>

  <button type="submit">Reset Password</button>
</form>
<?php endif; ?>
