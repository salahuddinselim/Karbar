<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$currentUser = require_permission_for_page('parties');
$flash = flash_get();
$old = $_SESSION['old_input'] ?? [];
clear_old_input();

$pageTitle = 'Add party';
require __DIR__ . '/includes/layout_header.php';
?>

<div class="mx-auto max-w-lg rounded-2xl border border-line bg-panel overflow-hidden">
  <div class="flex items-center justify-between border-b border-line px-6 py-4">
    <h2 class="text-lg font-semibold text-white">Add New Party</h2>
    <a href="/parties.php" class="text-gray-500 hover:text-gray-300 text-xl leading-none">&times;</a>
  </div>

  <form method="POST" action="/actions/parties_create.php" class="space-y-4 p-6">
    <?php csrf_field(); ?>
    <div>
      <label class="block text-sm font-medium text-gray-300 mb-1">Full Name</label>
      <input name="name" required autofocus placeholder="Enter the name of party" value="<?= e($old['name'] ?? '') ?>"
             class="w-full rounded-lg border border-line bg-panel-2 px-3 py-2 text-gray-100 placeholder-gray-600 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-300 mb-1">Phone Number</label>
      <input name="phone" placeholder="Enter party phone no" value="<?= e($old['phone'] ?? '') ?>"
             class="w-full rounded-lg border border-line bg-panel-2 px-3 py-2 text-gray-100 placeholder-gray-600 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-300 mb-2">Party Type</label>
      <div class="flex gap-2">
        <?php $type = $old['type'] ?? 'CUSTOMER'; ?>
        <label class="flex-1">
          <input type="radio" name="type" value="CUSTOMER" class="peer sr-only" <?= $type === 'CUSTOMER' ? 'checked' : '' ?>>
          <span class="block text-center rounded-lg border border-line px-3 py-2 text-sm text-gray-300 peer-checked:bg-brand-500 peer-checked:text-ink peer-checked:border-brand-500 peer-focus-visible:ring-2 peer-focus-visible:ring-brand-400 peer-focus-visible:ring-offset-2 peer-focus-visible:ring-offset-panel cursor-pointer">Customer</span>
        </label>
        <label class="flex-1">
          <input type="radio" name="type" value="SUPPLIER" class="peer sr-only" <?= $type === 'SUPPLIER' ? 'checked' : '' ?>>
          <span class="block text-center rounded-lg border border-line px-3 py-2 text-sm text-gray-300 peer-checked:bg-brand-500 peer-checked:text-ink peer-checked:border-brand-500 peer-focus-visible:ring-2 peer-focus-visible:ring-brand-400 peer-focus-visible:ring-offset-2 peer-focus-visible:ring-offset-panel cursor-pointer">Supplier</span>
        </label>
      </div>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-300 mb-1">Opening Balance</label>
      <div class="flex items-center rounded-lg border border-line bg-panel-2 focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500">
        <span class="pl-3 text-gray-500 text-sm"><?= e($storeSettings['currency'] === 'BDT' ? 'Tk.' : $storeSettings['currency']) ?></span>
        <input name="openingBalance" type="number" step="any" placeholder="0" value="<?= e($old['openingBalance'] ?? '0') ?>"
               class="w-full bg-transparent px-2 py-2 text-gray-100 focus:outline-none">
      </div>
      <p class="mt-1 text-xs text-gray-600">For customers: what they already owe you. For suppliers: what you already owe them.</p>
    </div>
    <div class="flex justify-end gap-3 pt-2 border-t border-line">
      <a href="/parties.php" class="rounded-lg border border-line px-4 py-2 text-sm font-medium text-gray-300 hover:bg-panel-2">Cancel</a>
      <button type="submit" class="rounded-lg bg-brand-500 hover:bg-brand-400 px-5 py-2 text-sm font-semibold text-ink">Save Party</button>
    </div>
  </form>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
