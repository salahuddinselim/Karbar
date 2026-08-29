<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

require_permission_for_action('products');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /products.php'); exit; }
csrf_verify();

$id = (string) ($_POST['id'] ?? '');
if ($id !== '') {
    $stmt = db()->prepare('DELETE FROM products WHERE id = ?');
    $stmt->execute([$id]);
}

flash_set('success', 'Product deleted.');
header('Location: /products.php');
exit;
