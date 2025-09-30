<?php

namespace Core\Lib;

use Core\Lib\Database;
use PDO;

class RateLimiter
{
	private PDO $db;
	public function __construct()
	{
		$this->db = Database::pdo();
	}

	public function isBlocked(string $email, string $ip, int $maxAttempts = 5, int $windowMinutes = 10): bool
	{
		$stmt = $this->db->prepare("
      SELECT COUNT(*) AS fails
      FROM login_attempts
      WHERE email = ? AND ip = ? AND success = 0
        AND attempted_at >= (NOW() - INTERVAL ? MINUTE)
    ");
		$stmt->execute([$email, $ip, $windowMinutes]);
		$fails = (int)$stmt->fetchColumn();
		return $fails >= $maxAttempts;
	}

	public function logAttempt(string $email, string $ip, bool $success): void
	{
		$stmt = $this->db->prepare("INSERT INTO login_attempts (email, ip, success) VALUES (?,?,?)");
		$stmt->execute([$email, $ip, $success ? 1 : 0]);
	}
}
