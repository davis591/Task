<?php
require __DIR__ . '/../lib/auth.php';
Auth::requireRole('cashier');
$pdo = DB::conn();
$cashier = Auth::user();
$today = (new DateTime('now'))->format('Y-m-d');
$sql = "SELECT COUNT(*) AS txns, COALESCE(SUM(amount_paid),0) AS total
        FROM payments WHERE date(created_at) = :d AND cashier_id = :cid";
$stmt = $pdo->prepare($sql);
$stmt->execute([':d' => $today, ':cid' => $cashier['id']]);
$row = $stmt->fetch();
$breakdown = $pdo->prepare("SELECT method, COALESCE(SUM(amount_paid),0) AS total FROM payments WHERE date(created_at)=:d AND cashier_id=:cid GROUP BY method");
$breakdown->execute([':d'=>$today, ':cid'=>$cashier['id']]);
$bd = $breakdown->fetchAll();
header('Content-Type: application/json');
echo json_encode(['total_collected_today'=>(float)$row['total'], 'transactions'=>(int)$row['txns'], 'breakdown'=>$bd]);
