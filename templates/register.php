<?php $this->layout('layout'); ?>

<h1 class="center">Register</h1>

<?php if (isset($error)): ?>
<p><strong><?= $this->e($error) ?></strong></p>
<?php endif; ?>

<form id="register_form" method="post" action="<?= $this->route('post_register') ?>">
  <label for="name">Name</label>
  <input type="text" id="name" name="name" required autofocus>

  <label for="email">E-mail</label>
  <input type="email" id="email" name="email" required>

  <label for="password">Password</label>
  <input type="password" id="password" name="password" required>

  <button id="register_button" type="submit">Register</button>
</form>

<script>
  document.getElementById("register_form").addEventListener("submit", () => {
    const button = document.getElementById("register_button")
    button.innerText = "Registering..."
    button.disabled = true
    if (button.ariaBusy !== undefined) {
      button.ariaBusy = true
    } else {
      button.setAttribute("aria-busy", true)
    }
  })
</script>
