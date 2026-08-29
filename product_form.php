<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$currentUser = require_permission_for_page('products');

$product = null;
$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = db()->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if (!$product) {
        header('Location: /products.php');
        exit;
    }
}

$flash = flash_get();
$old = $_SESSION['old_input'] ?? [];
clear_old_input();
$field = fn(string $key, $fallback = '') => $old[$key] ?? ($product[$key] ?? $fallback);

$pageTitle = $product ? 'Edit item' : 'Add new item';
require __DIR__ . '/includes/layout_header.php';
?>

<div class="mx-auto max-w-2xl rounded-2xl border border-line bg-panel overflow-hidden">
  <div class="flex items-center justify-between border-b border-line px-6 py-4">
    <h2 class="text-lg font-semibold text-white"><?= $product ? 'Edit item' : 'Add New Item' ?></h2>
    <a href="/products.php" class="text-gray-500 hover:text-gray-300 text-xl leading-none">&times;</a>
  </div>


  <form method="POST" action="/actions/<?= $product ? 'products_update.php' : 'products_create.php' ?>" class="grid grid-cols-2 gap-4 p-6">
    <?php csrf_field(); ?>
    <?php if ($product): ?><input type="hidden" name="id" value="<?= e($product['id']) ?>"><?php endif; ?>

    <div class="col-span-2">
      <label class="block text-sm font-medium text-gray-300 mb-1">Name</label>
      <input name="name" required autofocus value="<?= e((string) $field('name')) ?>"
             class="w-full rounded-lg border border-line bg-panel-2 px-3 py-2 text-gray-100 placeholder-gray-600 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-300 mb-1">Category</label>
      <input name="category" value="<?= e((string) $field('category')) ?>"
             class="w-full rounded-lg border border-line bg-panel-2 px-3 py-2 text-gray-100 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-300 mb-1">SKU</label>
      <input name="sku" value="<?= e((string) $field('sku')) ?>"
             class="w-full rounded-lg border border-line bg-panel-2 px-3 py-2 text-gray-100 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-300 mb-1">Unit</label>
      <input name="unit" value="<?= e((string) $field('unit', 'pcs')) ?>"
             class="w-full rounded-lg border border-line bg-panel-2 px-3 py-2 text-gray-100 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-300 mb-1">Low stock alert at</label>
      <input name="lowStockAt" type="number" step="any" value="<?= e((string) $field('low_stock_at', '0')) ?>"
             class="w-full rounded-lg border border-line bg-panel-2 px-3 py-2 text-gray-100 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-300 mb-1">Cost price</label>
      <input name="costPrice" type="number" step="any" value="<?= e((string) $field('cost_price', '0')) ?>"
             class="w-full rounded-lg border border-line bg-panel-2 px-3 py-2 text-gray-100 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-300 mb-1">Sell price</label>
      <input name="sellPrice" type="number" step="any" value="<?= e((string) $field('sell_price', '0')) ?>"
             class="w-full rounded-lg border border-line bg-panel-2 px-3 py-2 text-gray-100 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
    </div>
    <div class="col-span-2">
      <label class="block text-sm font-medium text-gray-300 mb-1">Current stock</label>
      <input name="stockQty" type="number" step="any" value="<?= e((string) $field('stock_qty', '0')) ?>"
             class="w-full rounded-lg border border-line bg-panel-2 px-3 py-2 text-gray-100 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
    </div>
    <div class="col-span-2 flex justify-end gap-3 pt-2 border-t border-line">
      <a href="/products.php" class="rounded-lg border border-line px-4 py-2 text-sm font-medium text-gray-300 hover:bg-panel-2">Cancel</a>
      <button type="submit" class="rounded-lg bg-brand-500 hover:bg-brand-400 px-5 py-2 text-sm font-semibold text-ink">
        <?= $product ? 'Save changes' : 'Add item' ?>
      </button>
    </div>
  </form>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
