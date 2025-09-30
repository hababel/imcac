<?php

namespace App\Model;

use Core\Lib\Model;

class PasswordReset extends Model
{
	public function createToken(int $userId, string $plainToken, int $minutes = 20): int
	{
		$hash = hash('sha256', $plainToken);
		$expires = date('Y-m-d H:i:s', time() + ($minutes * 60));

		return $this->insert('password_resets', [
			'user_id'    => $userId,
			'token_hash' => $hash,
			'expires_at' => $expires,
			'created_at' => $this->now(),
		]);
	}

	public function countRecentRequests(int $userId, int $windowMinutes = 10): int
	{
		$sql = "SELECT COUNT(*) AS c
                  FROM password_resets
                 WHERE user_id = :uid
                   AND created_at >= (NOW() - INTERVAL :win MINUTE)";
		$row = $this->fetchOne($sql, ['uid' => $userId, 'win' => $windowMinutes]);
		return (int)($row['c'] ?? 0);
	}

	public function findValidByTokenAndEmail(string $plainToken, string $email): ?array
	{
		$hash = hash('sha256', $plainToken);

		$sql = "SELECT pr.*
                  FROM password_resets pr
                  JOIN users u ON u.id = pr.user_id
                 WHERE pr.token_hash = :th
                   AND u.email = :email
                   AND pr.used_at IS NULL
                   AND pr.expires_at > NOW()
                 LIMIT 1";
		return $this->fetchOne($sql, ['th' => $hash, 'email' => $email]);
	}

	public function markUsed(int $id): int
	{
		return $this->update('password_resets', [
			'used_at'   => $this->now(),
		], 'id = :id', ['id' => $id]);
	}
}
