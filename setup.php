<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$existingCount = (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
if ($existingCount > 0) {
    header('Location: /login.php');
    exit;
}

$flash = flash_get();
$old = $_SESSION['old_input'] ?? [];
clear_old_input();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Set up your store · <?= e(APP_NAME) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = { theme: { extend: { colors: { brand: {
    400:'#22b8ae', 500:'#0d9488', 600:'#0b7c72', 700:'#09635c'
  } } } } };
</script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
  <div class="w-full max-w-md bg-white rounded-2xl shadow-sm p-8 sm:p-10">
    <div class="flex items-center gap-2 mb-8">
      <?= app_logo_mark() ?>
      <span class="text-xl font-bold text-gray-900"><?= e(APP_NAME) ?></span>
    </div>

    <h1 class="text-2xl font-bold text-gray-900">Let's get started</h1>
    <p class="text-sm text-gray-500 mt-1 mb-6">One-time setup for the store owner account.</p>

    <?php if ($flash): ?>
      <div class="mb-4 rounded-lg px-4 py-3 text-sm bg-red-50 text-red-700 border border-red-200"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <form method="POST" action="/actions/setup.php" class="space-y-4">
      <?php csrf_field(); ?>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Store name</label>
        <input name="storeName" required value="<?= e(old_input('storeName')) ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Business category</label>
        <select name="category" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
          <option value="">Select category</option>
          <?php foreach (BUSINESS_CATEGORIES as $cat): ?>
            <option value="<?= e($cat) ?>" <?= old_input('category') === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <hr class="border-gray-200 my-2">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Your name</label>
        <input name="name" required value="<?= e(old_input('name')) ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input type="email" name="email" required value="<?= e(old_input('email')) ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
        <input type="password" name="password" required minlength="6" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
        <p class="text-xs text-gray-400 mt-1">At least 6 characters.</p>
      </div>
      <button class="w-full bg-brand-600 hover:bg-brand-700 text-white rounded-lg py-2.5 text-sm font-semibold">Create store & owner account</button>
    </form>
  </div>
</body>
</html>
