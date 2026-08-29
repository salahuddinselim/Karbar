<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

require_permission_for_action('parties');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /parties.php'); exit; }
csrf_verify();

$partyId = (string) ($_POST['partyId'] ?? '');
$amountRaw = trim((string) ($_POST['amount'] ?? '0'));
$amount = is_numeric($amountRaw) ? (float) $amountRaw : 0.0;
$direction = (string) ($_POST['direction'] ?? 'IN');
$note = trim((string) ($_POST['note'] ?? '')) ?: null;

$back = '/party_view.php?id=' . urlencode($partyId);

if ($partyId === '') {
    redirect_with_error('/parties.php', 'Missing party.');
}
$stmt = db()->prepare('SELECT id FROM parties WHERE id = ?');
$stmt->execute([$partyId]);
if (!$stmt->fetch()) {
    redirect_with_error('/parties.php', 'Party not found.');
}
if ($amount <= 0) {
    redirect_with_error($back, 'Enter a valid amount.');
}
if (!in_array($direction, ['IN', 'OUT'], true)) {
    redirect_with_error($back, 'Invalid direction.');
}

$pdo = db();
$pdo->beginTransaction();
try {
    $paymentId = uuid();
    $stmt = $pdo->prepare('INSERT INTO payments (id, party_id, amount, direction, note) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$paymentId, $partyId, $amount, $direction, $note]);

    // IN = we received money (balance owed to us decreases -> credit).
    // OUT = we paid money (balance we owe decreases -> credit as well).
    $ledgerNote = $note ?? ($direction === 'IN' ? 'Payment received' : 'Payment made');
    $stmt = $pdo->prepare('INSERT INTO ledger_entries (id, party_id, payment_id, credit, note) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([uuid(), $partyId, $paymentId, $amount, $ledgerNote]);

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    error_log('Payment record failed: ' . $e->getMessage());
    redirect_with_error($back, 'Could not record payment. Please try again.');
}

flash_set('success', 'Payment recorded.');
header('Location: ' . $back);
exit;
