<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$currentUser = require_permission_for_page('transactions');
$settings = get_store_settings();
$currency = $settings['currency'];

$typeStyles = [
    'SALE' => 'bg-brand-500/15 text-brand-400',
    'PURCHASE' => 'bg-blue-500/15 text-blue-400',
    'EXPENSE' => 'bg-orange-500/15 text-orange-400',
];

$filter = $_GET['type'] ?? null;
if (!in_array($filter, ['SALE', 'PURCHASE', 'EXPENSE'], true)) {
    $filter = null;
}

if ($filter) {
    $stmt = db()->prepare(
        'SELECT t.*, p.name AS party_name FROM transactions t LEFT JOIN parties p ON p.id = t.party_id
         WHERE t.type = ? ORDER BY t.date DESC LIMIT 100'
    );
    $stmt->execute([$filter]);
} else {
    $stmt = db()->query(
        'SELECT t.*, p.name AS party_name FROM transactions t LEFT JOIN parties p ON p.id = t.party_id
         ORDER BY t.date DESC LIMIT 100'
    );
}
$transactions = $stmt->fetchAll();

$tabs = [
    ['label' => 'All', 'value' => null],
    ['label' => 'Sales', 'value' => 'SALE'],
    ['label' => 'Purchases', 'value' => 'PURCHASE'],
    ['label' => 'Expenses', 'value' => 'EXPENSE'],
];

$pageTitle = 'Transactions';
require __DIR__ . '/includes/layout_header.php';
?>

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
  <div class="flex gap-2">
    <?php foreach ($tabs as $t): ?>
      <a href="<?= $t['value'] ? '/transactions.php?type=' . e($t['value']) : '/transactions.php' ?>"
         class="rounded-lg px-3 py-1.5 text-sm font-medium <?= $filter === $t['value'] ? 'bg-brand-500 text-ink' : 'bg-panel text-gray-300 border border-line hover:bg-panel-2' ?>">
        <?= e($t['label']) ?>
      </a>
    <?php endforeach; ?>
  </div>
  <a href="/transaction_form.php" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 hover:bg-brand-400 px-4 py-2 text-sm font-semibold text-ink">+ New transaction</a>
</div>

<div class="overflow-x-auto rounded-xl border border-line bg-panel">
  <table class="w-full text-left text-sm">
    <thead class="border-b border-line bg-panel-2 text-xs uppercase tracking-wide text-gray-500">
      <tr>
        <th class="px-4 py-3">Date</th>
        <th class="px-4 py-3">Type</th>
        <th class="px-4 py-3">Party</th>
        <th class="px-4 py-3">Description</th>
        <th class="px-4 py-3 text-right">Total</th>
        <th class="px-4 py-3 text-right">Due</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($transactions as $t): ?>
        <tr class="border-b border-line last:border-0 hover:bg-panel-2">
          <td class="px-4 py-3 text-gray-500"><a href="/transaction_view.php?id=<?= e($t['id']) ?>" class="block"><?= e(format_date($t['date'])) ?></a></td>
          <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-xs font-semibold <?= e($typeStyles[$t['type']]) ?>"><?= e($t['type']) ?></span></td>
          <td class="px-4 py-3 text-gray-300"><?= e($t['party_name'] ?? '-') ?></td>
          <td class="px-4 py-3 text-gray-500"><?= e($t['description'] ?? '-') ?></td>
          <td class="px-4 py-3 text-right font-medium text-gray-100"><?= e(format_money((float) $t['total'], $currency)) ?></td>
          <td class="px-4 py-3 text-right">
            <?php if ($t['due_amount'] > 0): ?>
              <span class="font-semibold text-red-400"><?= e(format_money((float) $t['due_amount'], $currency)) ?></span>
            <?php else: ?>
              <span class="text-gray-600">-</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$transactions): ?>
        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No transactions yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
