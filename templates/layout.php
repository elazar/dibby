<!doctype html>
<html class="no-js" lang="en">
<head>
  <meta charset="utf-8">
  <title><?= $this->e($title) ?> - Dibby</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="m-4 bg-blue-200 font-serif">
  <div class="p-2 pl-4 mr-4 w-full bg-gray-100 text-2xl font-bold border border-gray-500 shadow-md rounded-md">
    <h1>Dibby</h1>
  </div>
  <div class="mt-8">
    <?= $this->section('content') ?>
  </div>
</body>
</html>
