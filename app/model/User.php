<?php

namespace App\Model;

use Core\Lib\Model;

class User extends Model
{
	public function findByEmail(string $email): ?array
	{
		return $this->fetchOne("SELECT * FROM users WHERE email = ? LIMIT 1", [$email]);
	}

	public function create(string $name, string $email, string $password, string $role = 'ENCARGADO'): int
	{
		return $this->insert('users', [
			'name'          => $name,
			'email'         => $email,
			'password_hash' => password_hash($password, PASSWORD_DEFAULT),
			'role'          => $role,
			'status'        => 'ACTIVE',
			'created_at'    => $this->now(),
		]);
	}

	public function disable(int $userId): int
	{
		return $this->update('users', ['status' => 'DISABLED'], 'id = :id', ['id' => $userId]);
	}

	public function updatePassword(int $userId, string $newPassword): int
	{
		return $this->update('users', [
			'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
		], 'id = :id', ['id' => $userId]);
	}

	public function countByRole(string $role): int
	{
		$row = $this->fetchOne("SELECT COUNT(*) as c FROM users WHERE role = ?", [$role]);
		return (int)($row['c'] ?? 0);
	}

	public function listByRole(string $role): array
	{
		return $this->fetchAll("SELECT id, name, email, status, created_at FROM users WHERE role = ? ORDER BY name ASC", [$role]);
	}
}
