<?php
/**
 * Expects (optionally) $pageTitle to be set before include.
 * Requires $currentUser to be set (from require_login/require_permission_for_page).
 */
$storeSettings = get_store_settings();
// If the page already consumed the flash message itself (form pages that need
// to react to it before rendering), don't call flash_get() again here — a
// second call would find the session value already cleared and wipe it out.
$flash = $flash ?? flash_get();

function nav_icon(string $name): string
{
    $paths = [
        'home' => '<path d="M3 10.5 12 3l9 7.5" /><path d="M5 9.5V20a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V9.5" />',
        'swap' => '<path d="M7 3v14M7 17 3 13M7 17l4-4" /><path d="M17 21V7M17 7l4 4M17 7l-4 4" />',
        'box' => '<path d="M21 8 12 3 3 8l9 5 9-5Z" /><path d="M3 8v8l9 5 9-5V8" /><path d="M12 13v8" />',
        'users' => '<circle cx="9" cy="8" r="3.25" /><path d="M2.5 20a6.5 6.5 0 0 1 13 0" /><path d="M16 5.5a3.25 3.25 0 0 1 0 6.4" /><path d="M15.5 14a6 6 0 0 1 6 6" />',
        'chart' => '<path d="M4 20V10M11 20V4M18 20v-7" /><path d="M2.5 20.5h19" />',
        'shield' => '<path d="M12 3l7 3v5c0 4.6-3 8.4-7 10-4-1.6-7-5.4-7-10V6l7-3Z" />',
    ];
    $body = $paths[$name] ?? '';
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="w-[18px] h-[18px] shrink-0">' . $body . '</svg>';
}

$navSections = [
    'Business' => [
        ['href' => '/dashboard.php', 'label' => 'Dashboard', 'module' => null, 'icon' => 'home'],
        ['href' => '/parties.php', 'label' => 'Parties', 'module' => 'parties', 'icon' => 'users'],
        ['href' => '/products.php', 'label' => 'Inventory', 'module' => 'products', 'icon' => 'box'],
        ['href' => '/transactions.php', 'label' => 'Transactions', 'module' => 'transactions', 'icon' => 'swap'],
    ],
    'Management' => [
        ['href' => '/reports.php', 'label' => 'Reports', 'module' => 'reports', 'icon' => 'chart'],
        ['href' => '/staff.php', 'label' => 'Manage Staff', 'module' => 'staff', 'icon' => 'shield'],
    ],
];
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$avatarLetter = strtoupper(substr($currentUser['name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? 'Dashboard') ?> · <?= e(APP_NAME) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: { extend: { colors: {
      brand: { 50:'#e6fbfa', 100:'#c3f4f1', 300:'#5fd9d1', 400:'#22b8ae',
               500:'#0d9488', 600:'#0b7c72', 700:'#09635c', 900:'#04302c' },
      ink: '#0a0d12', panel: '#12151c', 'panel-2': '#1a1f29', line: '#252b38'
    } } }
  };
