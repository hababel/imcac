<?php

namespace App\Model;

use Core\Lib\Model;

class Payment extends Model
{
	/**
	 * Busca el último pago asociado a un equipo.
	 */
	public function findLastByTeamId(int $teamId): ?array
	{
		return $this->fetchOne(
			"SELECT * FROM payments WHERE team_id = :team_id ORDER BY id DESC LIMIT 1",
			['team_id' => $teamId]
		);
	}

	public function countByStatus(string $status): int
	{
		$row = $this->fetchOne("SELECT COUNT(*) as c FROM payments WHERE status = ?", [$status]);
		return (int)($row['c'] ?? 0);
	}
}
