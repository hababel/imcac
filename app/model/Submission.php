<?php

namespace App\Model;

use Core\Lib\Model;

class Submission extends Model
{
	/**
	 * Cuenta las respuestas (submissions) de un equipo.
	 */
	public function countByTeamId(int $teamId): int
	{
		$sql = "SELECT COUNT(s.id) as c FROM submissions s
				JOIN participants p ON p.id = s.participant_id
				WHERE p.team_id = :team_id";
		$row = $this->fetchOne($sql, ['team_id' => $teamId]);
		return (int)($row['c'] ?? 0);
	}

	public function countAll(): int
	{
		$row = $this->fetchOne("SELECT COUNT(*) as c FROM submissions");
		return (int)($row['c'] ?? 0);
	}
}