</script>
<link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="bg-ink text-gray-200 min-h-screen">
<div class="flex min-h-screen">
  <aside class="hidden md:flex md:flex-col w-64 bg-panel border-r border-line shrink-0">
    <div class="h-16 flex items-center gap-2 px-5 border-b border-line">
      <?= app_logo_mark() ?>
      <span class="font-semibold text-lg text-white"><?= e(APP_NAME) ?></span>
    </div>

    <div class="px-3 pt-4">
      <div class="flex items-center gap-2 rounded-xl border border-line bg-panel-2 px-3 py-2.5">
        <div class="w-7 h-7 rounded-lg bg-brand-500/20 text-brand-400 flex items-center justify-center text-sm font-bold"><?= e(substr($storeSettings['store_name'], 0, 1)) ?></div>
        <span class="text-sm font-medium text-gray-100 truncate"><?= e($storeSettings['store_name']) ?></span>
      </div>
    </div>

    <nav class="flex-1 px-3 py-4 space-y-5 overflow-y-auto">
      <?php foreach ($navSections as $sectionLabel => $navLinks): ?>
        <?php
          $visible = array_filter($navLinks, fn($it) => !$it['module'] || can_access($currentUser, $it['module']));
          if (!$visible) continue;
        ?>
        <div>
          <p class="px-3 mb-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500"><?= e($sectionLabel) ?></p>
          <div class="space-y-0.5">
            <?php foreach ($visible as $item): ?>
              <?php $active = $currentPath === $item['href']; ?>
              <a href="<?= e($item['href']) ?>"
                 class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors <?= $active ? 'bg-brand-500 text-ink' : 'text-gray-300 hover:bg-panel-2 hover:text-white' ?>">
                <?= nav_icon($item['icon']) ?>
                <?= e($item['label']) ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </nav>

    <div class="p-3 border-t border-line">
      <div class="flex items-center gap-2 px-2 mb-2">
        <div class="w-7 h-7 rounded-full bg-brand-500 text-ink flex items-center justify-center text-xs font-bold"><?= e($avatarLetter) ?></div>
        <div class="min-w-0">
          <p class="text-sm text-gray-200 truncate"><?= e($currentUser['name']) ?></p>
          <p class="text-[11px] text-gray-500"><?= e(ucfirst($currentUser['role'])) ?></p>
        </div>
      </div>
      <form method="POST" action="/actions/logout.php">
        <?php csrf_field(); ?>
        <button class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium text-gray-400 hover:bg-panel-2 hover:text-white">Log out</button>
      </form>
      <p class="mt-2 px-3 text-[10px] text-gray-600"><?= e(APP_NAME) ?> by <?= e(APP_CREATOR) ?></p>
    </div>
  </aside>

  <div class="flex-1 flex flex-col min-w-0">
    <header class="h-16 bg-panel border-b border-line flex items-center gap-4 px-4 md:px-6">
      <button class="md:hidden text-gray-400" onclick="document.getElementById('mobileNav').classList.toggle('hidden')">&#9776;</button>
      <div class="hidden md:flex flex-1 max-w-md items-center gap-2 rounded-lg border border-line bg-panel-2 px-3 py-2 text-sm text-gray-500">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-4 h-4"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        <span>Search or create anything...</span>
        <span class="ml-auto text-xs border border-line rounded px-1.5 py-0.5 text-gray-600">Ctrl+K</span>
      </div>
      <h1 class="flex-1 md:hidden text-base font-semibold text-white"><?= e($pageTitle ?? 'Dashboard') ?></h1>
      <div class="flex items-center gap-3 text-gray-400">
        <span class="hidden sm:inline text-xs rounded-full bg-panel-2 border border-line px-2 py-1"><?= e($storeSettings['currency']) ?></span>
        <div class="flex items-center gap-1.5 pl-2 border-l border-line">
          <div class="w-7 h-7 rounded-full bg-brand-500 text-ink flex items-center justify-center text-xs font-bold"><?= e($avatarLetter) ?></div>
          <span class="hidden sm:inline text-sm text-gray-200"><?= e($currentUser['name']) ?></span>
        </div>
      </div>
    </header>

    <nav id="mobileNav" class="hidden md:hidden bg-panel border-b border-line px-3 py-2 flex gap-1 overflow-x-auto">
      <?php foreach ($navSections as $navLinks): foreach ($navLinks as $item): ?>
        <?php if ($item['module'] && !can_access($currentUser, $item['module'])) continue; ?>
        <a href="<?= e($item['href']) ?>" class="whitespace-nowrap rounded-lg px-3 py-1.5 text-sm font-medium text-gray-300 hover:bg-panel-2"><?= e($item['label']) ?></a>
      <?php endforeach; endforeach; ?>
    </nav>

    <main class="flex-1 p-4 md:p-6">
      <h1 class="hidden md:block mb-5 text-xl font-bold text-white"><?= e($pageTitle ?? 'Dashboard') ?></h1>
      <?php if ($flash): ?>
        <div class="mb-4 rounded-lg px-4 py-3 text-sm border <?= $flash['type'] === 'error' ? 'bg-red-500/10 text-red-300 border-red-800' : 'bg-brand-500/10 text-brand-300 border-brand-900' ?>">
          <?= e($flash['message']) ?>
        </div>
      <?php endif; ?>
