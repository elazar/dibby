<section>
  <article>
    <strong><?= $this->formatAmount($transaction->getAmount()) ?></strong><br>
    <?= $this->formatDate($transaction->getDate()) ?><br>
    <em><?= $this->e($transaction->getDescription()) ?></em>
  </article>
</section>
