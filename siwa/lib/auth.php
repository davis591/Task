<?php
require_once __DIR__ . '/db.php';

class Auth {
	public static function start(): void {
		if (session_status() !== PHP_SESSION_ACTIVE) {
			session_start();
		}
	}

	public static function login(string $username, string $password): bool {
		self::start();
		$pdo = DB::conn();
		$stmt = $pdo->prepare('SELECT * FROM users WHERE username = :u');
		$stmt->execute([':u' => $username]);
		$user = $stmt->fetch();
		if (!$user) return false;
		if (!password_verify($password, $user['password_hash'])) return false;
		$_SESSION['user'] = [
			'id' => (int)$user['id'],
			'username' => $user['username'],
			'role' => $user['role'],
			'full_name' => $user['full_name'],
		];
		return true;
	}

	public static function logout(): void {
		self::start();
		$_SESSION = [];
		if (ini_get('session.use_cookies')) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
		}
		session_destroy();
	}

	public static function requireRole(string $role): void {
		self::start();
		if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== $role) {
			header('Location: /public/index.php');
			exit;
		}
	}

	public static function user(): ?array {
		self::start();
		return $_SESSION['user'] ?? null;
	}
}

