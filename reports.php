<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$currentUser = require_permission_for_page('reports');
$settings = get_store_settings();
$currency = $settings['currency'];

$from = !empty($_GET['from']) ? date('Y-m-d 00:00:00', strtotime((string) $_GET['from'])) : date('Y-m-01 00:00:00');
$to = !empty($_GET['to']) ? date('Y-m-d 23:59:59', strtotime((string) $_GET['to'])) : date('Y-m-d 23:59:59');

$stmt = db()->prepare('SELECT COALESCE(SUM(total),0) AS total, COUNT(*) AS cnt FROM transactions WHERE type = "SALE" AND date BETWEEN ? AND ?');
$stmt->execute([$from, $to]);
$sales = $stmt->fetch();

$stmt = db()->prepare('SELECT COALESCE(SUM(total),0) AS total FROM transactions WHERE type = "PURCHASE" AND date BETWEEN ? AND ?');
$stmt->execute([$from, $to]);
$purchases = $stmt->fetch();

$stmt = db()->prepare('SELECT COALESCE(SUM(total),0) AS total FROM transactions WHERE type = "EXPENSE" AND date BETWEEN ? AND ?');
$stmt->execute([$from, $to]);
$expenses = $stmt->fetch();

$stmt = db()->prepare(
    'SELECT ti.qty, COALESCE(p.cost_price, 0) AS cost_price
     FROM transaction_items ti
     JOIN transactions t ON t.id = ti.transaction_id
     LEFT JOIN products p ON p.id = ti.product_id
     WHERE t.type = "SALE" AND t.date BETWEEN ? AND ?'
);
$stmt->execute([$from, $to]);
$saleItems = $stmt->fetchAll();

$revenue = (float) $sales['total'];
$cogs = 0.0;
foreach ($saleItems as $it) {
    $cogs += (float) $it['qty'] * (float) $it['cost_price'];
}
$grossProfit = $revenue - $cogs;
$expenseTotal = (float) $expenses['total'];
$netProfit = $grossProfit - $expenseTotal;

$balances = get_all_party_balances();
$parties = db()->query('SELECT * FROM parties')->fetchAll();
$duesList = [];
foreach ($parties as $p) {
    $bal = $balances[$p['id']] ?? 0.0;
    if ($bal != 0) {
        $p['balance'] = $bal;
        $duesList[] = $p;
    }
}
usort($duesList, fn($a, $b) => abs($b['balance']) <=> abs($a['balance']));

$pageTitle = 'Reports';
require __DIR__ . '/includes/layout_header.php';
?>

<form method="GET" action="/reports.php" class="mb-6 flex flex-wrap items-end gap-3 rounded-xl border border-line bg-panel p-4">
  <div>
    <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
    <input type="date" name="from" value="<?= e(date('Y-m-d', strtotime($from))) ?>" class="rounded-lg border border-line bg-panel-2 px-3 py-1.5 text-sm text-gray-100 focus:border-brand-500 focus:outline-none">
  </div>
  <div>
    <label class="block text-xs font-medium text-gray-500 mb-1">To</label>
    <input type="date" name="to" value="<?= e(date('Y-m-d', strtotime($to))) ?>" class="rounded-lg border border-line bg-panel-2 px-3 py-1.5 text-sm text-gray-100 focus:border-brand-500 focus:outline-none">
  </div>
  <button class="rounded-lg bg-brand-500 hover:bg-brand-400 px-4 py-1.5 text-sm font-semibold text-ink">Apply</button>
</form>

<h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-400">Profit & Loss</h2>
<div class="mb-8 overflow-hidden rounded-xl border border-line bg-panel">
  <dl class="divide-y divide-line text-sm">
    <div class="flex justify-between px-4 py-3"><dt class="text-gray-400">Revenue (sales)</dt><dd class="font-medium text-gray-200"><?= e(format_money($revenue, $currency)) ?></dd></div>
    <div class="flex justify-between px-4 py-3"><dt class="text-gray-400">Cost of goods sold</dt><dd class="font-medium text-gray-200">-<?= e(format_money($cogs, $currency)) ?></dd></div>
    <div class="flex justify-between px-4 py-3"><dt class="text-gray-400">Gross profit</dt><dd class="font-bold text-white"><?= e(format_money($grossProfit, $currency)) ?></dd></div>
    <div class="flex justify-between px-4 py-3"><dt class="text-gray-400">Operating expenses</dt><dd class="font-medium text-gray-200">-<?= e(format_money($expenseTotal, $currency)) ?></dd></div>
    <div class="flex justify-between px-4 py-3"><dt class="text-gray-400">Net profit</dt><dd class="font-bold <?= $netProfit >= 0 ? 'text-brand-400' : 'text-red-400' ?>"><?= e(format_money($netProfit, $currency)) ?></dd></div>
    <div class="flex justify-between px-4 py-3"><dt class="text-gray-400">Purchases (for reference)</dt><dd class="font-medium text-gray-200"><?= e(format_money((float) $purchases['total'], $currency)) ?></dd></div>
  </dl>
</div>

<h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-400">Outstanding dues</h2>
<div class="overflow-hidden rounded-xl border border-line bg-panel">
  <table class="w-full text-left text-sm">
    <thead class="border-b border-line bg-panel-2 text-xs uppercase tracking-wide text-gray-500">
      <tr><th class="px-4 py-3">Party</th><th class="px-4 py-3">Type</th><th class="px-4 py-3 text-right">Balance</th></tr>
    </thead>
    <tbody>
      <?php foreach ($duesList as $p): ?>
        <tr class="border-b border-line last:border-0">
          <td class="px-4 py-3 font-medium text-gray-100"><?= e($p['name']) ?></td>
          <td class="px-4 py-3 text-gray-500"><?= e($p['type']) ?></td>
          <td class="px-4 py-3 text-right font-semibold <?= $p['balance'] > 0 ? 'text-red-400' : 'text-brand-400' ?>"><?= e(format_money(abs($p['balance']), $currency)) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$duesList): ?>
        <tr><td colspan="3" class="px-4 py-8 text-center text-gray-500">Everyone is settled up.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
