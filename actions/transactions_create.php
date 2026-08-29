<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

require_permission_for_action('transactions');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /transactions.php'); exit; }
csrf_verify();

$back = '/transaction_form.php';

$type = (string) ($_POST['type'] ?? '');
if (!in_array($type, ['SALE', 'PURCHASE', 'EXPENSE'], true)) {
    redirect_with_error($back, 'Invalid transaction type.');
}

$partyId = trim((string) ($_POST['partyId'] ?? '')) ?: null;
$description = trim((string) ($_POST['description'] ?? '')) ?: null;
$paymentMethod = trim((string) ($_POST['paymentMethod'] ?? 'cash')) ?: 'cash';
$dateStr = (string) ($_POST['date'] ?? '');
$date = $dateStr !== '' ? date('Y-m-d H:i:s', strtotime($dateStr)) : date('Y-m-d H:i:s');

$items = [];
$itemsRaw = (string) ($_POST['items'] ?? '[]');
$decoded = json_decode($itemsRaw, true);
if (!is_array($decoded)) {
    redirect_with_error($back, 'Invalid line items.');
}
$items = $decoded;

$subtotal = 0.0;
$total = 0.0;

if ($type === 'EXPENSE') {
    $total = (float) ($_POST['expenseAmount'] ?? 0);
    if (!is_finite($total) || $total <= 0) {
        redirect_with_error($back, 'Enter a valid expense amount.');
    }
    $subtotal = $total;
} else {
    if (empty($items)) {
        redirect_with_error($back, 'Add at least one line item.');
    }
    foreach ($items as $it) {
        $name = trim((string) ($it['name'] ?? ''));
        $qty = (float) ($it['qty'] ?? 0);
        $unitPrice = (float) ($it['unitPrice'] ?? 0);
        if ($name === '' || !is_finite($qty) || $qty <= 0 || !is_finite($unitPrice)) {
            redirect_with_error($back, 'Each line item needs a name, quantity, and unit price.');
        }
        $subtotal += $qty * $unitPrice;
    }
    $total = $subtotal;
}

$paidAmountRaw = trim((string) ($_POST['paidAmount'] ?? ''));
$paidAmount = $paidAmountRaw === '' ? $total : (float) $paidAmountRaw;
if (!is_finite($paidAmount) || $paidAmount < 0) $paidAmount = 0.0;
if ($paidAmount > $total) $paidAmount = $total;
$dueAmount = max(0.0, $total - $paidAmount);

if ($dueAmount > 0 && !$partyId) {
    redirect_with_error($back, 'Select a customer/supplier to track a due balance.');
}

try {
    $receiptImagePath = save_receipt_image($_FILES['receipt'] ?? null);
} catch (RuntimeException $e) {
    redirect_with_error($back, $e->getMessage());
}

$pdo = db();
$pdo->beginTransaction();
try {
    $transactionId = uuid();
    $stmt = $pdo->prepare(
        'INSERT INTO transactions (id, type, date, party_id, description, subtotal, total, paid_amount, due_amount, payment_method, receipt_image_path)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$transactionId, $type, $date, $partyId, $description, $subtotal, $total, $paidAmount, $dueAmount, $paymentMethod, $receiptImagePath]);

    if ($type !== 'EXPENSE') {
        $itemStmt = $pdo->prepare(
            'INSERT INTO transaction_items (id, transaction_id, product_id, name, qty, unit_price, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $incStmt = $pdo->prepare('UPDATE products SET stock_qty = stock_qty + ? WHERE id = ?');

        foreach ($items as $it) {
            $productId = trim((string) ($it['productId'] ?? '')) ?: null;
            $name = trim((string) $it['name']);
            $qty = (float) $it['qty'];
            $unitPrice = (float) $it['unitPrice'];
            $itemStmt->execute([uuid(), $transactionId, $productId, $name, $qty, $unitPrice, $qty * $unitPrice]);

            if ($productId) {
                $delta = $type === 'SALE' ? -$qty : $qty;
                $incStmt->execute([$delta, $productId]);
            }
        }
    }

    if ($dueAmount > 0 && $partyId) {
        $note = ($type === 'SALE' ? 'Sale' : 'Purchase') . ' on credit';
        $stmt = $pdo->prepare('INSERT INTO ledger_entries (id, party_id, transaction_id, debit, note) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([uuid(), $partyId, $transactionId, $dueAmount, $note]);
    }

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    error_log('Transaction create failed: ' . $e->getMessage());
    redirect_with_error($back, 'Could not save transaction. Please try again.');
}

header('Location: /transaction_view.php?id=' . urlencode($transactionId));
exit;
