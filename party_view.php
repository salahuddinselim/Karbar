<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$currentUser = require_permission_for_page('parties');

$id = (string) ($_GET['id'] ?? '');
$stmt = db()->prepare('SELECT * FROM parties WHERE id = ?');
$stmt->execute([$id]);
$party = $stmt->fetch();
if (!$party) {
    header('Location: /parties.php');
    exit;
}

$balance = get_party_balance($id);
$settings = get_store_settings();
$currency = $settings['currency'];

$stmt = db()->prepare(
    'SELECT le.*, t.type AS tx_type FROM ledger_entries le
     LEFT JOIN transactions t ON t.id = le.transaction_id
     WHERE le.party_id = ? ORDER BY le.date DESC'
);
$stmt->execute([$id]);
$ledgerEntries = $stmt->fetchAll();

$isCustomer = $party['type'] === 'CUSTOMER';
$balanceLabel = $isCustomer ? 'owes you' : 'you owe them';
$defaultDirection = $isCustomer ? 'IN' : 'OUT';

$whatsappHref = null;
if ($party['phone']) {
    $text = "Hi {$party['name']}, this is a reminder from {$settings['store_name']}. Your outstanding balance is "
        . format_money(abs($balance), $currency) . '. Please clear it at your earliest convenience. Thank you!';
    $whatsappHref = 'https://wa.me/' . to_whatsapp_number($party['phone']) . '?text=' . rawurlencode($text);
}

$flash = flash_get();

$pageTitle = $party['name'];
require __DIR__ . '/includes/layout_header.php';
?>

<a href="/parties.php" class="text-sm text-brand-400 hover:underline">&larr; Back to parties</a>

<div class="mt-3 mb-6 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-line bg-panel p-5">
  <div>
    <h2 class="text-xl font-bold text-white"><?= e($party['name']) ?></h2>
    <p class="text-sm text-gray-500"><?= $isCustomer ? 'Customer' : 'Supplier' ?> &middot; <?= e($party['phone'] ?? 'no phone') ?></p>
  </div>
  <div class="text-right">
    <p class="text-xs uppercase tracking-wide text-gray-500">Balance (<?= e($balanceLabel) ?>)</p>
    <p class="text-2xl font-bold <?= $balance > 0 ? 'text-red-400' : ($balance < 0 ? 'text-brand-400' : 'text-gray-400') ?>">
      <?= e(format_money(abs($balance), $currency)) ?>
    </p>
  </div>
</div>

<?php if ($balance != 0 && $whatsappHref): ?>
  <a href="<?= e($whatsappHref) ?>" target="_blank" rel="noreferrer"
     class="mb-6 inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-500">
    Send WhatsApp payment reminder
  </a>
<?php endif; ?>

<div class="mb-8 rounded-xl border border-line bg-panel p-4">
  <h2 class="mb-3 text-sm font-semibold text-gray-300">Record a payment</h2>
  <form method="POST" action="/actions/parties_payment.php" class="flex flex-wrap items-end gap-3">
    <?php csrf_field(); ?>
    <input type="hidden" name="partyId" value="<?= e($party['id']) ?>">
    <div>
      <label class="block text-xs font-medium text-gray-500 mb-1">Direction</label>
      <select name="direction" class="rounded-lg border border-line bg-panel-2 px-3 py-2 text-sm text-gray-100 focus:border-brand-500 focus:outline-none">
        <option value="IN" <?= $defaultDirection === 'IN' ? 'selected' : '' ?>>Received money</option>
        <option value="OUT" <?= $defaultDirection === 'OUT' ? 'selected' : '' ?>>Paid money</option>
      </select>
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-500 mb-1">Amount</label>
      <input name="amount" type="number" step="any" required class="w-32 rounded-lg border border-line bg-panel-2 px-3 py-2 text-sm text-gray-100 focus:border-brand-500 focus:outline-none">
    </div>
    <div class="flex-1 min-w-[10rem]">
      <label class="block text-xs font-medium text-gray-500 mb-1">Note</label>
      <input name="note" class="w-full rounded-lg border border-line bg-panel-2 px-3 py-2 text-sm text-gray-100 focus:border-brand-500 focus:outline-none">
    </div>
    <button type="submit" class="rounded-lg bg-brand-500 hover:bg-brand-400 px-4 py-2 text-sm font-semibold text-ink">Record payment</button>
  </form>
</div>

<h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-400">Ledger history</h2>
<div class="overflow-hidden rounded-xl border border-line bg-panel">
  <table class="w-full text-left text-sm">
    <thead class="border-b border-line bg-panel-2 text-xs uppercase tracking-wide text-gray-500">
      <tr>
        <th class="px-4 py-3">Date</th>
        <th class="px-4 py-3">Note</th>
        <th class="px-4 py-3 text-right">Debit</th>
        <th class="px-4 py-3 text-right">Credit</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($ledgerEntries as $le): ?>
        <tr class="border-b border-line last:border-0">
          <td class="px-4 py-3 text-gray-500"><?= e(format_date($le['date'])) ?></td>
          <td class="px-4 py-3 text-gray-100"><?= e($le['note'] ?? ($le['tx_type'] ? $le['tx_type'] . ' transaction' : 'Ledger entry')) ?></td>
          <td class="px-4 py-3 text-right text-red-400"><?= $le['debit'] > 0 ? e(format_money((float) $le['debit'], $currency)) : '-' ?></td>
          <td class="px-4 py-3 text-right text-brand-400"><?= $le['credit'] > 0 ? e(format_money((float) $le['credit'], $currency)) : '-' ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$ledgerEntries): ?>
        <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No ledger activity yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
