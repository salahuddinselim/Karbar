<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

if (current_user()) {
    header('Location: /dashboard.php');
    exit;
}

$storeIsSetUp = (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0;
$settings = get_store_settings();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e(APP_NAME) ?> — Manage your business, simply</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = { theme: { extend: { colors: { brand: {
    50:'#e6fbfa', 400:'#22b8ae', 500:'#0d9488', 600:'#0b7c72', 700:'#09635c'
  } } } } };
</script>
</head>
<body class="bg-gray-50 min-h-screen">
  <header class="border-b border-gray-200 bg-white">
    <div class="mx-auto max-w-6xl px-4 py-4 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <?= app_logo_mark() ?>
        <span class="text-lg font-bold text-gray-900"><?= e(APP_NAME) ?></span>
      </div>
      <div class="flex items-center gap-3">
        <a href="/login.php" class="rounded-lg px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">Log in</a>
        <?php if ($storeIsSetUp): ?>
          <span class="hidden sm:inline text-xs text-gray-400">Store already set up</span>
        <?php else: ?>
          <a href="/setup.php" class="rounded-lg bg-brand-600 hover:bg-brand-700 px-4 py-2 text-sm font-semibold text-white">Get Started</a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <main class="mx-auto max-w-6xl px-4 py-16 md:py-24">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
      <div>
        <p class="inline-block rounded-full bg-brand-50 text-brand-700 text-xs font-semibold px-3 py-1 mb-4">Single-store business manager</p>
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 leading-tight">Run your shop's<br>accounts from one place</h1>
        <p class="mt-5 text-lg text-gray-600 max-w-md">
          Track sales, purchases, and expenses. Keep a running "khata" ledger of what
          customers owe you and what you owe suppliers. See profit & loss whenever you need it.
        </p>
        <div class="mt-8 flex flex-wrap gap-3">
          <?php if ($storeIsSetUp): ?>
            <a href="/login.php" class="rounded-lg bg-brand-600 hover:bg-brand-700 px-6 py-3 text-sm font-semibold text-white">Log in to your store</a>
          <?php else: ?>
            <a href="/setup.php" class="rounded-lg bg-brand-600 hover:bg-brand-700 px-6 py-3 text-sm font-semibold text-white">Get started — it's free</a>
            <a href="/login.php" class="rounded-lg border border-gray-300 px-6 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">Log in</a>
          <?php endif; ?>
        </div>
      </div>

      <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-6">
        <div class="flex items-center gap-2 mb-4">
          <div class="w-6 h-6 rounded bg-brand-600"></div>
          <div class="h-2.5 w-24 rounded bg-gray-200"></div>
        </div>
        <div class="grid grid-cols-2 gap-3 mb-4">
          <div class="rounded-lg bg-brand-50 p-3">
            <div class="h-2 w-14 rounded bg-brand-200 mb-2"></div>
            <div class="h-4 w-20 rounded bg-brand-300"></div>
          </div>
          <div class="rounded-lg bg-red-50 p-3">
            <div class="h-2 w-14 rounded bg-red-200 mb-2"></div>
            <div class="h-4 w-20 rounded bg-red-300"></div>
          </div>
        </div>
        <div class="flex items-end gap-1.5 h-24 mb-2">
          <?php foreach ([35, 60, 25, 75, 45, 65, 30, 55] as $h): ?>
            <div class="flex-1 rounded bg-gray-200" style="height: <?= $h ?>%"></div>
          <?php endforeach; ?>
        </div>
        <div class="space-y-2">
          <div class="h-2 w-full rounded bg-gray-100"></div>
          <div class="h-2 w-3/4 rounded bg-gray-100"></div>
        </div>
      </div>
    </div>

    <div class="mt-24 grid grid-cols-1 sm:grid-cols-3 gap-6">
      <div class="rounded-xl border border-gray-200 bg-white p-5">
        <h3 class="font-semibold text-gray-900 mb-1">Sales, purchase & expense</h3>
        <p class="text-sm text-gray-500">Log every transaction as it happens, with line items tied to your inventory.</p>
      </div>
      <div class="rounded-xl border border-gray-200 bg-white p-5">
        <h3 class="font-semibold text-gray-900 mb-1">Customer & supplier ledger</h3>
        <p class="text-sm text-gray-500">Know exactly who owes you and who you owe, with a WhatsApp reminder one tap away.</p>
      </div>
      <div class="rounded-xl border border-gray-200 bg-white p-5">
        <h3 class="font-semibold text-gray-900 mb-1">Profit & loss reports</h3>
        <p class="text-sm text-gray-500">Revenue, cost of goods sold, and net profit for any date range.</p>
      </div>
    </div>
  </main>

  <footer class="border-t border-gray-200 py-6">
    <p class="text-center text-xs text-gray-400">&copy; <?= date('Y') ?> <?= e(APP_NAME) ?> &middot; Built by <?= e(APP_CREATOR) ?></p>
  </footer>
</body>
</html>
