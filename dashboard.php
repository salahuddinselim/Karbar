<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$currentUser = require_login();
$settings = get_store_settings();
$currency = $settings['currency'];

$todayStart = date('Y-m-d 00:00:00');

$stmt = db()->prepare('SELECT COALESCE(SUM(total),0) AS total, COUNT(*) AS cnt FROM transactions WHERE type = "SALE" AND date >= ?');
$stmt->execute([$todayStart]);
$todaySales = $stmt->fetch();

$stmt = db()->prepare('SELECT COALESCE(SUM(total),0) AS total FROM transactions WHERE type = "PURCHASE" AND date >= ?');
$stmt->execute([$todayStart]);
$todayPurchases = $stmt->fetch();

$stmt = db()->prepare('SELECT COALESCE(SUM(total),0) AS total FROM transactions WHERE type = "EXPENSE" AND date >= ?');
$stmt->execute([$todayStart]);
$todayExpenses = $stmt->fetch();

$lowStock = db()->query('SELECT * FROM products WHERE stock_qty <= low_stock_at ORDER BY name LIMIT 5')->fetchAll();

$recent = db()->query(
    'SELECT t.*, p.name AS party_name FROM transactions t LEFT JOIN parties p ON p.id = t.party_id
     ORDER BY t.date DESC LIMIT 6'
)->fetchAll();

$balances = get_all_party_balances();
$parties = db()->query('SELECT id, type FROM parties')->fetchAll();
$receivable = 0.0;
$payable = 0.0;
foreach ($parties as $p) {
    $bal = $balances[$p['id']] ?? 0.0;
    if ($p['type'] === 'CUSTOMER' && $bal > 0) $receivable += $bal;
    if ($p['type'] === 'SUPPLIER' && $bal > 0) $payable += $bal;
}

$pageTitle = 'Dashboard';
require __DIR__ . '/includes/layout_header.php';
?>

<div class="mb-6 flex items-center justify-end">
  <?php if (can_access($currentUser, 'transactions')): ?>
    <a href="/transaction_form.php" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 hover:bg-brand-400 px-4 py-2 text-sm font-semibold text-ink">+ New transaction</a>
  <?php endif; ?>
</div>

<div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4">
  <div class="rounded-xl border border-line bg-panel p-4">
    <p class="text-xs uppercase tracking-wide text-gray-500">Today's sales</p>
    <p class="mt-1 text-xl font-bold text-white"><?= e(format_money((float) $todaySales['total'], $currency)) ?></p>
    <p class="mt-0.5 text-xs text-gray-500"><?= (int) $todaySales['cnt'] ?> sale(s)</p>
  </div>
  <div class="rounded-xl border border-line bg-panel p-4">
    <p class="text-xs uppercase tracking-wide text-gray-500">Today's purchases</p>
    <p class="mt-1 text-xl font-bold text-white"><?= e(format_money((float) $todayPurchases['total'], $currency)) ?></p>
  </div>
  <div class="rounded-xl border border-line bg-panel p-4">
    <p class="text-xs uppercase tracking-wide text-gray-500">Today's expenses</p>
    <p class="mt-1 text-xl font-bold text-white"><?= e(format_money((float) $todayExpenses['total'], $currency)) ?></p>
  </div>
  <div class="rounded-xl border border-line bg-panel p-4">
    <p class="text-xs uppercase tracking-wide text-gray-500">Low stock items</p>
    <p class="mt-1 text-xl font-bold <?= count($lowStock) > 0 ? 'text-amber-400' : 'text-white' ?>"><?= count($lowStock) ?></p>
  </div>
</div>

<div class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2">
  <div class="rounded-xl border border-line bg-panel p-4">
    <p class="text-xs uppercase tracking-wide text-gray-500">Receivable (customers owe you)</p>
    <p class="mt-1 text-3xl font-bold text-red-400"><?= e(format_money($receivable, $currency)) ?></p>
  </div>
  <div class="rounded-xl border border-line bg-panel p-4">
    <p class="text-xs uppercase tracking-wide text-gray-500">Payable (you owe suppliers)</p>
    <p class="mt-1 text-3xl font-bold text-amber-400"><?= e(format_money($payable, $currency)) ?></p>
  </div>
</div>

<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
  <div>
    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-400">Recent transactions</h2>
    <div class="overflow-hidden rounded-xl border border-line bg-panel">
      <ul class="divide-y divide-line">
        <?php foreach ($recent as $t): ?>
          <li>
            <a href="/transaction_view.php?id=<?= e($t['id']) ?>" class="flex items-center justify-between px-4 py-3 text-sm hover:bg-panel-2">
              <span>
                <span class="font-medium text-gray-100"><?= e($t['type']) ?></span>
                <span class="ml-2 text-gray-500"><?= e($t['party_name'] ?? format_date($t['date'])) ?></span>
              </span>
              <span class="font-semibold text-gray-100"><?= e(format_money((float) $t['total'], $currency)) ?></span>
            </a>
          </li>
        <?php endforeach; ?>
        <?php if (!$recent): ?>
          <li class="px-4 py-8 text-center text-sm text-gray-500">No transactions yet.</li>
        <?php endif; ?>
      </ul>
    </div>
  </div>

  <div>
    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-400">Low stock</h2>
    <div class="overflow-hidden rounded-xl border border-line bg-panel">
      <ul class="divide-y divide-line">
        <?php foreach ($lowStock as $p): ?>
          <li class="flex items-center justify-between px-4 py-3 text-sm">
            <span class="font-medium text-gray-100"><?= e($p['name']) ?></span>
            <span class="font-semibold text-red-400"><?= e((string) (0 + $p['stock_qty'])) ?> <?= e($p['unit']) ?></span>
          </li>
        <?php endforeach; ?>
        <?php if (!$lowStock): ?>
          <li class="px-4 py-8 text-center text-sm text-gray-500">All stocked up.</li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
