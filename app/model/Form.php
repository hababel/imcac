<?php

namespace App\Model;

use Core\Lib\Model;

class Form extends Model
{
	public function all(): array
	{
		return $this->fetchAll("SELECT * FROM forms ORDER BY id DESC");
	}

	public function findById(int $id): ?array
	{
		return $this->fetchOne("SELECT * FROM forms WHERE id = ?", [$id]);
	}

	public function findFirstPublished(): ?array
	{
		return $this->fetchOne("SELECT * FROM forms WHERE status = 'PUBLISHED' ORDER BY id DESC LIMIT 1");
	}

	public function create(string $name, string $description): int
	{
		return $this->insert('forms', [
			'name' => $name,
			'description' => $description,
			'status' => 'DRAFT'
		]);
	}

	public function updateStatus(int $id, string $status): int
	{
		return $this->update('forms', ['status' => $status], 'id = :id', ['id' => $id]);
	}

	public function updateFormula(int $id, string $formula): int
	{
		return $this->update('forms', ['calculation_formula' => $formula], 'id = :id', ['id' => $id]);
	}

	public function updateEmailTemplate(int $id, string $templateHtml): int
	{
		return $this->update('forms', ['email_template_html' => $templateHtml], 'id = :id', ['id' => $id]);
	}

	public function getFormStructure(int $formId): array
	{
		$groupsSql = "SELECT * FROM form_groups WHERE form_id = ? ORDER BY id ASC";
		$groups = $this->fetchAll($groupsSql, [$formId]);

		if (empty($groups)) {
			return [];
		}

		$groupIds = array_column($groups, 'id');
		$placeholders = implode(',', array_fill(0, count($groupIds), '?'));

		$questionsSql = "SELECT * FROM form_questions WHERE group_id IN ($placeholders) ORDER BY id ASC";
		$questions = $this->fetchAll($questionsSql, $groupIds);

		$questionIds = array_column($questions, 'id');
		$answers = [];
		if (!empty($questionIds)) {
			$placeholders = implode(',', array_fill(0, count($questionIds), '?'));
			$answersSql = "SELECT * FROM form_answers WHERE question_id IN ($placeholders) ORDER BY id ASC";
			$answerRows = $this->fetchAll($answersSql, $questionIds);
			foreach ($answerRows as $answer) {
				$answers[$answer['question_id']][] = $answer;
			}
		}

		$questionsById = [];
		foreach ($questions as $question) {
			$questionsById[$question['id']] = [
				'id' => $question['id'],
				'question' => $question['question'],
				'answers' => $answers[$question['id']] ?? []
			];
		}

		$structure = [];
		foreach ($groups as $group) {
			$groupQuestions = [];
			foreach ($questions as $q) {
				if ($q['group_id'] == $group['id']) {
					$groupQuestions[$q['id']] = $questionsById[$q['id']];
				}
			}
			$structure[] = [
				'id' => $group['id'],
				'name' => $group['name'],
				'description' => $group['description'],
				'weight' => $group['weight'],
				'questions' => $groupQuestions
			];
		}
		return $structure;
	}

	public function getStatsByStatus(): array
	{
		$sql = "SELECT status, COUNT(*) as count FROM forms GROUP BY status";
		$rows = $this->fetchAll($sql);

		$stats = [
			'DRAFT' => 0,
			'PUBLISHED' => 0,
			'DISABLED' => 0,
			'TOTAL' => 0,
		];

		foreach ($rows as $row) {
			$stats[$row['status']] = (int)$row['count'];
			$stats['TOTAL'] += (int)$row['count'];
		}
		return $stats;
	}
}
