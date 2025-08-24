<?php
require __DIR__ . '/../lib/auth.php';
Auth::requireRole('cashier');
$pdo = DB::conn();
$cashier = Auth::user();
$stmt = $pdo->prepare("SELECT p.id, p.receipt_no, time(p.created_at) AS time, c.name AS consumer, c.account_no, p.amount_paid, p.method
FROM payments p JOIN consumers c ON c.id = p.consumer_id
WHERE p.cashier_id = :cid ORDER BY p.id DESC LIMIT 10");
$stmt->execute([':cid'=>$cashier['id']]);
header('Content-Type: application/json');
echo json_encode(['items'=>$stmt->fetchAll()]);
