<?php

$nav = [
    'get_transactions' => 'Transactions',
    'get_accounts' => 'Accounts',
    'get_activity' => 'Activity',
    'get_users' => 'Users',
    'get_help' => 'Help',
];

?>
<!doctype html>
<html class="no-js" lang="en">
<head>
  <meta charset="utf-8">
  <title><?= $this->e($title) ?> - Dibby</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://unpkg.com/tailwindcss@%5E2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="m-4 bg-blue-200 text-gray-800 text-lg font-serif">
  <header class="p-2 pl-4 pr-4 mr-4 w-full bg-gray-50 border shadow-md rounded-md grid grid-cols-5 items-center">
    <h1 class="text-2xl font-bold">Dibby</h1>
    <?php if (isset($user)): ?>
    <nav class="col-span-3 flex place-content-center">
      <ul class="hidden md:flex space-x-2 lg:space-x-10">
        <?php foreach ($nav as $route => $label): ?>
        <li class="inline"><a href="<?= $this->route($route) ?>" class="rounded-md py-1 px-3 border border-gray-300 <?php if (isset($activeRoute) && $route === $activeRoute): ?>font-bold bg-gray-200<?php else: ?>hover:bg-gray-200 hover:border-opacity-100 border-opacity-0<?php endif; ?>"><?= $this->e($label) ?></a></li>
        <?php endforeach; ?>
      </ul>
      <div class="flex justify-center<?php if ($activeRoute === 'get_menu'): ?> hidden<?php endif; ?>">
        <a href="<?= $this->route('get_menu') ?>" class="p-2">
          <svg class="h-6 w-6 md:hidden" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" area-labelledby="menu-icon">
            <title id="menu-icon">menu icon</title>
            <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
          </svg>
        </a>
      </div>
    </nav>
    <div class="flex justify-end">
      <a href="<?= $this->route('edit_user', ['userId' => $user->getId()]) ?>" class="hover:bg-gray-200 hover:border-opacity-100 border-opacity-0 border border-gray-300 rounded-md p-1 flex leading-none items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="flex h-7 w-7 mr-1" viewBox="0 0 20 20" fill="currentColor" aria-labelledby="user-icon">
          <title id="user-icon">user icon</title>
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd" />
        </svg>
        <?= $this->e($user->getName()) ?>
      </a>
    </div>
    <?php endif; ?>
  </header>
  <main class="mt-6 md:mt-8">
    <h2 class="text-center font-bold font-xl block mb-4"><?= $this->e($title) ?></h2>
    <?= $this->section('content') ?>
  </main>
  <div id="progress-indicator-modal" class="hidden container h-full w-full place-content-center absolute top-0 left-0 flex">
    <div class="rounded-md bg-gray-800 text-gray-50 p-4 flex items-center m-auto">
      <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-labelledby="progress-indicator">
        <title id="progress-indicator">progress indicator</title>
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      Please wait...
    </div>
  </div>
  <script>
    window.addEventListener("beforeunload", () => {
      document.getElementById("progress-indicator-modal").classList.remove("hidden")
    })
  </script>
</body>
</html>
