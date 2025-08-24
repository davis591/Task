<?php
require __DIR__ . '/../lib/auth.php';
Auth::requireRole('cashier');
$pdo = DB::conn();
$cashier = Auth::user();
$today = (new DateTime('now'))->format('Y-m-d');
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="siwa-collections-' . $today . '.csv"');
$out = fopen('php://output', 'w');
fputcsv($out, ['Receipt No','Time','Account No','Consumer','Amount','Method']);
$stmt = $pdo->prepare("SELECT p.receipt_no, p.created_at, c.account_no, c.name, p.amount_paid, p.method
FROM payments p JOIN consumers c ON c.id = p.consumer_id
WHERE p.cashier_id = :cid AND date(p.created_at)=:d ORDER BY p.id");
$stmt->execute([':cid'=>$cashier['id'], ':d'=>$today]);
while ($row = $stmt->fetch()) {
	fputcsv($out, [$row['receipt_no'], $row['created_at'], $row['account_no'], $row['name'], $row['amount_paid'], $row['method']]);
}
fclose($out);