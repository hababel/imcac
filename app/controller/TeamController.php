<?php

namespace App\Controller;

use Core\Lib\Flash;

use Core\Lib\Auth;
use Core\Lib\Controller;
use Core\Lib\Url;
use App\Model\Team;
use App\Model\Participant;
use App\Model\Invitation;
use App\Model\Payment;
use App\Model\Submission;
use App\Model\Form;
use Core\Lib\Mailer;

class TeamController extends Controller
{
	// Uses the default layout
	public function index()
	{
		if (!Auth::check()) {
			header('Location: ' . Url::to('/login'));
			exit;
		}
		$user = Auth::user();
		$teamModel = new Team();
		$teams = [];

		if ($user && $user['role'] === 'ENCARGADO') {
			$teams = $teamModel->findByManagerId((int)$user['id']);
		} else {
			$teams = $teamModel->all();
		}
		$this->view('teams/index', ['title' => 'Equipos', 'teams' => $teams, 'user' => $user]);
	}
	public function store()
	{
		if (!Auth::check()) {
			http_response_code(403);
			echo 'Acceso denegado';
			return;
		}
		$user = Auth::user();
		$teamModel = new Team();

		// Validar límite de creación para 'ENCARGADO'
		if ($user['role'] === 'ENCARGADO') {
			$currentTeams = $teamModel->countByManagerId((int)$user['id']);
			if ($currentTeams >= $user['team_creation_limit']) {
				Flash::set('danger', 'Has alcanzado el límite de equipos que puedes crear.');
				header('Location: ' . Url::to('/teams'));
				exit;
			}
		}

		$name = trim($_POST['name'] ?? '');
		if ($name === '') {
			Flash::set('warning', 'El nombre del equipo es requerido.');
			header('Location: ' . Url::to('/teams'));
			exit;
		}

		$teamModel->create($name, (int)$user['id']);
		Flash::set('success', 'Equipo "' . htmlspecialchars($name) . '" creado correctamente.');
		header('Location: ' . Url::to('/teams'));
		exit;
	}

	public function showManage(string $id)
	{
		$teamId = (int)$id;
		$user = Auth::user();

		// Proteger la ruta
		if (!$user) {
			Flash::set('danger', 'Acceso no autorizado.');
			header('Location: ' . Url::to('/teams'));
			exit;
		}

		$team = (new Team())->findDetailsById($teamId);

		if (!$team) {
			Flash::set('danger', 'El equipo no fue encontrado.');
			header('Location: ' . Url::to('/teams'));
			exit;
		}

		// Un ADMIN puede ver todo. Un ENCARGADO solo puede ver sus propios equipos.
		$isOwner = ($user['role'] === 'ENCARGADO' && (int)$user['id'] === (int)$team['manager_user_id']);
		if ($user['role'] !== 'ADMIN' && !$isOwner) {
			Flash::set('danger', 'No tienes permiso para gestionar este equipo.');
			header('Location: ' . Url::to('/teams'));
			exit;
		}

		$participants = (new Participant())->findByTeamId($teamId);
		$submissionCount = (new Submission())->countByTeamId($teamId);
		$payment = (new Payment())->findLastByTeamId($teamId);

		$this->view('teams/manage', [
			'title' => 'Gestionar Equipo: ' . htmlspecialchars($team['name']),
			'team' => $team,
			'participants' => $participants,
			'submissionCount' => $submissionCount,
			'payment' => $payment,
		]);
	}

