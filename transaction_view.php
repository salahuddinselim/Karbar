<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$currentUser = require_permission_for_page('transactions');

$id = (string) ($_GET['id'] ?? '');
$stmt = db()->prepare('SELECT t.*, p.name AS party_name, p.phone AS party_phone FROM transactions t LEFT JOIN parties p ON p.id = t.party_id WHERE t.id = ?');
$stmt->execute([$id]);
$transaction = $stmt->fetch();
if (!$transaction) {
    header('Location: /transactions.php');
    exit;
}

$stmt = db()->prepare('SELECT * FROM transaction_items WHERE transaction_id = ?');
$stmt->execute([$id]);
$items = $stmt->fetchAll();

$settings = get_store_settings();
$currency = $settings['currency'];
$isSale = $transaction['type'] === 'SALE';

$lines = [
    $settings['store_name'] . ' - ' . $transaction['type'] . ' Receipt',
    'Date: ' . format_date($transaction['date']),
];
if ($transaction['party_name']) {
    $lines[] = ($isSale ? 'Customer: ' : 'Supplier: ') . $transaction['party_name'];
}
$lines[] = '';
foreach ($items as $it) {
    $lines[] = $it['name'] . ' x' . (0 + $it['qty']) . ' @ ' . format_money((float) $it['unit_price'], $currency) . ' = ' . format_money((float) $it['subtotal'], $currency);
}
$lines[] = '';
$lines[] = 'Total: ' . format_money((float) $transaction['total'], $currency);
$lines[] = 'Paid: ' . format_money((float) $transaction['paid_amount'], $currency);
if ($transaction['due_amount'] > 0) {
    $lines[] = 'Due: ' . format_money((float) $transaction['due_amount'], $currency);
}
$lines[] = '';
$lines[] = $settings['invoice_note'];
$whatsappMessage = implode("\n", array_filter($lines, fn($l) => $l !== ''));

$whatsappHref = $transaction['party_phone']
    ? 'https://wa.me/' . to_whatsapp_number($transaction['party_phone']) . '?text=' . rawurlencode($whatsappMessage)
    : 'https://wa.me/?text=' . rawurlencode($whatsappMessage);

$pageTitle = $transaction['type'] . ' receipt';
require __DIR__ . '/includes/layout_header.php';
?>

<div class="mb-4 flex items-center justify-between no-print">
  <a href="/transactions.php" class="text-sm text-brand-400 hover:underline">&larr; Back to transactions</a>
  <div class="flex gap-2">
    <a href="<?= e($whatsappHref) ?>" target="_blank" rel="noreferrer" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-500">Share via WhatsApp</a>
    <button onclick="window.print()" class="rounded-lg border border-line bg-panel px-4 py-2 text-sm font-semibold text-gray-200 hover:bg-panel-2">Print / Save as PDF</button>
  </div>
</div>

<div class="mx-auto max-w-lg rounded-xl border border-gray-200 bg-white p-8">
  <div class="mb-6 flex items-start justify-between">
    <div>
      <h1 class="text-xl font-bold text-gray-900"><?= e($settings['store_name']) ?></h1>
      <p class="text-sm text-gray-500"><?= e($settings['address']) ?></p>
      <p class="text-sm text-gray-500"><?= e($settings['phone']) ?></p>
    </div>
    <div class="text-right">
      <p class="text-lg font-semibold uppercase text-gray-700"><?= e($transaction['type']) ?></p>
      <p class="text-sm text-gray-500"><?= e(format_date($transaction['date'])) ?></p>
    </div>
  </div>

  <?php if ($transaction['party_name']): ?>
    <p class="mb-4 text-sm text-gray-700">
      <span class="font-medium"><?= $isSale ? 'Customer: ' : 'Supplier: ' ?></span>
      <?= e($transaction['party_name']) ?><?= $transaction['party_phone'] ? ' (' . e($transaction['party_phone']) . ')' : '' ?>
    </p>
  <?php endif; ?>
  <?php if ($transaction['description']): ?>
    <p class="mb-4 text-sm text-gray-700"><?= e($transaction['description']) ?></p>
  <?php endif; ?>

  <?php if ($items): ?>
    <table class="mb-4 w-full text-sm">
      <thead>
        <tr class="border-b border-gray-200 text-left text-xs uppercase text-gray-500">
          <th class="py-2">Item</th>
          <th class="py-2 text-right">Qty</th>
          <th class="py-2 text-right">Price</th>
          <th class="py-2 text-right">Amount</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $it): ?>
          <tr class="border-b border-gray-100">
            <td class="py-2"><?= e($it['name']) ?></td>
            <td class="py-2 text-right"><?= e((string) (0 + $it['qty'])) ?></td>
            <td class="py-2 text-right"><?= e(format_money((float) $it['unit_price'], $currency)) ?></td>
            <td class="py-2 text-right"><?= e(format_money((float) $it['subtotal'], $currency)) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <div class="space-y-1 border-t border-gray-200 pt-3 text-sm">
    <div class="flex justify-between">
      <span class="text-gray-600">Total</span>
      <span class="font-semibold text-gray-900"><?= e(format_money((float) $transaction['total'], $currency)) ?></span>
    </div>
    <div class="flex justify-between">
      <span class="text-gray-600">Paid (<?= e($transaction['payment_method']) ?>)</span>
      <span class="text-gray-900"><?= e(format_money((float) $transaction['paid_amount'], $currency)) ?></span>
    </div>
    <?php if ($transaction['due_amount'] > 0): ?>
      <div class="flex justify-between">
        <span class="font-medium text-red-600">Due</span>
        <span class="font-bold text-red-600"><?= e(format_money((float) $transaction['due_amount'], $currency)) ?></span>
      </div>
    <?php endif; ?>
  </div>

  <?php if ($transaction['receipt_image_path']): ?>
    <div class="mt-6 border-t border-gray-200 pt-4 no-print">
      <p class="mb-2 text-xs font-medium uppercase text-gray-500">Attached receipt</p>
      <a href="<?= e($transaction['receipt_image_path']) ?>" target="_blank" rel="noreferrer">
        <img src="<?= e($transaction['receipt_image_path']) ?>" alt="Receipt" class="max-h-64 rounded-lg border border-gray-200">
      </a>
    </div>
  <?php endif; ?>

  <?php if ($settings['invoice_note']): ?>
    <p class="mt-6 text-center text-xs text-gray-400"><?= e($settings['invoice_note']) ?></p>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
