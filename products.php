<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$currentUser = require_permission_for_page('products');
$products = db()->query('SELECT * FROM products ORDER BY name ASC')->fetchAll();
$settings = get_store_settings();
$currency = $settings['currency'];

$pageTitle = 'Inventory';
require __DIR__ . '/includes/layout_header.php';
?>

<div class="mb-6 flex items-center justify-between">
  <p class="text-sm text-gray-500"><?= count($products) ?> item<?= count($products) === 1 ? '' : 's' ?></p>
  <a href="/product_form.php" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 hover:bg-brand-400 px-4 py-2 text-sm font-semibold text-ink">+ Add New Item</a>
</div>

<?php if (!$products): ?>
  <div class="rounded-xl border border-dashed border-line bg-panel py-16 text-center">
    <p class="text-lg font-semibold text-white">Let's add your first item</p>
    <p class="mt-1 text-sm text-gray-500">Click the button above to start managing your inventory.</p>
  </div>
<?php else: ?>
<div class="overflow-x-auto rounded-xl border border-line bg-panel">
  <table class="w-full text-left text-sm">
    <thead class="border-b border-line bg-panel-2 text-xs uppercase tracking-wide text-gray-500">
      <tr>
        <th class="px-4 py-3">Name</th>
        <th class="px-4 py-3">Category</th>
        <th class="px-4 py-3">Unit</th>
        <th class="px-4 py-3 text-right">Cost</th>
        <th class="px-4 py-3 text-right">Sell price</th>
        <th class="px-4 py-3 text-right">Stock</th>
        <th class="px-4 py-3"></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($products as $p): ?>
        <tr class="border-b border-line last:border-0 hover:bg-panel-2">
          <td class="px-4 py-3 font-medium text-gray-100"><?= e($p['name']) ?></td>
          <td class="px-4 py-3 text-gray-400"><?= e($p['category'] ?? '-') ?></td>
          <td class="px-4 py-3 text-gray-400"><?= e($p['unit']) ?></td>
          <td class="px-4 py-3 text-right text-gray-400"><?= e(format_money((float) $p['cost_price'], $currency)) ?></td>
          <td class="px-4 py-3 text-right text-gray-100"><?= e(format_money((float) $p['sell_price'], $currency)) ?></td>
          <td class="px-4 py-3 text-right">
            <span class="<?= $p['stock_qty'] <= $p['low_stock_at'] ? 'font-semibold text-red-400' : 'text-gray-100' ?>">
              <?= e((string) (0 + $p['stock_qty'])) ?> <?= e($p['unit']) ?>
            </span>
          </td>
          <td class="px-4 py-3">
            <div class="flex justify-end gap-3">
              <a href="/product_form.php?id=<?= e($p['id']) ?>" class="text-sm font-medium text-brand-400 hover:underline">Edit</a>
              <form method="POST" action="/actions/products_delete.php" onsubmit="return confirm('Delete this product?');">
                <?php csrf_field(); ?>
                <input type="hidden" name="id" value="<?= e($p['id']) ?>">
                <button class="text-sm font-medium text-red-400 hover:underline">Delete</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
