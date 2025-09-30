<?php

namespace App\Controller;

use Core\Lib\Auth;
use Core\Lib\Controller;
use Core\Lib\Flash;
use Core\Lib\Url;
use App\Model\Form;
use App\Model\FormGroup;
use App\Model\FormQuestion;
use App\Model\FormAnswer;

class FormController extends Controller
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

	public function index()
	{
		$this->guardAdmin();
		$forms = (new Form())->all();
		$this->view('forms/index', [
			'title' => 'Gestor de Diagnósticos',
			'forms' => $forms
		]);
	}
	public function edit(string $id)
	{
		$this->guardAdmin();
		$formId = (int)$id;
		$formModel = new Form();
		$form = $formModel->findById($formId);
		if (!$form) {
			Flash::set('danger', 'Formulario no encontrado.');
			header('Location: ' . Url::to('/'));
			exit;
		}

		$structure = $formModel->getFormStructure($formId);

		$this->view('forms/edit', [
			'title' => 'Editor: ' . htmlspecialchars($form['name']),
			'form' => $form,
			'structure' => $structure
		]);
	}

	public function preview(string $id)
	{
		$this->guardAdmin();
		$formId = (int)$id;
		$formModel = new Form();
		$form = $formModel->findById($formId);
		if (!$form) {
			Flash::set('danger', 'Formulario no encontrado.');
			header('Location: ' . Url::to('/admin/forms'));
			exit;
		}

		$structure = $formModel->getFormStructure($formId);

		// Esta previsualización es estática, solo muestra el contenido del formulario.
		$this->view('forms/preview', [
			'title' => 'Previsualización: ' . htmlspecialchars($form['name']),
			'form' => $form,
			'structure' => $structure
		]);
	}

	public function create()
	{
		$this->guardAdmin();
		$this->view('forms/create', ['title' => 'Crear Nuevo Diagnóstico']);
	}

	public function store()
	{
		$this->guardAdmin();
		$name = trim($_POST['name'] ?? '');
		$description = trim($_POST['description'] ?? '');

		if ($name === '') {
			Flash::set('danger', 'El nombre del diagnóstico es requerido.');
			header('Location: ' . Url::to('/admin/forms/create'));
			exit;
		}

		$formId = (new Form())->create($name, $description);
		Flash::set('success', 'Diagnóstico "' . htmlspecialchars($name) . '" creado. Ahora puedes agregarle grupos y preguntas.');
		header('Location: ' . Url::to('/admin/forms/edit/' . $formId));
		exit;
	}


	public function storeGroup()
	{
		$this->guardAdmin();
		$formId = (int)($_POST['form_id'] ?? 0);
		$name = trim($_POST['name'] ?? '');
		$description = trim($_POST['description'] ?? '');
		$weight = (float)($_POST['weight'] ?? 0);

		if ($formId <= 0 || $name === '') {
			Flash::set('danger', 'Datos del grupo inválidos.');
			header('Location: ' . Url::to('/admin/forms/edit/' . $formId));
			exit;
		}

		(new FormGroup())->create($formId, $name, $description, $weight);
		Flash::set('success', 'Grupo "' . htmlspecialchars($name) . '" creado.');
		header('Location: ' . Url::to('/admin/forms/edit/' . $formId));
		exit;
	}

	public function updateGroup(string $id)
	{
		$this->guardAdmin();
		$groupId = (int)$id;
		$formId = (int)($_POST['form_id'] ?? 0);

		// CSRF check
		if (!isset($_POST['_csrf']) || !\Core\Lib\Csrf::verify($_POST['_csrf'])) {
			http_response_code(403);
			echo "CSRF inválido";
			return;
		}

		$form = (new Form())->findById($formId);
		if ($form && $form['status'] === 'PUBLISHED') {
			Flash::set('danger', 'No se puede editar un grupo de un formulario publicado.');
			header('Location: ' . Url::to('/admin/forms/edit/' . $formId));
			exit;
		}

		$name = trim($_POST['name'] ?? '');
		$description = trim($_POST['description'] ?? '');
		$weight = (float)($_POST['weight'] ?? 0);

		if ($groupId <= 0 || $name === '') {
			Flash::set('danger', 'Datos del grupo inválidos.');
			header('Location: ' . Url::to('/admin/forms/edit/' . $formId));
			exit;
		}

		(new FormGroup())->updateById($groupId, $name, $description, $weight);
		Flash::set('success', 'Grupo "' . htmlspecialchars($name) . '" actualizado.');
		header('Location: ' . Url::to('/admin/forms/edit/' . $formId));
		exit;
	}

	public function storeQuestion()
	{
		$this->guardAdmin();
		$formId = (int)($_POST['form_id'] ?? 0);
		$groupId = (int)($_POST['group_id'] ?? 0);
		$question = trim($_POST['question'] ?? '');

		if ($groupId <= 0 || $question === '') {
			Flash::set('danger', 'Datos de la pregunta inválidos.');
			header('Location: ' . Url::to('/admin/forms/edit/' . $formId));
			exit;
		}

		(new FormQuestion())->create($groupId, $question);
		Flash::set('success', 'Pregunta agregada correctamente.');
		header('Location: ' . Url::to('/admin/forms/edit/' . $formId));
		exit;
	}

	public function storeAnswer()
	{
		$this->guardAdmin();
		$formId = (int)($_POST['form_id'] ?? 0);
		$questionId = (int)($_POST['question_id'] ?? 0);
		$label = trim($_POST['label'] ?? '');
		$value = (int)($_POST['value'] ?? 0);
		$justification = trim($_POST['justification'] ?? '');

		if ($questionId <= 0 || $label === '') {
			Flash::set('danger', 'Datos de la respuesta inválidos.');
			header('Location: ' . Url::to('/admin/forms/edit/' . $formId));
			exit;
		}

		(new FormAnswer())->create($questionId, $label, $value, $justification);
		Flash::set('success', 'Respuesta agregada correctamente.');
		// Redirigir al ancla de la pregunta para mejorar la UX
		$redirectUrl = Url::to('/admin/forms/edit/' . $formId) . '#question-' . $questionId;
		header('Location: ' . $redirectUrl);
		exit;
	}

	public function updateAnswer(string $id)
	{
		$this->guardAdmin();
		$answerId = (int)$id;

		// CSRF check
		if (!isset($_POST['_csrf']) || !\Core\Lib\Csrf::verify($_POST['_csrf'])) {
			http_response_code(403);
			echo "CSRF inválido";
			return;
		}

		$formId = (int)($_POST['form_id'] ?? 0);
		$questionId = (int)($_POST['question_id'] ?? 0);
		$label = trim($_POST['label'] ?? '');
		$value = (int)($_POST['value'] ?? 0);
		$justification = trim($_POST['justification'] ?? '');

		if ($answerId <= 0 || $questionId <= 0 || $label === '') {
			Flash::set('danger', 'Datos de la respuesta inválidos.');
			header('Location: ' . Url::to('/admin/forms/edit/' . $formId));
			exit;
		}

		(new FormAnswer())->updateById($answerId, $label, $value, $justification);
		Flash::set('success', 'Respuesta actualizada correctamente.');
		header('Location: ' . Url::to('/admin/forms/edit/' . $formId) . '#question-' . $questionId);
		exit;
	}

	public function updateStatus(string $id)
	{
		$this->guardAdmin();
		$formId = (int)$id;
		$newStatus = trim($_POST['status'] ?? '');

		if (!in_array($newStatus, ['DRAFT', 'PUBLISHED', 'DISABLED'])) {
			Flash::set('danger', 'Estado no válido.');
			header('Location: ' . Url::to('/admin/forms/edit/' . $formId));
			exit;
		}

		$formModel = new Form();

		// Validaciones antes de publicar
		if ($newStatus === 'PUBLISHED') {
			$structure = $formModel->getFormStructure($formId);
			if (empty($structure)) {
				Flash::set('danger', 'No se puede publicar: El formulario debe tener al menos un grupo.');
				header('Location: ' . Url::to('/admin/forms/edit/' . $formId));
				exit;
			}

			$totalWeight = 0;
			foreach ($structure as $group) {
				$totalWeight += (float)$group['weight'];
				if (empty($group['questions'])) {
					Flash::set('danger', 'No se puede publicar: El grupo "' . htmlspecialchars($group['name']) . '" no tiene preguntas.');
					header('Location: ' . Url::to('/admin/forms/edit/' . $formId));
					exit;
				}

				foreach ($group['questions'] as $question) {
					if (count($question['answers']) < 2) {
						Flash::set('danger', 'No se puede publicar: La pregunta "' . htmlspecialchars(substr($question['question'], 0, 30)) . '..." debe tener al menos 2 respuestas.');
						header('Location: ' . Url::to('/admin/forms/edit/' . $formId) . '#question-' . $question['id']);
						exit;
					}
					$answerValues = array_column($question['answers'], 'value');
					if (count(array_unique($answerValues)) === 1 && $answerValues[0] == 0) {
						Flash::set('danger', 'No se puede publicar: La pregunta "' . htmlspecialchars(substr($question['question'], 0, 30)) . '..." debe tener al menos una respuesta con valor distinto de cero.');
						header('Location: ' . Url::to('/admin/forms/edit/' . $formId) . '#question-' . $question['id']);
						exit;
					}
				}
			}

			if (abs($totalWeight - 100.0) > 0.01) {
				Flash::set('danger', 'No se puede publicar: La suma de los pesos de los grupos debe ser exactamente 100%. Actualmente es ' . $totalWeight . '%.');
				header('Location: ' . Url::to('/admin/forms/edit/' . $formId));
				exit;
			}
		}

		$formModel->updateStatus($formId, $newStatus);
		Flash::set('success', 'El estado del formulario se ha actualizado a "' . $newStatus . '".');
		header('Location: ' . Url::to('/admin/forms/edit/' . $formId));
		exit;
	}

	public function updateFormula(string $id)
	{
		$this->guardAdmin();
		$formId = (int)$id;

		if (!isset($_POST['_csrf']) || !\Core\Lib\Csrf::verify($_POST['_csrf'])) {
			http_response_code(403);
			echo "CSRF inválido";
			return;
		}

		$form = (new Form())->findById($formId);
		if ($form && $form['status'] === 'PUBLISHED') {
			Flash::set('danger', 'No se puede editar la fórmula de un formulario publicado.');
			header('Location: ' . Url::to('/admin/forms/edit/' . $formId));
			exit;
		}

		$formula = trim($_POST['formula'] ?? '');
		(new Form())->updateFormula($formId, $formula);
		Flash::set('success', 'Fórmula de cálculo guardada correctamente.');
		header('Location: ' . Url::to('/admin/forms/edit/' . $formId));
		exit;
	}

	public function updateEmailTemplate(string $id)
	{
		$this->guardAdmin();
		$formId = (int)$id;

		if (!isset($_POST['_csrf']) || !\Core\Lib\Csrf::verify($_POST['_csrf'])) {
			http_response_code(403);
			echo "CSRF inválido";
			return;
		}

		$form = (new Form())->findById($formId);
		if ($form && $form['status'] === 'PUBLISHED') {
			Flash::set('danger', 'No se puede editar la plantilla de un formulario publicado.');
			header('Location: ' . Url::to('/admin/forms/edit/' . $formId));
			exit;
		}

		$templateHtml = $_POST['email_template'] ?? '';

		(new Form())->updateEmailTemplate($formId, $templateHtml);

		Flash::set('success', 'Plantilla de correo guardada correctamente.');
		header('Location: ' . Url::to('/admin/forms/edit/' . $formId));
		exit;
	}
}
