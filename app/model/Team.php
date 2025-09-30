<?php

namespace App\Model;

use Core\Lib\Model;

class Team extends Model
{
	/**
	 * Retorna todos los equipos.
	 */
	public function all(): array
	{
		return $this->fetchAll("SELECT * FROM teams ORDER BY id DESC");
	}

	/**
	 * Retorna los equipos asignados a un usuario manager/encargado.
	 */
	public function findByManagerId(int $userId): array
	{
		return $this->fetchAll(
			"SELECT * FROM teams WHERE manager_user_id = :user_id ORDER BY id DESC",
			['user_id' => $userId]
		);
	}

	/**
	 * Cuenta los equipos asignados a un usuario manager/encargado.
	 */
	public function countByManagerId(int $userId): int
	{
		$row = $this->fetchOne(
			"SELECT COUNT(*) as c FROM teams WHERE manager_user_id = :user_id",
			['user_id' => $userId]
		);
		return (int)($row['c'] ?? 0);
	}

	/**
	 * Crea un nuevo equipo.
	 * Asigna el manager y un plan por defecto.
	 */
	public function create(string $name, int $managerId): int
	{
		return $this->insert('teams', [
			'name' => $name,
			'manager_user_id' => $managerId,
			'plan_id' => 1, // Plan por defecto
			'size_limit' => 5, // Límite de tamaño por defecto del Plan 1
		]);
	}

	/**
	 * Busca los detalles completos de un equipo, incluyendo plan y manager.
	 */
	public function findDetailsById(int $teamId): ?array
	{
		$sql = "SELECT
					t.*,
					p.name as plan_name,
					u.name as manager_name,
					u.email as manager_email
				FROM teams t
				LEFT JOIN plans p ON p.id = t.plan_id
				LEFT JOIN users u ON u.id = t.manager_user_id
				WHERE t.id = :team_id";
		return $this->fetchOne($sql, ['team_id' => $teamId]);
	}

	public function countAll(): int
	{
		$row = $this->fetchOne("SELECT COUNT(*) as c FROM teams");
		return (int)($row['c'] ?? 0);
	}
}
