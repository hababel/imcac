<?php

namespace App\Model;

use Core\Lib\Model;

class Participant extends Model
{

	public function findById(int $id): ?array
	{
		return $this->fetchOne("SELECT * FROM participants WHERE id = ?", [$id]);
	}
	/**
	 * Retorna todos los participantes de un equipo.
	 */
	public function findByTeamId(int $teamId): array
	{
		$sql = "SELECT
					p.*,
					(SELECT COUNT(*) FROM submissions s WHERE s.participant_id = p.id) > 0 as has_submission,
					(SELECT COUNT(*) FROM invitations i WHERE i.participant_id = p.id) > 0 as has_invitation
				FROM participants p
				WHERE p.team_id = :team_id
				ORDER BY p.name ASC";
		return $this->fetchAll($sql, ['team_id' => $teamId]);
	}

	/**
	 * Busca un participante por email dentro de un equipo específico.
	 */
	public function findByEmailAndTeam(string $email, int $teamId): ?array
	{
		return $this->fetchOne(
			"SELECT * FROM participants WHERE email = :email AND team_id = :team_id",
			['email' => $email, 'team_id' => $teamId]
		);
	}

	/**
	 * Crea un nuevo participante en un equipo.
	 */
	public function create(int $teamId, string $name, string $email): int
	{
		return $this->insert('participants', [
			'team_id' => $teamId,
			'name' => $name,
			'email' => $email,
			'role' => 'INTEGRANTE'
		]);
	}

	/**
	 * Retorna los participantes de un equipo que aún no tienen invitación.
	 */
	public function findUninvitedByTeamId(int $teamId): array
	{
		$sql = "SELECT p.* FROM participants p
				LEFT JOIN invitations i ON p.id = i.participant_id
				WHERE p.team_id = :team_id AND i.id IS NULL";
		return $this->fetchAll($sql, ['team_id' => $teamId]);
	}

	public function countAll(): int
	{
		$row = $this->fetchOne("SELECT COUNT(*) as c FROM participants");
		return (int)($row['c'] ?? 0);
	}

	public function listAllWithDetails(): array
	{
		$sql = "SELECT
					p.id, p.name, p.email, p.created_at,
					t.name as team_name,
					m.name as manager_name
				FROM participants p
				LEFT JOIN teams t ON p.team_id = t.id
				LEFT JOIN users m ON t.manager_user_id = m.id
				ORDER BY p.name ASC";
		return $this->fetchAll($sql);
	}
}
