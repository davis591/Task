<?php
require __DIR__ . '/../lib/auth.php';
Auth::requireRole('cashier');
$pdo = DB::conn();
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$consumerId = (int)($input['consumer_id'] ?? 0);
$billId = (int)($input['bill_id'] ?? 0);
$amount = (float)($input['amount'] ?? 0);
$method = trim($input['method'] ?? 'Cash');
$notes = trim($input['notes'] ?? '');
$cashier = Auth::user();
header('Content-Type: application/json');
if ($consumerId <= 0 || $billId <= 0 || $amount <= 0) { http_response_code(400); echo json_encode(['error'=>'Invalid input']); exit; }

$pdo->beginTransaction();
try {
	// Generate receipt number: SI-R-YYYY-####
	$year = (new DateTime('now'))->format('Y');
	$last = $pdo->query("SELECT receipt_no FROM payments WHERE receipt_no LIKE 'SI-R-$year-%' ORDER BY id DESC LIMIT 1")->fetch()['receipt_no'] ?? null;
	$seq = 1;
	if ($last && preg_match('/SI-R-'.preg_quote($year,'/').'-(\d+)/', $last, $m)) { $seq = ((int)$m[1]) + 1; }
	$receipt = sprintf('SI-R-%s-%04d', $year, $seq);

	$ins = $pdo->prepare("INSERT INTO payments(bill_id, consumer_id, cashier_id, amount_paid, method, receipt_no, notes) VALUES(?,?,?,?,?,?,?)");
	$ins->execute([$billId, $consumerId, $cashier['id'], $amount, $method, $receipt, $notes]);

	// Update bill status
	$bill = $pdo->prepare('SELECT total_due FROM bills WHERE id = ?');
	$bill->execute([$billId]);
	$totalDue = (float)($bill->fetch()['total_due'] ?? 0);
	if ($amount >= $totalDue) {
		$pdo->prepare("UPDATE bills SET status='paid', unpaid_balance=0, penalties=0, current_charges=0 WHERE id=?")->execute([$billId]);
	} else {
		$remaining = max($totalDue - $amount, 0);
		$pdo->prepare("UPDATE bills SET status='partial', unpaid_balance = :rem WHERE id=:id")
			->execute([':rem'=>$remaining, ':id'=>$billId]);
	}
	$pdo->commit();

	// Return receipt info
	$consumer = $pdo->prepare('SELECT account_no, name FROM consumers WHERE id=?');
	$consumer->execute([$consumerId]);
	$c = $consumer->fetch();
	echo json_encode([
		'success'=>true,
		'receipt_no'=>$receipt,
		'consumer'=>$c,
		'amount_paid'=>$amount,
		'method'=>$method,
		'cashier'=>$cashier['full_name'],
		'created_at'=>(new DateTime('now'))->format('Y-m-d H:i:s')
	]);
} catch (Throwable $e) {
	$pdo->rollBack();
	http_response_code(500);
	echo json_encode(['error'=>'Payment failed','details'=>$e->getMessage()]);
}