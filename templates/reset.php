<?php $this->layout('layout', ['title' => 'Reset Password']); ?>

<div class="container mx-auto text-center">
  <?php if (isset($error)): ?>
  <p class="font-bold mt-6 mb-6"><?= $this->e($error) ?></p>
  <p><a class="font-bold p-1 border-dashed border-0 border-b-2 border-gray-500" href="<?= $this->route('get_reset') ?>">Try again.</a></p>

  <?php elseif (isset($success)): ?>
  <p class="font-bold mt-6 mb-6">Password reset successfully.</p>
  <p><a class="font-bold p-1 border-dashed border-0 border-b-2 border-gray-500" href="<?= $this->route('get_login') ?>">Proceed to log in.</a></p>

  <?php else: ?>
  <form method="post" action="<?= $this->route('post_reset') ?>">
    <input type="hidden" name="user" value="<?= $this->e($user) ?>">
    <input type="hidden" name="token" value="<?= $this->e($token) ?>">
    <div class="flex flex-col items-center">

      <div class="flex flex-col justify-start w-1/3 mt-3 mb-3">
        <div class="block flex text-left">
          <label for="password" class="text-lg">New Password</label>
        </div>
        <div class="mt-1 block flex">
          <input type="password" id="password" name="password" class="bg-gray-50 border border-gray-500 rounded-md shadow-md p-2 w-full">
        </div>
      </div>

      <button type="submit" class="rounded-md shadow-md border border-gray-500 bg-gray-50 p-2 mt-8 text-lg w-1/6">
        Reset Password
      </button>

    </div>
  </form>
  <?php endif; ?>

</div>