	public function invite()
	{
		// 1. Validaciones de seguridad y autenticación
		if (!isset($_POST['_csrf']) || !\Core\Lib\Csrf::verify($_POST['_csrf'])) {
			http_response_code(403);
			echo "CSRF inválido";
			return;
		}

		$user = Auth::user();
		if (!$user) {
			Flash::set('danger', 'Debes iniciar sesión para invitar.');
			header('Location: ' . Url::to('/login'));
			exit;
		}

		// 2. Validar datos de entrada
		$teamId = (int)($_POST['team_id'] ?? 0);
		$name = trim($_POST['name'] ?? '');
		$email = strtolower(trim($_POST['email'] ?? ''));

		if ($teamId <= 0 || $name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			Flash::set('danger', 'Datos de invitación inválidos.');
			header('Location: ' . Url::to('/teams')); // Redirigir a la lista general si el teamId es malo
			exit;
		}

		$teamModel = new Team();
		$team = $teamModel->findDetailsById($teamId);

		// 3. Validaciones de autorización y reglas de negocio
		$isOwner = ($user['role'] === 'ENCARGADO' && (int)$user['id'] === (int)$team['manager_user_id']);
		if ($user['role'] !== 'ADMIN' && !$isOwner) {
			Flash::set('danger', 'No tienes permiso para invitar a este equipo.');
			header('Location: ' . Url::to('/teams/manage/' . $teamId));
			exit;
		}

		$participantModel = new Participant();
		// Verificar si el equipo ya está lleno
		$participants = $participantModel->findByTeamId($teamId);
		if (count($participants) >= $team['size_limit']) {
			Flash::set('danger', 'El equipo ha alcanzado su límite de integrantes. No se pueden añadir más.');
			header('Location: ' . Url::to('/teams/manage/' . $teamId));
			exit;
		}

		// Verificar si el email ya fue invitado a este equipo
		if ($participantModel->findByEmailAndTeam($email, $teamId)) {
			Flash::set('warning', 'Este email ya pertenece a un integrante del equipo.');
			header('Location: ' . Url::to('/teams/manage/' . $teamId));
			exit;
		}

		// 4. Crear la invitación y enviar correo
		$participantId = (new Participant())->create($teamId, $name, $email);
		$token = (new Invitation())->createForParticipant($participantId, $teamId);

		if ($token) {
			// Asumimos que las invitaciones son para el formulario principal (ID 1)
			// Esto podría ser configurable por equipo en el futuro.
			$form = (new Form())->findById(1);
			$bodyTemplate = $form['email_template_html'] ?: file_get_contents(__DIR__ . '/../views/emails/invitation_default.php');

			$header = file_get_contents(__DIR__ . '/../views/emails/partials/header.php');
			$footer = file_get_contents(__DIR__ . '/../views/emails/partials/footer.php');

			$inviteLink = Url::to('/survey/start') . '?token=' . urlencode($token);

			// Reemplazar placeholders
			$bodyTemplate = str_replace('{{participant_name}}', htmlspecialchars($name), $bodyTemplate);
			$bodyTemplate = str_replace('{{team_name}}', htmlspecialchars($team['name']), $bodyTemplate);
			$bodyTemplate = str_replace('{{invite_link}}', $inviteLink, $bodyTemplate);

			$fullHtml = $header . $bodyTemplate . $footer;
			$_SESSION['last_email_preview'] = $fullHtml;

			/* --- INICIO: Bloque temporal para mostrar correo en pantalla en lugar de enviarlo --- */
			$previewLink = Url::to('/preview/email');
			Flash::set('link', $previewLink);
			Flash::set('success', 'Invitación para "' . htmlspecialchars($name) . '" generada. Haz clic en el enlace de arriba para previsualizar el correo.');
			/* --- FIN: Bloque temporal --- */

			/* --- INICIO: Bloque original de envío de correo (actualmente comentado) ---
			$mailer = new Mailer();
			$sent = $mailer->send($email, $name, 'Invitación al Diagnóstico IMCAC', $fullHtml);
			if ($sent) {
				Flash::set('success', 'Correo de invitación enviado a ' . htmlspecialchars($name) . '.');
			} else {
				Flash::set('danger', 'El correo no pudo ser enviado. Revisa la configuración. El enlace es: ' . $inviteLink);
			}
			--- FIN: Bloque original --- */
		}

		header('Location: ' . Url::to('/teams/manage/' . $teamId));
		exit;
	}

