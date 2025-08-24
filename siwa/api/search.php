<?php
require __DIR__ . '/../lib/auth.php';
Auth::requireRole('cashier');
$pdo = DB::conn();
$q = trim($_GET['q'] ?? '');
header('Content-Type: application/json');
if ($q === '') { echo json_encode(['items'=>[]]); exit; }
$sql = "SELECT c.*, b.id AS bill_id, b.period_start, b.period_end, b.current_charges, b.unpaid_balance, b.penalties, b.total_due, b.status, b.due_date
        FROM consumers c
        LEFT JOIN bills b ON b.consumer_id = c.id AND b.status != 'paid'
        WHERE c.account_no LIKE :q OR c.name LIKE :q OR c.sitio LIKE :q
        ORDER BY c.name LIMIT 10";
$stmt = $pdo->prepare($sql);
$stmt->execute([':q' => "%$q%"]);
$items = $stmt->fetchAll();
echo json_encode(['items'=>$items]);
