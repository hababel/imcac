<?php

namespace App\Model;

use Core\Lib\Model;

class Invitation extends Model
{
	/**
	 * Crea un participante y su invitación con un token único.
	 * Devuelve el token en texto plano para poder mostrarlo una vez.
	 * @deprecated Usa Participant::create y luego Invitation::createForParticipant
	 */
	public function createForNewParticipant(int $teamId, string $name, string $email): string
	{
		$participantModel = new Participant();
		$participantId = 0;

		$this->withTransaction(function () use ($teamId, $name, $email, $participantModel, &$participantId) {
			$participantId = $participantModel->create($teamId, $name, $email);
		});

		return $this->createForParticipant($participantId, $teamId);
	}

	/**
	 * Crea una invitación para un participante existente.
	 * Devuelve el token en texto plano.
	 */
	public function createForParticipant(int $participantId, int $teamId): string
	{
		// Prevenir duplicados: si ya existe una invitación, no se crea otra.
		$existing = $this->fetchOne(
			"SELECT id FROM invitations WHERE participant_id = :pid",
			['pid' => $participantId]
		);
		if ($existing) {
			// Podríamos optar por devolver la invitación existente o lanzar un error.
			// Por ahora, para simplificar, no hacemos nada y devolvemos un token vacío.
			// El controlador debería prevenir esto.
			return '';
		}

		$token = bin2hex(random_bytes(32));

		$this->insert('invitations', [
			'team_id' => $teamId,
			'participant_id' => $participantId,
			'token_hash' => hash('sha256', $token),
			// El token expira en 7 días
			'expires_at' => date('Y-m-d H:i:s', strtotime('+7 days'))
		]);

		return $token; // Devolver el token en texto plano
	}

	/**
	 * Busca una invitación válida por su token y retorna los detalles.
	 */
	public function findValidByToken(string $token): ?array
	{
		$tokenHash = hash('sha256', $token);
		$sql = "SELECT
					i.id as invitation_id, i.token_hash,
					p.id as participant_id, p.name as participant_name, p.email as participant_email,
					t.id as team_id, t.name as team_name, t.form_id,
					u.name as manager_name
				FROM invitations i
				JOIN participants p ON i.participant_id = p.id
				JOIN teams t ON i.team_id = t.id
				JOIN users u ON t.manager_user_id = u.id
				WHERE i.token_hash = :token_hash
				  AND i.consumed_at IS NULL
				  AND i.expires_at > NOW()";

		return $this->fetchOne($sql, ['token_hash' => $tokenHash]);
	}
}