	public function addParticipant()
	{
		// 1. Validaciones de seguridad y autenticación
		if (!isset($_POST['_csrf']) || !\Core\Lib\Csrf::verify($_POST['_csrf'])) {
			http_response_code(403);
			echo "CSRF inválido";
			return;
		}

		$user = Auth::user();
		if (!$user) {
			Flash::set('danger', 'Debes iniciar sesión para agregar integrantes.');
			header('Location: ' . Url::to('/login'));
			exit;
		}

		// 2. Validar datos de entrada
		$teamId = (int)($_POST['team_id'] ?? 0);
		$name = trim($_POST['name'] ?? '');
		$email = strtolower(trim($_POST['email'] ?? ''));

		if ($teamId <= 0 || $name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			Flash::set('danger', 'Datos del integrante inválidos.');
			header('Location: ' . Url::to('/teams'));
			exit;
		}

		$teamModel = new Team();
		$team = $teamModel->findDetailsById($teamId);

		// 3. Validaciones de autorización y reglas de negocio
		$isOwner = ($user['role'] === 'ENCARGADO' && (int)$user['id'] === (int)$team['manager_user_id']);
		if ($user['role'] !== 'ADMIN' && !$isOwner) {
			Flash::set('danger', 'No tienes permiso para agregar integrantes a este equipo.');
			header('Location: ' . Url::to('/teams/manage/' . $teamId));
			exit;
		}

		$participantModel = new Participant();
		// Verificar si el equipo ya está lleno
		$participants = $participantModel->findByTeamId($teamId);
		if (count($participants) >= $team['size_limit']) {
			Flash::set('danger', 'El equipo ha alcanzado su límite de integrantes.');
			header('Location: ' . Url::to('/teams/manage/' . $teamId));
			exit;
		}

		// Verificar si el email ya existe en el equipo
		if ($participantModel->findByEmailAndTeam($email, $teamId)) {
			Flash::set('warning', 'Este email ya pertenece a un integrante del equipo.');
			header('Location: ' . Url::to('/teams/manage/' . $teamId));
			exit;
		}

		// 4. Crear solo el participante
		$participantModel->create($teamId, $name, $email);
		Flash::set('success', 'Integrante "' . htmlspecialchars($name) . '" agregado al equipo.');
		header('Location: ' . Url::to('/teams/manage/' . $teamId));
		exit;
	}

