<?php
require __DIR__ . "/lib/db.php";
$pdo = DB::conn();
$sql = file_get_contents(__DIR__ . "/lib/schema.sql");
$pdo->exec($sql);
// Seed users
$exists = $pdo->query("SELECT COUNT(*) AS c FROM users")->fetch()['c'] ?? 0;
if (!$exists) {
	$ins = $pdo->prepare("INSERT INTO users(username,password_hash,role,full_name) VALUES(?,?,?,?)");
	$ins->execute(["cashier", password_hash("cashier123", PASSWORD_DEFAULT), "cashier", "Maria Santos"]);
	$ins->execute(["admin", password_hash("admin123", PASSWORD_DEFAULT), "admin", "Admin User"]);
}
// Seed consumers and bills if empty
$exists = $pdo->query("SELECT COUNT(*) AS c FROM consumers")->fetch()['c'] ?? 0;
if (!$exists) {
	$pdo->beginTransaction();
	$consumerStmt = $pdo->prepare("INSERT INTO consumers(account_no,name,sitio) VALUES(?,?,?)");
	$billStmt = $pdo->prepare("INSERT INTO bills(consumer_id,period_start,period_end,current_charges,unpaid_balance,penalties,status,due_date) VALUES(?,?,?,?,?,?,?,?)");
	$consumers = [
		["SI-2025-0142","Santos, Juan D.","Purok 3, San Isidro"],
		["SI-2025-0089","Reyes, Maria C.","Purok 2, San Isidro"],
		["SI-2025-0201","Cruz, Antonio B.","Purok 1, San Isidro"],
		["SI-2025-0176","Garcia, Elena F.","Purok 4, San Isidro"],
		["SI-2025-0054","Mendoza, Roberto G.","Purok 5, San Isidro"],
	];
	foreach ($consumers as $c) {
		$consumerStmt->execute($c);
		$cid = (int)$pdo->lastInsertId();
		$billStmt->execute([$cid, "2025-06-15", "2025-07-15", 425.00, 150.00, 45.00, "unpaid", "2025-07-30"]);
	}
	$pdo->commit();
}

echo "Initialized.\n";
