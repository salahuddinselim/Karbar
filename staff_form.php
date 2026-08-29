<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$currentUser = require_login();
require_owner($currentUser);

$flash = flash_get();
$old = $_SESSION['old_input'] ?? [];
clear_old_input();

$perms = [
    ['key' => 'canManageProducts', 'label' => 'Manage products & inventory'],
    ['key' => 'canManageParties', 'label' => 'Manage customers & suppliers'],
    ['key' => 'canRecordTransactions', 'label' => 'Record sales/purchases/expenses'],
    ['key' => 'canViewReports', 'label' => 'View reports'],
];

$pageTitle = 'Add staff';
require __DIR__ . '/includes/layout_header.php';
?>

<div class="mx-auto max-w-lg rounded-2xl border border-line bg-panel overflow-hidden">
  <div class="flex items-center justify-between border-b border-line px-6 py-4">
    <h2 class="text-lg font-semibold text-white">Add Staff Account</h2>
    <a href="/staff.php" class="text-gray-500 hover:text-gray-300 text-xl leading-none">&times;</a>
  </div>

  <form method="POST" action="/actions/staff_create.php" class="space-y-4 p-6">
    <?php csrf_field(); ?>
    <div>
      <label class="block text-sm font-medium text-gray-300 mb-1">Name</label>
      <input name="name" required autofocus value="<?= e($old['name'] ?? '') ?>"
             class="w-full rounded-lg border border-line bg-panel-2 px-3 py-2 text-gray-100 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-300 mb-1">Email</label>
      <input name="email" type="email" required value="<?= e($old['email'] ?? '') ?>"
             class="w-full rounded-lg border border-line bg-panel-2 px-3 py-2 text-gray-100 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-300 mb-1">Password</label>
      <input name="password" type="password" required minlength="6"
             class="w-full rounded-lg border border-line bg-panel-2 px-3 py-2 text-gray-100 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
    </div>
    <div>
      <p class="mb-2 text-sm font-medium text-gray-300">Permissions</p>
      <div class="space-y-2">
        <?php foreach ($perms as $p): ?>
          <label class="flex items-center gap-2 text-sm text-gray-300">
            <input type="checkbox" name="<?= e($p['key']) ?>" checked class="h-4 w-4 rounded border-line bg-panel-2 text-brand-500 focus:ring-brand-500">
            <?= e($p['label']) ?>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="flex justify-end gap-3 pt-2 border-t border-line">
      <a href="/staff.php" class="rounded-lg border border-line px-4 py-2 text-sm font-medium text-gray-300 hover:bg-panel-2">Cancel</a>
      <button type="submit" class="rounded-lg bg-brand-500 hover:bg-brand-400 px-4 py-2 text-sm font-semibold text-ink">Add staff account</button>
    </div>
  </form>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
