<section>
  <article>
    <a class="edit" href="<?= $this->route('edit_transaction', ['transactionId' => $transaction->getId()]) ?>">Edit</a>
    <strong><?= $this->formatAmount($transaction->getAmount()) ?></strong><br>
    <?= $this->formatDate($transaction->getDate()) ?><br>
    <?php if ($transaction->getDescription()): ?>
    <em><?= $this->e($transaction->getDescription()) ?></em><br>
    <?php endif; ?>
    From: <a href="<?= $this->route('get_account_summary', ['accountId' => $transaction->getDebitAccount()->getId()]) ?>">
      <?= $this->e($transaction->getDebitAccount()->getName()) ?>
    </a><br>
    To: <a href="<?= $this->route('get_account_summary', ['accountId' => $transaction->getCreditAccount()->getId()]) ?>">
      <?= $this->e($transaction->getCreditAccount()->getName()) ?>
    </a><br>
  </article>
</section>
