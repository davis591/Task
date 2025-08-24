<?php
require __DIR__ . '/../lib/auth.php';
Auth::requireRole('cashier');
$pdo = DB::conn();
$receipt = $_GET['no'] ?? '';
$stmt = $pdo->prepare("SELECT p.*, c.name AS consumer_name, c.account_no, b.period_start, b.period_end
FROM payments p
JOIN consumers c ON c.id = p.consumer_id
JOIN bills b ON b.id = p.bill_id
WHERE p.receipt_no = ?");
$stmt->execute([$receipt]);
$r = $stmt->fetch();
if (!$r) { http_response_code(404); echo 'Not found'; exit; }
?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Receipt <?= htmlspecialchars($r['receipt_no']) ?></title>
	<link rel="stylesheet" href="/assets/css/print.css" />
</head>
<body class="receipt-print" onload="window.print()">
	<div class="header">
		<div class="title">SAN ISIDRO WATER ASSOCIATION</div>
		<div class="sub">Catanauan, Quezon</div>
		<div class="sub">Official Receipt</div>
	</div>
	<div class="section">
		<div><strong>Receipt No:</strong> <?= htmlspecialchars($r['receipt_no']) ?></div>
		<div><strong>Date:</strong> <?= htmlspecialchars($r['created_at']) ?></div>
		<div><strong>Account No:</strong> <?= htmlspecialchars($r['account_no']) ?></div>
		<div><strong>Consumer:</strong> <?= htmlspecialchars($r['consumer_name']) ?></div>
		<div><strong>Billing Period:</strong> <?= htmlspecialchars($r['period_start']) ?> - <?= htmlspecialchars($r['period_end']) ?></div>
		<div><strong>Amount Paid:</strong> ₱<?= number_format($r['amount_paid'],2) ?></div>
		<div><strong>Payment Method:</strong> <?= htmlspecialchars($r['method']) ?></div>
		<div><strong>Cashier:</strong> <?= htmlspecialchars(Auth::user()['full_name']) ?></div>
	</div>
	<div class="footer">Thank you for your prompt payment! Keep this receipt for your records.</div>
</body>
</html>