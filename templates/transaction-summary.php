<h2 class="center">Summary</h2>
<table role="grid" class="small">
  <thead>
    <tr>
      <th>Date</th>
      <th class="center">Count</th>
      <th class="center">Total</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($summary->getRows() as $row): ?>
    <tr>
      <td><?= $this->formatDate($row->getDate()) ?></td>
      <td class="right"><?= $row->getCount() ?></td>
      <td class="right"><?= $this->formatAmount($row->getTotal()) ?></td>
    </tr>
  <?php endforeach; ?>
    <tr>
      <td><strong>Total</strong></td>
      <td class="right"><?= $summary->getCount() ?></td>
      <td class="right"><?= $this->formatAmount($summary->getTotal()) ?></td>
    </tr>
  <tbody>
</table>
