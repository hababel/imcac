<?php

namespace App\Controller;

use Core\Lib\Auth;
use Core\Lib\Controller;
use Core\Lib\Flash;
use Core\Lib\Url;
use App\Model\User;
use App\Model\Participant;
use App\Model\Setting;
use App\Model\GlobalFormField;
use App\Model\GlobalFieldSet;
use App\Model\Form;

class AdminController extends Controller
{
	private function guardAdmin()
	{
		$user = Auth::user();
		if (!$user || $user['role'] !== 'ADMIN') {
			Flash::set('danger', 'Acceso no autorizado.');
			header('Location: ' . Url::to('/'));
			exit;
		}
	}

	public function listUsers()
	{
		$this->guardAdmin();

		$managers = (new User())->listByRole('ENCARGADO');
		$participants = (new Participant())->listAllWithDetails();

		$this->view('admin/users/index', [
			'title' => 'Gestión de Usuarios',
			'managers' => $managers,
			'participants' => $participants,
		]);
	}

	public function showCreateUser()
	{
		$this->guardAdmin();
		$this->view('admin/users/create', ['title' => 'Crear Nuevo Responsable']);
	}

	public function storeUser()
	{
		$this->guardAdmin();

		if (!isset($_POST['_csrf']) || !\Core\Lib\Csrf::verify($_POST['_csrf'])) {
			http_response_code(403);
			echo "CSRF inválido";
			return;
		}

		$name = trim($_POST['name'] ?? '');
		$email = strtolower(trim($_POST['email'] ?? ''));
		$password = $_POST['password'] ?? '';
		$role = $_POST['role'] ?? 'ENCARGADO';

		if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
			Flash::set('danger', 'Datos inválidos. Revisa el nombre, email y contraseña (mín. 8 caracteres).');
			header('Location: ' . Url::to('/admin/users/create'));
			exit;
		}

		if (!in_array($role, ['ENCARGADO', 'ADMIN'])) {
			Flash::set('danger', 'Rol no válido.');
			header('Location: ' . Url::to('/admin/users/create'));
			exit;
		}

		$userModel = new User();
		if ($userModel->findByEmail($email)) {
			Flash::set('warning', 'Ese correo ya está registrado.');
			header('Location: ' . Url::to('/admin/users/create'));
			exit;
		}

		$userModel->create($name, $email, $password, $role);

