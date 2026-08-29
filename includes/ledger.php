<?php
declare(strict_types=1);

/**
 * Balance sign convention (same field, interpreted per party type):
 * - CUSTOMER: positive balance = amount the customer owes the store ("baki" / receivable).
 * - SUPPLIER: positive balance = amount the store owes the supplier (payable).
 * Debit increases the balance (new due created), credit decreases it (payment settles it).
 */
function get_party_balance(string $partyId): float
{
    $stmt = db()->prepare('SELECT opening_balance FROM parties WHERE id = ?');
    $stmt->execute([$partyId]);
    $openingBalance = (float) ($stmt->fetchColumn() ?: 0);

    $stmt = db()->prepare('SELECT COALESCE(SUM(debit), 0) AS d, COALESCE(SUM(credit), 0) AS c FROM ledger_entries WHERE party_id = ?');
    $stmt->execute([$partyId]);
    $sums = $stmt->fetch();

    return $openingBalance + (float) $sums['d'] - (float) $sums['c'];
}

/** @return array<string, float> party_id => balance, for every party. */
function get_all_party_balances(): array
{
    $parties = db()->query('SELECT id, opening_balance FROM parties')->fetchAll();
    $grouped = db()->query(
        'SELECT party_id, COALESCE(SUM(debit), 0) AS d, COALESCE(SUM(credit), 0) AS c
         FROM ledger_entries GROUP BY party_id'
    )->fetchAll();

    $sums = [];
    foreach ($grouped as $row) {
        $sums[$row['party_id']] = $row;
    }

    $balances = [];
    foreach ($parties as $p) {
        $sum = $sums[$p['id']] ?? ['d' => 0, 'c' => 0];
        $balances[$p['id']] = (float) $p['opening_balance'] + (float) $sum['d'] - (float) $sum['c'];
    }
    return $balances;
}