	public function sendSingleInvitation(string $participantId)
	{
		// 1. Validaciones
		if (!isset($_POST['_csrf']) || !\Core\Lib\Csrf::verify($_POST['_csrf'])) {
			http_response_code(403);
			echo "CSRF inválido";
			return;
		}
		$user = Auth::user();
		if (!$user) {
			Flash::set('danger', 'Acceso no autorizado.');
			header('Location: ' . Url::to('/login'));
			exit;
		}

		$pId = (int)$participantId;
		$participant = (new Participant())->findById($pId);
		if (!$participant) {
			Flash::set('danger', 'Participante no encontrado.');
			header('Location: ' . Url::to('/teams'));
			exit;
		}

		$teamId = (int)$participant['team_id'];
		$team = (new Team())->findDetailsById($teamId);

		// 2. Autorización
		$isOwner = ($user['role'] === 'ENCARGADO' && (int)$user['id'] === (int)$team['manager_user_id']);
		if ($user['role'] !== 'ADMIN' && !$isOwner) {
			Flash::set('danger', 'No tienes permiso para gestionar este equipo.');
			header('Location: ' . Url::to('/teams'));
			exit;
		}

		// 3. Crear invitación
		$invitationModel = new Invitation();
		$token = $invitationModel->createForParticipant($pId, $teamId);

		if ($token) {
			// Asumimos que las invitaciones son para el formulario principal (ID 1)
			$form = (new Form())->findById(1);
			$bodyTemplate = $form['email_template_html'] ?: file_get_contents(__DIR__ . '/../views/emails/invitation_default.php');

			$header = file_get_contents(__DIR__ . '/../views/emails/partials/header.php');
			$footer = file_get_contents(__DIR__ . '/../views/emails/partials/footer.php');

			$inviteLink = Url::to('/survey/start') . '?token=' . urlencode($token);

			// Reemplazar placeholders
			$bodyTemplate = str_replace('{{participant_name}}', htmlspecialchars($participant['name']), $bodyTemplate);
			$bodyTemplate = str_replace('{{team_name}}', htmlspecialchars($team['name']), $bodyTemplate);
			$bodyTemplate = str_replace('{{invite_link}}', $inviteLink, $bodyTemplate);

			$fullHtml = $header . $bodyTemplate . $footer;
			$_SESSION['last_email_preview'] = $fullHtml;

			/* --- INICIO: Bloque temporal para mostrar correo en pantalla en lugar de enviarlo --- */
			$previewLink = Url::to('/preview/email');
			Flash::set('link', $previewLink);
			Flash::set('success', 'Invitación para "' . htmlspecialchars($participant['name']) . '" generada. Haz clic en el enlace de arriba para previsualizar el correo.');
			/* --- FIN: Bloque temporal --- */

			/* --- INICIO: Bloque original de envío de correo (actualmente comentado) ---
			$mailer = new Mailer();
			$sent = $mailer->send($participant['email'], $participant['name'], 'Invitación al Diagnóstico IMCAC', $fullHtml);
			if ($sent) {
				Flash::set('success', 'Correo de invitación enviado a ' . htmlspecialchars($participant['name']) . '.');
			} else {
				Flash::set('danger', 'El correo no pudo ser enviado. Revisa la configuración. El enlace es: ' . $inviteLink);
			}
			--- FIN: Bloque original --- */
		} else {
			Flash::set('warning', 'El participante ya tenía una invitación.');
		}

		header('Location: ' . Url::to('/teams/manage/' . $teamId));
		exit;
	}

	public function sendBulkInvitations()
	{
		// 1. Validaciones
		if (!isset($_POST['_csrf']) || !\Core\Lib\Csrf::verify($_POST['_csrf'])) {
			http_response_code(403);
			echo "CSRF inválido";
			return;
		}
		$user = Auth::user();
		if (!$user) {
			Flash::set('danger', 'Acceso no autorizado.');
			header('Location: ' . Url::to('/login'));
			exit;
		}

		$teamId = (int)($_POST['team_id'] ?? 0);
		$team = (new Team())->findDetailsById($teamId);
		if (!$team) {
			Flash::set('danger', 'Equipo no encontrado.');
			header('Location: ' . Url::to('/teams'));
			exit;
		}

		// 2. Autorización
		$isOwner = ($user['role'] === 'ENCARGADO' && (int)$user['id'] === (int)$team['manager_user_id']);
		if ($user['role'] !== 'ADMIN' && !$isOwner) {
			Flash::set('danger', 'No tienes permiso para gestionar este equipo.');
			header('Location: ' . Url::to('/teams'));
			exit;
		}

		// 3. Obtener no invitados y crear invitaciones
		$participantModel = new Participant();
		$uninvited = $participantModel->findUninvitedByTeamId($teamId);

		if (empty($uninvited)) {
			Flash::set('info', 'No hay integrantes pendientes de invitación.');
			header('Location: ' . Url::to('/teams/manage/' . $teamId));
			exit;
		}

		$invitationModel = new Invitation();
		foreach ($uninvited as $participant) {
			$invitationModel->createForParticipant((int)$participant['id'], $teamId);
		}

		Flash::set('success', 'Se generaron ' . count($uninvited) . ' nuevas invitaciones. Refresca la página para ver los enlaces o estados actualizados.');
		header('Location: ' . Url::to('/teams/manage/' . $teamId));
		exit;
	}
}
