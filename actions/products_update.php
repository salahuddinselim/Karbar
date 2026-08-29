<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

require_permission_for_action('products');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /products.php'); exit; }
csrf_verify();

function numu(string $key): float
{
    $v = trim((string) ($_POST[$key] ?? '0'));
    return is_numeric($v) ? (float) $v : 0.0;
}

$id = (string) ($_POST['id'] ?? '');
$name = trim((string) ($_POST['name'] ?? ''));
if ($id === '' || $name === '') {
    redirect_with_error('/product_form.php?id=' . urlencode($id), 'Product name is required.', $_POST);
}

$stmt = db()->prepare(
    'UPDATE products SET name=?, sku=?, unit=?, category=?, cost_price=?, sell_price=?, stock_qty=?, low_stock_at=? WHERE id=?'
);
$stmt->execute([
    $name,
    trim((string) ($_POST['sku'] ?? '')) ?: null,
    trim((string) ($_POST['unit'] ?? '')) ?: 'pcs',
    trim((string) ($_POST['category'] ?? '')) ?: null,
    numu('costPrice'),
    numu('sellPrice'),
    numu('stockQty'),
    numu('lowStockAt'),
    $id,
]);

flash_set('success', 'Product updated.');
header('Location: /products.php');
exit;
