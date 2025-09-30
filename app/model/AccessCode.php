<?php

namespace App\Model;

use Core\Lib\Model;

class AccessCode extends Model
{
	/**
	 * Crea un código de acceso de 6 dígitos para un usuario.
	 * El código expira en 10 minutos.
	 *
	 * @param int $userId ID del usuario.
	 * @return string El código de 6 dígitos en texto plano.
	 */
	public function createForUser(int $userId): string
	{
		$code = (string)random_int(100000, 999999);

		$this->insert('access_codes', [
			'user_id' => $userId,
			'code_hash' => password_hash($code, PASSWORD_DEFAULT),
			'expires_at' => date('Y-m-d H:i:s', strtotime('+10 minutes'))
		]);

		return $code;
	}

	/**
	 * Busca un código válido (no expirado, no consumido) para un usuario.
	 *
	 * @param int $userId ID del usuario.
	 * @param string $code El código en texto plano a verificar.
	 * @return array|null Los datos del código si es válido, o null.
	 */
	public function findValidByCode(int $userId, string $code): ?array
	{
		$codes = $this->fetchAll("SELECT * FROM access_codes WHERE user_id = ? AND consumed_at IS NULL AND expires_at > NOW() ORDER BY id DESC", [$userId]);
		foreach ($codes as $row) {
			if (password_verify($code, $row['code_hash'])) {
				return $row;
			}
		}
		return null;
	}

	public function markUsed(int $codeId): int
	{
		return $this->update('access_codes', ['consumed_at' => $this->now()], 'id = :id', ['id' => $codeId]);
	}
}
