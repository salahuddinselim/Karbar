<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

require_permission_for_action('products');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /products.php'); exit; }
csrf_verify();

function num(string $key): float
{
    $v = trim((string) ($_POST[$key] ?? '0'));
    return is_numeric($v) ? (float) $v : 0.0;
}

$name = trim((string) ($_POST['name'] ?? ''));
$old = $_POST;
if ($name === '') {
    redirect_with_error('/product_form.php', 'Product name is required.', $old);
}

$stmt = db()->prepare(
    'INSERT INTO products (id, name, sku, unit, category, cost_price, sell_price, stock_qty, low_stock_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
);
$stmt->execute([
    uuid(),
    $name,
    trim((string) ($_POST['sku'] ?? '')) ?: null,
    trim((string) ($_POST['unit'] ?? '')) ?: 'pcs',
    trim((string) ($_POST['category'] ?? '')) ?: null,
    num('costPrice'),
    num('sellPrice'),
    num('stockQty'),
    num('lowStockAt'),
]);

flash_set('success', 'Product added.');
header('Location: /products.php');
exit;
