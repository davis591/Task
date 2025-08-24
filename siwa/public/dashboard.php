<?php
require __DIR__ . '/../lib/auth.php';
Auth::requireRole('cashier');
$user = Auth::user();
?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>SIWA Cashier</title>
	<link rel="stylesheet" href="/assets/css/app.css" />
	<script defer src="/assets/js/app.js"></script>
</head>
<body>
	<header class="topbar">
		<div>
			<strong>San Isidro Water Association</strong>
			<div class="sub">Catanauan, Quezon</div>
		</div>
		<div class="userbox">
			<span>Cashier: <?= htmlspecialchars($user['full_name']) ?></span>
			<a href="/public/logout.php" class="btn">Logout</a>
		</div>
	</header>
	<main class="grid">
		<section class="card">
			<h3>Consumer Search</h3>
			<div class="search-row">
				<input id="q" placeholder="Search by account number, name or sitio" />
				<button id="btnSearch">Search</button>
			</div>
			<div id="consumer" class="consumer"></div>
		</section>
		<section class="card">
			<h3>Daily Collection Summary</h3>
			<div id="summary"></div>
			<div class="actions">
				<button id="btnExport">Export CSV</button>
				<button id="btnEmail">Email to Admin</button>
			</div>
		</section>
		<section class="card span-2">
			<h3>Payment Processing</h3>
			<form id="payForm">
				<input type="hidden" id="consumer_id" />
				<input type="hidden" id="bill_id" />
				<div class="row">
					<label>Amount Due</label>
					<input id="amount_due" readonly />
					<label>Amount Received</label>
					<input id="amount_received" required />
					<label>Change</label>
					<input id="change" readonly />
				</div>
				<div class="row">
					<label>Payment Method</label>
					<select id="method">
						<option>Cash</option>
						<option>Check</option>
						<option>Money Order</option>
					</select>
					<label>Notes</label>
					<input id="notes" />
					<button id="btnProcess" type="submit">Process Payment</button>
				</div>
			</form>
		</section>
		<section class="card span-2">
			<h3>Receipt Preview</h3>
			<div id="receipt" class="receipt"></div>
			<button id="btnPrint">Print Receipt</button>
		</section>
		<section class="card span-2">
			<h3>Recent Transactions</h3>
			<div id="recent"></div>
			<button id="btnRefreshRecent">Refresh</button>
		</section>
	</main>
</body>
</html>
