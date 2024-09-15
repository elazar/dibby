<?php $this->layout('layout'); ?>

<h1 class="center">Forgot Password</h1>

<?php if (isset($message)): ?>
<p><strong><?= $this->e($message) ?></strong></p>
<?php endif; ?>

<form method="post" action="<?= $this->route('post_password') ?>">
  <label for="email">E-mail</label>
  <input type="email" id="email" name="email" required autofocus>

  <button type="submit">Send Reset E-mail</button>
</form>

<p>Remembered your password? <a href="<?= $this->route('get_login') ?>">Log in.</a></p>
