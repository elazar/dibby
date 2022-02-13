<datalist id="accounts">
  <?php foreach ($accounts as $account): ?>
  <option value="<?= $this->e($account->getName()) ?>">
  <?php endforeach; ?>
</datalist>
