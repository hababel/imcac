<?php

namespace App\Controller;

use Core\Lib\Auth;
use Core\Lib\Controller;
use Core\Lib\Database;
use Core\Lib\Url;
use App\Model\Form;
use App\Model\User;
use App\Model\Team;
use App\Model\Participant;
use App\Model\Submission;
use App\Model\Payment;
use App\Model\GlobalFormField;
use App\Model\Setting;
use Core\Lib\Flash;

class HomeController extends Controller
{
	public function index()
	{
		if (!Auth::check()) {
			header('Location: ' . Url::to('/login'));
			exit;
		}

		$user = Auth::user();
		$data = [
			'title' => 'Dashboard',
			'user' => $user
		];

		if ($user['role'] === 'ADMIN') {
			$data['stats'] = [
				'forms' => (new Form())->getStatsByStatus(),
				'managers' => (new User())->countByRole('ENCARGADO'),
				'teams' => (new Team())->countAll(),
				'participants' => (new Participant())->countAll(),
				'submissions' => (new Submission())->countAll(),
				'pending_payments' => (new Payment())->countByStatus('PENDING'),
			];
		} else {
			// Aquí podrías cargar datos para el dashboard del 'ENCARGADO'
			$pdo = Database::pdo();
			$data['dbName'] = $pdo->query("SELECT DATABASE() as db")->fetch()['db'] ?? 'no-db';
		}

		$this->view('home/index', $data);
	}
	public function health()
	{
		header('Content-Type: application/json');
		echo json_encode(['ok' => true, 'ts' => gmdate('c')]);
	}

	public function debug()
	{
		header('Content-Type: application/json');
		echo json_encode([
			'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? null,
			'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? null,
			'base'        => Url::base(),
		], JSON_PRETTY_PRINT);
	}

	/**
	 * Muestra una previsualización de la "Vista de Diagnóstico Interactivo".
	 * Solución temporal para facilitar el desarrollo del frontend.
	 */
	public function previewInteractiveSurvey()
	{
		// Proteger la ruta para que solo los admins puedan verla
		$user = Auth::user();
		if (!$user || $user['role'] !== 'ADMIN') {
			Flash::set('danger', 'Acceso no autorizado.');
			header('Location: ' . Url::to('/'));
			exit;
		}

		// 1. Obtener un formulario publicado para usar como base
		$formModel = new Form();
		$form = $formModel->findFirstPublished();

		if (!$form) {
			Flash::set('warning', 'No se puede previsualizar porque no hay ningún formulario de diagnóstico en estado "Publicado".');
			header('Location: ' . Url::to('/'));
			exit;
		}

		// 2. Obtener los campos globales activos
		$settings = (new Setting())->getAllAsAssoc();
		$activeSetId = $settings['active_global_field_set_id'] ?? 1;
		$globalFields = (new GlobalFormField())->findAllOrderedBySetId($activeSetId);

		// 3. Obtener la estructura del formulario
		$structure = $formModel->getFormStructure((int)$form['id']);

		// 4. Preparar datos simulados para la vista
		$mockInvitation = [
			'participant_name' => 'Usuario de Prueba',
			'participant_email' => 'preview@example.com',
			'team_name' => 'Equipo de Demostración',
			'manager_name' => 'Responsable de Prueba'
		];

		// 5. Renderizar la vista de previsualización
		$this->layout = 'survey_layout';
		$this->view('survey/show', [
			'title' => 'Previsualización de Diagnóstico',
			'form' => $form,
			'invitation' => $mockInvitation,
			'structure' => $structure,
			'globalFields' => $globalFields,
			'token' => 'preview_token'
		]);
	}

	/**
	 * Muestra una previsualización del último correo generado.
	 * Solución temporal para facilitar el desarrollo sin un servidor de correo.
	 */
	public function previewEmail()
	{
		// No es necesario un layout para esta vista
		$this->layout = null;

		// Se asume que la sesión ya está iniciada
		if (isset($_SESSION['last_email_preview'])) {
			echo $_SESSION['last_email_preview'];
			// Opcional: limpiar la sesión para que el correo solo se vea una vez.
			// unset($_SESSION['last_email_preview']);
		} else {
			http_response_code(404);
			echo "No hay previsualización de correo disponible.";
		}
	}
}
