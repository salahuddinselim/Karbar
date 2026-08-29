<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$currentUser = require_login();
require_owner($currentUser);

$staff = db()->query("SELECT * FROM users WHERE role = 'staff' ORDER BY name ASC")->fetchAll();
$flash = flash_get();

$perms = [
    ['key' => 'can_manage_products', 'label' => 'Products'],
    ['key' => 'can_manage_parties', 'label' => 'Customers/Suppliers'],
    ['key' => 'can_record_transactions', 'label' => 'Transactions'],
    ['key' => 'can_view_reports', 'label' => 'Reports'],
];

$pageTitle = 'Manage Staff';
require __DIR__ . '/includes/layout_header.php';
?>

<div class="mb-6 flex items-center justify-between">
  <p class="text-sm text-gray-500">Owner: <?= e($currentUser['name']) ?></p>
  <a href="/staff_form.php" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 hover:bg-brand-400 px-4 py-2 text-sm font-semibold text-ink">+ Add staff</a>
</div>

<div class="space-y-4">
  <?php foreach ($staff as $s): ?>
    <div class="rounded-xl border border-line bg-panel p-4">
      <div class="mb-3 flex items-center justify-between">
        <div>
          <p class="font-semibold text-gray-100"><?= e($s['name']) ?></p>
          <p class="text-sm text-gray-500"><?= e($s['email']) ?></p>
        </div>
        <form method="POST" action="/actions/staff_delete.php" onsubmit="return confirm('Remove this staff account?');">
          <?php csrf_field(); ?>
          <input type="hidden" name="id" value="<?= e($s['id']) ?>">
          <button class="text-sm font-medium text-red-400 hover:underline">Remove</button>
        </form>
      </div>
      <form method="POST" action="/actions/staff_update.php" class="flex flex-wrap items-center gap-4">
        <?php csrf_field(); ?>
        <input type="hidden" name="id" value="<?= e($s['id']) ?>">
        <?php foreach ($perms as $p): ?>
          <label class="flex items-center gap-2 text-sm text-gray-300">
            <input type="checkbox" name="<?= e($p['key']) ?>" <?= $s[$p['key']] ? 'checked' : '' ?> class="h-4 w-4 rounded border-line bg-panel-2 text-brand-500 focus:ring-brand-500">
            <?= e($p['label']) ?>
          </label>
        <?php endforeach; ?>
        <button type="submit" class="rounded-lg border border-line px-3 py-1.5 text-sm font-medium text-gray-300 hover:bg-panel-2">Save permissions</button>
      </form>
    </div>
  <?php endforeach; ?>
  <?php if (!$staff): ?>
    <p class="rounded-xl border border-dashed border-line p-8 text-center text-gray-500">No staff accounts yet. Add one to give employees limited access.</p>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
