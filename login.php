<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

if (current_user()) {
    header('Location: /dashboard.php');
    exit;
}

$existingCount = (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
if ($existingCount === 0) {
    header('Location: /setup.php');
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
<title>Log in · <?= e(APP_NAME) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = { theme: { extend: { colors: { brand: {
    400:'#22b8ae', 500:'#0d9488', 600:'#0b7c72', 700:'#09635c'
  } } } } };
</script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
  <div class="w-full max-w-4xl grid grid-cols-1 md:grid-cols-2 bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="p-8 sm:p-10 flex flex-col justify-center">
      <div class="flex items-center gap-2 mb-10">
        <?= app_logo_mark() ?>
        <span class="text-xl font-bold text-gray-900"><?= e(APP_NAME) ?></span>
      </div>

      <h1 class="text-2xl font-bold text-gray-900">Welcome back</h1>
      <p class="text-sm text-gray-500 mt-1 mb-6">Please log in to continue.</p>

      <?php if ($flash): ?>
        <div class="mb-4 rounded-lg px-4 py-3 text-sm bg-red-50 text-red-700 border border-red-200"><?= e($flash['message']) ?></div>
      <?php endif; ?>

      <form method="POST" action="/actions/login.php" class="space-y-4">
        <?php csrf_field(); ?>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input type="email" name="email" required value="<?= e(old_input('email')) ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
          <input type="password" name="password" required class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
        </div>
        <button class="w-full bg-brand-600 hover:bg-brand-700 text-white rounded-lg py-2.5 text-sm font-semibold">Continue</button>
      </form>
    </div>

    <div class="hidden md:flex flex-col items-center justify-center bg-gray-50 p-10 border-l border-gray-100">
      <div class="w-full max-w-xs rounded-xl border border-gray-200 bg-white shadow-sm p-4">
        <div class="flex items-center gap-2 mb-3">
          <div class="w-5 h-5 rounded bg-brand-600"></div>
          <div class="h-2 w-16 rounded bg-gray-200"></div>
        </div>
        <div class="grid grid-cols-2 gap-2 mb-3">
          <div class="h-10 rounded-lg bg-brand-50"></div>
          <div class="h-10 rounded-lg bg-red-50"></div>
        </div>
        <div class="flex items-end gap-1 h-16">
          <?php foreach ([40, 65, 30, 80, 50, 70, 35] as $h): ?>
            <div class="flex-1 rounded bg-gray-200" style="height: <?= $h ?>%"></div>
          <?php endforeach; ?>
        </div>
      </div>
      <h2 class="mt-6 text-lg font-bold text-gray-900 text-center">Manage Business From Anywhere</h2>
      <p class="mt-1 text-sm text-gray-500 text-center max-w-xs">Track sales, inventory, and customer dues from a single dashboard.</p>
    </div>
  </div>
</body>
</html>
