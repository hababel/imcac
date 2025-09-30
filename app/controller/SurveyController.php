<?php

namespace App\Controller;

use Core\Lib\Controller;
use Core\Lib\Flash;
use Core\Lib\Url;
use App\Model\Invitation;
use App\Model\Form;
use App\Model\GlobalFormField;
use App\Model\Setting;
use App\Model\Submission;
use App\Model\SubmissionAnswer;
use Core\Lib\Csrf;

class SurveyController extends Controller
{
	public function start()
	{
		$token = $_GET['token'] ?? '';
		if (empty($token)) {
			// En un caso real, mostrar una vista de error bonita.
			die("Token de invitación no proporcionado.");
		}

		$invitationModel = new Invitation();
		$invitation = $invitationModel->findValidByToken($token);

		if (!$invitation) {
			die("El enlace de invitación es inválido, ha expirado o ya ha sido utilizado.");
		}

		$formModel = new Form();
		$form = $formModel->findById((int)$invitation['form_id']);

		if (!$form || $form['status'] !== 'PUBLISHED') {
			die("El diagnóstico no está disponible en este momento.");
		}

		$structure = $formModel->getFormStructure((int)$form['id']);

		// Obtener el conjunto de campos globales ACTIVO para este nuevo diagnóstico
		$settings = (new Setting())->getAllAsAssoc();
		$activeSetId = $settings['active_global_field_set_id'] ?? 1;
		$globalFields = (new GlobalFormField())->findAllOrderedBySetId($activeSetId);

		// Usamos un layout diferente para la vista pública
		$this->layout = 'survey_layout';

		$this->view('survey/show', [
			'title' => 'Diagnóstico: ' . htmlspecialchars($form['name']),
			'form' => $form,
			'invitation' => $invitation,
			'structure' => $structure,
			'globalFields' => $globalFields,
			'token' => $token,
			'activeSetId' => $activeSetId // Pasamos el ID del set a la vista
		]);
	}

	public function submit()
	{
		// Deshabilitar el layout para respuestas JSON
		$this->layout = null;
		header('Content-Type: application/json');

		// 1. Validar método y CSRF
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			http_response_code(405);
			echo json_encode(['error' => 'Método no permitido.']);
			return;
		}

		if (!isset($_POST['_csrf']) || !Csrf::verify($_POST['_csrf'])) {
			http_response_code(403);
			echo json_encode(['error' => 'Token de seguridad inválido.']);
			return;
		}

		// 2. Validar el token y obtener la invitación
		$formModel = new Form();
		$token = $_POST['token'] ?? '';
		$isPreview = ($token === 'preview_token');
		$invitation = null;
		$form = null;

		if ($isPreview) {
			// Es una previsualización: obtenemos el primer formulario publicado
			$form = $formModel->findFirstPublished();
			if (!$form) {
				http_response_code(404);
				echo json_encode(['error' => 'No se encontró un formulario publicado para la previsualización.']);
				return;
			}
		} else {
			// Es una invitación real: validamos el token
			$invitationModel = new Invitation();
			$invitation = $invitationModel->findValidByToken($token);
			if (!$invitation) {
				http_response_code(400);
				echo json_encode(['error' => 'Invitación no válida o ya utilizada.']);
				return;
			}
			$form = $formModel->findById((int)$invitation['form_id']);
		}

		// 3. Obtener el formulario y su estructura
		$structure = $formModel->getFormStructure((int)$form['id']);

		// 4. Calcular puntajes
		$submittedAnswers = $_POST['q'] ?? [];
		$groupScores = []; // SPG: Suma de Puntos por Grupo
		$groupMaxScores = []; // TPMG: Total Puntos Máximo por Grupo
		$groupWeights = []; // Pesos de cada grupo
		$selectedAnswersForResponse = []; // Para devolver al frontend

		foreach ($structure as $group) {
			$groupId = $group['id'];
			$groupScores[$groupId] = 0;
			$groupMaxScores[$groupId] = 0;
			$groupWeights[$groupId] = (float)$group['weight'];

			foreach ($group['questions'] as $qId => $question) {
				// Calcular el puntaje máximo posible para la pregunta
				if (!empty($question['answers'])) {
					$groupMaxScores[$groupId] += max(array_column($question['answers'], 'value'));
				}

				// Si se envió una respuesta para esta pregunta
				if (isset($submittedAnswers[$qId])) {
					$selectedAnswerId = (int)$submittedAnswers[$qId];
					foreach ($question['answers'] as $answer) {
						if ($answer['id'] == $selectedAnswerId) {
							$points = (int)$answer['value'];
							$groupScores[$groupId] += $points;
							$selectedAnswersForResponse[$qId] = [
								'answer_id' => $selectedAnswerId,
								'points' => $points
							];
							break;
						}
					}
				}
			}
		}

		// 5. Evaluar la fórmula de cálculo
		$formula = $form['calculation_formula'];
		foreach ($groupScores as $groupId => $score) {
			$formula = str_replace("SPG{$groupId}", (string)$score, $formula);
			$formula = str_replace("TPMG{$groupId}", (string)$groupMaxScores[$groupId], $formula);
			$formula = str_replace("WEIGHT{$groupId}", (string)$groupWeights[$groupId], $formula);
		}

		// Limpieza y evaluación segura de la fórmula
		$formula = preg_replace('/[^0-9\.\+\-\*\/\(\)\s]/', '', $formula); // Eliminar caracteres no permitidos
		$totalScore = 0;
		if (!empty($formula)) {
			// Usar eval de forma controlada. En un entorno productivo, se recomienda un parser matemático.
			try {
				// Añadir un return para que eval devuelva el resultado
				$totalScore = eval("return ($formula);");
			} catch (\Throwable $e) {
				// En caso de error en la fórmula, devolver un error claro
				http_response_code(500);
				echo json_encode(['error' => 'Error al calcular el resultado. Revise la fórmula del formulario.', 'details' => $e->getMessage()]);
				return;
			}
		}

		// 6. Guardar la sumisión (solo si NO es una previsualización)
		if (!$isPreview && $invitation) {
			// $submissionModel = new Submission();
			// $submissionId = $submissionModel->create(...);
			// (new SubmissionAnswer())->saveAll($submissionId, ...);
			// $invitationModel->markAsUsed($invitation['invitation_id']);
		}

		// 7. Devolver el resultado
		echo json_encode([
			'success' => true,
			'totalScore' => round($totalScore, 2),
			'selectedAnswers' => $selectedAnswersForResponse,
			'maturityLevel' => 'Nivel de Madurez (pendiente)', // Lógica de niveles pendiente
			'maturityExplanation' => 'Explicación del nivel (pendiente)'
		]);
	}
}
