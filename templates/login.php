<?php $this->layout('layout'); ?>

<h1 class="center">Log In</h1>

<?php if (isset($error)): ?>
<p><strong><?= $this->e($error) ?></strong></p>
<?php endif; ?>

<form method="post" action="<?= $this->route('post_login') ?>">
  <label for="email">E-mail</label>
  <input type="email" id="email" name="email" required autofocus>

  <label for="password">Password</label>
  <input type="password" id="password" name="password" required>

  <button id="login_button" type="submit">Log In</button>
</form>

<p class="center">Forgot your password? <a href="<?= $this->route('get_password') ?>" tabindex="0">Reset it</a>.</p>

<script>
  lockButtonOnSubmit("login_button", "Logging In...")
</script>
