<?php

$title = (isset($id) ? 'Edit' : 'Add') . ' Transaction';

$this->layout('layout', [
  'user' => $user,
  'activeRoute' => 'get_transactions',
  'title' => $title,
]);

?>

<div class="container mx-auto text-center">
  <?php if (isset($error)): ?>
  <p class="font-bold mt-6 mb-6"><?= $this->e($error) ?></p>
  <?php endif; ?>

  <form method="post" action="<?= $this->route('post_transactions') ?>">
    <?php if (isset($id)): ?>
    <input type="hidden" name="id" value="<?= $this->e($id) ?>">
    <?php endif; ?>

    <div class="p-3 flex-shrink">
      <label for="amount" class="block">Amount</label>
      <div class="mt-1">
        <input id="amount" name="amount" type="number" step="0.01" autofocus class="py-1 px-2 mt-1 border border-gray-500 rounded-md shadow-md" placeholder="e.g. 1.99">
      </div>
    </div>

    <div class="p-3 flex-shrink">
      <label for="debit_account" class="block">Debit Account</label>
      <div class="mt-1">
        <input id="debit_account" name="debit_account" type="text" class="py-1 px-2 mt-1 border border-gray-500 rounded-md shadow-md" placeholder="e.g. Checking">
      </div>
    </div>

    <div class="p-3 flex-shrink">
      <label for="credit_account" class="block">Credit Account</label>
      <div class="mt-1">
        <input id="credit_account" name="credit_account" type="text" class="py-1 px-2 mt-1 border border-gray-500 rounded-md shadow-md" placeholder="e.g. Groceries">
      </div>
    </div>

    <div class="p-3 flex-shrink">
      <label for="description" class="block">Description</label>
      <div class="mt-1">
        <input id="description" name="description" type="text" class="py-1 px-2 mt-1 border border-gray-500 rounded-md shadow-md" placeholder="(Optional)">
      </div>
    </div>

    <div class="p-3 flex-shrink">
      <label for="date" class="block">Date</label>
      <div class="mt-1">
        <input id="date" name="date" type="date" class="py-1 px-2 mt-1 border border-gray-500 rounded-md shadow-md">
      </div>
    </div>

    <div class="p-3 mt-1 justify-center flex">
    <button type="submit" class="px-6 py-3 bg-white mt-1 border border-gray-500 rounded-md shadow-md"><?php if (isset($id)): ?>Update<?php else: ?>Add<?php endif; ?> Transaction</button>
    </div>

  </form>
</div>

<?php if (!isset($id)): ?>
<script>
  const now = new Date()
  const offset = now.getTimezoneOffset()
  const local = new Date(now.getTime() - (offset * 60000))
  document.getElementById("date").valueAsDate = local
</script>
<?php endif; ?>
