<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$currentUser = require_permission_for_page('parties');
$parties = db()->query('SELECT * FROM parties ORDER BY name ASC')->fetchAll();
$balances = get_all_party_balances();
$settings = get_store_settings();
$currency = $settings['currency'];

$customers = array_values(array_filter($parties, fn($p) => $p['type'] === 'CUSTOMER'));
$suppliers = array_values(array_filter($parties, fn($p) => $p['type'] === 'SUPPLIER'));

function render_party_group(string $title, string $subtitle, array $parties, array $balances, string $currency): void
{
    ?>
    <div>
      <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400"><?= e($title) ?></h2>
      <p class="mb-3 text-xs text-gray-600"><?= e($subtitle) ?></p>
      <div class="overflow-hidden rounded-xl border border-line bg-panel">
        <table class="w-full text-left text-sm">
          <thead class="border-b border-line bg-panel-2 text-xs uppercase tracking-wide text-gray-500">
            <tr>
              <th class="px-4 py-3">Name</th>
              <th class="px-4 py-3">Phone</th>
              <th class="px-4 py-3 text-right">Balance</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($parties as $p): ?>
              <?php $balance = $balances[$p['id']] ?? 0.0; ?>
              <tr class="border-b border-line last:border-0 hover:bg-panel-2">
                <td class="px-4 py-3">
                  <a href="/party_view.php?id=<?= e($p['id']) ?>" class="font-medium text-brand-400 hover:underline"><?= e($p['name']) ?></a>
                </td>
                <td class="px-4 py-3 text-gray-400"><?= e($p['phone'] ?? '-') ?></td>
                <td class="px-4 py-3 text-right font-semibold <?= $balance > 0 ? 'text-red-400' : ($balance < 0 ? 'text-brand-400' : 'text-gray-500') ?>">
                  <?= e(format_money(abs($balance), $currency)) ?>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$parties): ?>
              <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">None yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php
}

$pageTitle = 'Parties';
require __DIR__ . '/includes/layout_header.php';
?>

<div class="mb-6 flex items-center justify-end">
  <a href="/party_form.php" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 hover:bg-brand-400 px-4 py-2 text-sm font-semibold text-ink">+ Add party</a>
</div>

<div class="space-y-8">
  <?php render_party_group('Customers', 'Positive balance = they owe you', $customers, $balances, $currency); ?>
  <?php render_party_group('Suppliers', 'Positive balance = you owe them', $suppliers, $balances, $currency); ?>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
