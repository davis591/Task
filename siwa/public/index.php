<?php
require __DIR__ . '/../lib/auth.php';
Auth::start();
if (Auth::user()) { header('Location: /public/dashboard.php'); exit; }
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (Auth::login($_POST['username'] ?? '', $_POST['password'] ?? '')) {
		header('Location: /public/dashboard.php');
		exit;
	} else {
		$error = 'Invalid credentials';
	}
}
?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>SIWA Cashier Login</title>
	<link rel="stylesheet" href="/assets/css/app.css" />
</head>
<body>
	<div class="login-box">
		<h1>San Isidro Water Association</h1>
		<div class="sub">Catanauan, Quezon</div>
		<h2>Cashier Login</h2>
		<?php if ($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
		<form method="post" class="form">
			<label>Username</label>
			<input name="username" required autocomplete="username" />
			<label>Password</label>
			<input type="password" name="password" required autocomplete="current-password" />
			<button type="submit">Sign In</button>
		</form>
	</div>
</body>
</html>
