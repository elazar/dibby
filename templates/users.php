<?php

$this->layout('layout', [
    'title' => 'Users',
    'activeRoute' => 'get_users',
    'userName' => $userName,
]);

?>

<div class="container mx-auto text-center">
  <nav class="hidden md:flex">
    <ul class="justify-center items-center space-x-10 flex mb-10">
      <li class="inline"><a href="<?= $this->route('add_user') ?>" class="rounded-md py-1 px-3 border border-gray-800 hover:bg-gray-50 hover:border-opacity-100 border-opacity-0">Add User</a></li>
    </ul>
  </nav>
  <table class="text-left bg-gray-50 w-1/4 mx-auto">
    <tr class="border border-gray-800 bg-gray-300">
      <th class="p-2">Name</th>
      <th class="p-2"></th>
    </tr>
    <?php foreach ($users as $user): ?>
    <tr class="border border-gray-800">
      <td class="p-2"><?= $this->e($user->getName()) ?></td>
      <td class="p-2 text-right"><a href="<?= $this->route('edit_user', ['userId' => $user->getId()]) ?>" class="p-1 border-dashed border-0 border-b-2 border-gray-500">Edit</a></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