		Flash::set('success', 'Usuario "' . htmlspecialchars($name) . '" creado correctamente.');
		header('Location: ' . Url::to('/admin/users'));
		exit;
	}

	public function showSettings()
	{
		$this->guardAdmin();
		$settings = (new Setting())->getAllAsAssoc();
		$fieldSetModel = new GlobalFieldSet();

		// El ID del set a mostrar puede venir por GET, si no, se usa el activo
		$displaySetId = (int)($_GET['set_id'] ?? ($settings['active_global_field_set_id'] ?? 1));
		$activeSetId = $settings['active_global_field_set_id'] ?? 1;

		$allSets = $fieldSetModel->findAll();
		$globalFields = (new GlobalFormField())->findAllOrderedBySetId($displaySetId);

		// --- INICIO: Lógica para sugerir nombre de nueva versión ---
		$allSetNames = array_column($allSets, 'name');
		$baseSuggestion = 'Campos Globales ' . date('Ymd-Hi');
		$suggestedVersionName = $baseSuggestion;
		$counter = 2;
		while (in_array($suggestedVersionName, $allSetNames)) {
			$suggestedVersionName = $baseSuggestion . ' (' . $counter++ . ')';
		}
		// --- FIN: Lógica para sugerir nombre ---

		$this->view('admin/settings/index', [
			'title' => 'Configuración General',
			'globalFields' => $globalFields,
			'allSets' => $allSets,
			'displaySetId' => $displaySetId,
			'activeSetId' => $activeSetId,
			'suggestedVersionName' => $suggestedVersionName,
		]);
	}

	public function saveSettings()
	{
		$this->guardAdmin();

		if (!isset($_POST['_csrf']) || !\Core\Lib\Csrf::verify($_POST['_csrf'])) {
			http_response_code(403);
			echo "CSRF inválido";
			return;
		}

		$settingModel = new Setting();
		$keys = ['survey_header_show_name', 'survey_header_show_email', 'survey_header_show_role'];

		foreach ($keys as $key) {
			$settingModel->set($key, isset($_POST[$key]) ? '1' : '0');
		}

		Flash::set('success', 'Configuración guardada correctamente.');
		header('Location: ' . Url::to('/admin/settings'));
		exit;
	}

	public function storeGlobalField()
	{
		$this->guardAdmin();

		if (!isset($_POST['_csrf']) || !\Core\Lib\Csrf::verify($_POST['_csrf'])) {
			http_response_code(403);
			echo "CSRF inválido";
			return;
		}

		$label = trim($_POST['label'] ?? '');
		$placeholder = trim($_POST['placeholder'] ?? '');
		$fieldType = $_POST['field_type'] ?? 'text';
		$isRequired = isset($_POST['is_required']);
		$optionsStr = trim($_POST['options'] ?? '');
		$displaySetId = (int)($_POST['display_set_id'] ?? 0);
		$optionsJson = null;

		if (empty($label)) {
			Flash::set('danger', 'El nombre del campo (título) es obligatorio.');
		} else {
			if (in_array($fieldType, ['select', 'radio']) && !empty($optionsStr)) {
				// Convertir string (una opción por línea) a un array JSON
				$optionsArray = array_map('trim', explode("\n", $optionsStr));
				$optionsJson = json_encode($optionsArray);
			} elseif ($fieldType === 'range') {
				$optionsJson = json_encode([
					'min' => $_POST['min'] ?? 0,
					'max' => $_POST['max'] ?? 10
				]);
			}

			(new GlobalFormField())->create($label, $placeholder, $fieldType, $isRequired, $optionsJson, $displaySetId);
			Flash::set('success', 'Campo global creado correctamente.');
		}

		header('Location: ' . Url::to('/admin/settings?set_id=' . $displaySetId));
		exit;
	}

	public function updateGlobalField(string $id)
	{
		$this->guardAdmin();
		$fieldId = (int)$id;

		if (!isset($_POST['_csrf']) || !\Core\Lib\Csrf::verify($_POST['_csrf'])) {
			http_response_code(403);
			echo "CSRF inválido";
			return;
		}

		$label = trim($_POST['label'] ?? '');
		$placeholder = trim($_POST['placeholder'] ?? '');
		$fieldType = $_POST['field_type'] ?? 'text';
		$isRequired = isset($_POST['is_required']);
		$optionsStr = trim($_POST['options'] ?? '');
		$optionsJson = null;

		if (empty($label)) {
			Flash::set('danger', 'El nombre del campo (título) es obligatorio.');
		} else {
			if (in_array($fieldType, ['select', 'radio']) && !empty($optionsStr)) {
				$optionsArray = array_map('trim', explode("\n", $optionsStr));
				$optionsJson = json_encode($optionsArray);
			} elseif ($fieldType === 'range') {
				$optionsJson = json_encode([
					'min' => $_POST['min'] ?? 0,
					'max' => $_POST['max'] ?? 10
				]);
			}
			(new GlobalFormField())->updateById($fieldId, $label, $placeholder, $fieldType, $isRequired, $optionsJson);
			Flash::set('success', 'Campo global actualizado correctamente.');
		}

		header('Location: ' . Url::to('/admin/settings?set_id=' . ($_POST['display_set_id'] ?? '')));
		exit;
	}

	public function setActiveFieldSet()
	{
		$this->guardAdmin();

		if (!isset($_POST['_csrf']) || !\Core\Lib\Csrf::verify($_POST['_csrf'])) {
			http_response_code(403);
			echo "CSRF inválido";
			return;
		}

		$setId = (int)($_POST['set_id'] ?? 0);
		if ($setId > 0) {
			(new Setting())->set('active_global_field_set_id', (string)$setId);
			Flash::set('success', 'Se ha establecido la nueva versión de campos como activa.');
		} else {
			Flash::set('danger', 'ID de versión no válido.');
		}

		header('Location: ' . Url::to('/admin/settings?set_id=' . $setId));
		exit;
	}

	public function createNewFieldVersion()
	{
		$this->guardAdmin();

		if (!isset($_POST['_csrf']) || !\Core\Lib\Csrf::verify($_POST['_csrf'])) {
			http_response_code(403);
			echo "CSRF inválido";
			return;
		}

		$newVersionName = trim($_POST['version_name'] ?? '');
		$sourceSetId = (int)($_POST['source_set_id'] ?? 0);

		$fieldSetModel = new GlobalFieldSet();
		$newSetId = $fieldSetModel->create($newVersionName);
		$fieldSetModel->copyFields($sourceSetId, $newSetId);

		Flash::set('success', 'Nueva versión "' . htmlspecialchars($newVersionName) . '" creada. Ahora puedes editarla. No olvides activarla cuando esté lista.');
		header('Location: ' . Url::to('/admin/settings?set_id=' . $newSetId));
		exit;
	}

	public function previewFullSurvey(string $setId)
	{
		$this->guardAdmin();

		// 1. Obtener los campos globales de la versión a previsualizar
		$globalFields = (new GlobalFormField())->findAllOrderedBySetId((int)$setId);

		// 2. Obtener un formulario publicado para usar como base
		$formModel = new Form();
		$form = $formModel->findFirstPublished();

		if (!$form) {
			Flash::set('warning', 'No se puede previsualizar porque no hay ningún formulario de diagnóstico en estado "Publicado".');
			header('Location: ' . Url::to('/admin/settings?set_id=' . $setId));
			exit;
		}

		// 3. Obtener la estructura de ese formulario
		$structure = $formModel->getFormStructure((int)$form['id']);

		// 4. Preparar datos simulados para la vista
		$mockInvitation = [
			'participant_name' => 'Usuario de Prueba',
			'manager_name' => 'Responsable de Prueba'
		];

		// 5. Renderizar la vista de previsualización
		$this->layout = 'survey_layout'; // Usar el layout específico para diagnósticos
		$this->view('survey/show', [
			'title' => 'Previsualización de Diagnóstico',
			'form' => $form,
			'invitation' => $mockInvitation,
			'structure' => $structure,
			'globalFields' => $globalFields,
			'token' => 'preview_token' // Token simulado
		]);
	}
}
