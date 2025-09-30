<?php

namespace App\Model;

use Core\Lib\Model;

class Submission extends Model
{
	public function create(int $participantId, int $formId, float $score, ?string $globalDataJson): int
	{
		return $this->insert('submissions', [
			'participant_id' => $participantId,
			'form_id' => $formId,
			'score' => $score,
			'global_data' => $globalDataJson,
			'submitted_at' => $this->now()
		]);
	}

	public function countAll(): int
	{
		return (int)$this->fetchOne("SELECT COUNT(*) as count FROM submissions")['count'];
	}

	public function countByTeamId(int $teamId): int
	{
		$sql = "SELECT COUNT(s.id) as count FROM submissions s
                JOIN participants p ON s.participant_id = p.id
                WHERE p.team_id = ?";
		return (int)$this->fetchOne($sql, [$teamId])['count'];
	}
}
