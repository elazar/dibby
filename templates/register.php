<?php $this->layout('layout', ['title' => 'Register']); ?>

<div class="container mx-auto text-center">
  <?php if (isset($error)): ?>
  <p class="font-bold mt-6 mb-6"><?= $this->e($error) ?></p>
  <?php endif; ?>

  <form method="post" action="<?= $this->route('post_register') ?>">
    <div class="flex flex-col items-center">

      <div class="flex flex-col justify-start w-1/3 mb-3">
        <div class="block flex text-left">
          <label for="name" class="text-lg">Name</label>
        </div>
        <div class="mt-1 block flex">
          <input type="text" id="name" name="name" class="bg-gray-50 border border-gray-500 rounded-md shadow-md p-2 w-full">
        </div>
      </div>

      <div class="flex flex-col justify-start w-1/3 mb-3">
        <div class="block flex text-left">
          <label for="email" class="text-lg">E-mail</label>
        </div>
        <div class="mt-1 block flex">
          <input type="email" id="email" name="email" class="bg-gray-50 border border-gray-500 rounded-md shadow-md p-2 w-full">
        </div>
      </div>

      <div class="flex flex-col justify-start w-1/3 mt-3 mb-3">
        <div class="block flex text-left">
          <label for="password" class="text-lg">Password</label>
        </div>
        <div class="mt-1 block flex">
          <input type="password" id="password" name="password" class="bg-gray-50 border border-gray-500 rounded-md shadow-md p-2 w-full">
        </div>
      </div>

      <button type="submit" class="rounded-md shadow-md border border-gray-500 bg-gray-50 p-2 mt-8 text-lg w-1/6">
        Register
      </button>
    </div>
  </form>
</div>
