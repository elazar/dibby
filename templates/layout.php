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
    <?php if (isset($userName)): ?>
    <nav class="col-span-3">
      <ul class="justify-center items-center space-x-10 flex">
        <li class="inline"><a href="#" class="font-bold border border-gray-300 bg-gray-200 rounded-md py-1 px-3">Transactions</a></li>
        <li class="inline"><a href="#" class="hover:bg-gray-200 hover:border-opacity-100 border-opacity-0 border border-gray-300 rounded-md py-1 px-3">Accounts</a></li>
        <li class="inline"><a href="#" class="hover:bg-gray-200 hover:border-opacity-100 border-opacity-0 border border-gray-300 rounded-md py-1 px-3">Activity</a></li>
        <li class="inline"><a href="#" class="hover:bg-gray-200 hover:border-opacity-100 border-opacity-0 border border-gray-300 rounded-md py-1 px-3">Users</a></li>
        <li class="inline"><a href="#" class="hover:bg-gray-200 hover:border-opacity-100 border-opacity-0 border border-gray-300 rounded-md py-1 px-3">Help</a></li>
      </ul>
    </nav>
    <div class="flex justify-end">
      <a href="#" class="hover:bg-gray-200 hover:border-opacity-100 border-opacity-0 border border-gray-300 rounded-md p-1 flex leading-none items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="flex h-7 w-7 mr-1" viewBox="0 0 20 20" fill="currentColor" aria-labelledby="user-icon">
          <title id="user-icon">user icon</title>
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd" />
        </svg>
        <?= $this->e($userName) ?>
      </a>
    </div>
    <?php endif; ?>
  </header>
  <main class="mt-8">
    <?= $this->section('content') ?>
  </main>
</body>
</html>
