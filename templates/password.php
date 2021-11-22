<?php $this->layout('layout', ['title' => $title]); ?>

<div class="container mx-auto text-center">
  <h2 class="text-xl font-bold mb-6"><?= $this->e($title) ?></h2>
  <form method="post" action="<?= $this->route('post_password') ?>">
    <div class="flex flex-col items-center">

      <div class="flex flex-col justify-start w-1/3 mb-3">
        <div class="block flex text-left">
          <label for="email" class="text-lg">E-mail</label>
        </div>
        <div class="mt-1 block flex">
          <input type="email" id="email" name="email" class="bg-gray-100 border border-gray-500 rounded-md shadow-md p-2 w-full">
        </div>
      </div>

      <button type="submit" class="rounded-md shadow-md border border-gray-500 bg-gray-100 p-2 mt-6 text-lg w-1/6">
        Send Reset E-mail
      </button>

      <p class="mt-8">Remembered your password? <a class="font-bold p-1 border-dashed border-0 border-b-2 border-gray-500" href="<?= $this->route('get_login') ?>">Log in.</a></p>
    </div>
  </form>
</div>
