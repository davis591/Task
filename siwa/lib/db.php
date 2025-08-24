<?php
class DB {
	public static function conn(): PDO {
		static $pdo = null;
		if ($pdo !== null) {
			return $pdo;
		}
		$path = __DIR__ . '/../storage/siwa.sqlite';
		$pdo = new PDO('sqlite:' . $path, null, null, [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		]);
		$pdo->exec('PRAGMA foreign_keys = ON');
		return $pdo;
	}
}