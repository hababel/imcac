<?php

namespace App\Model;

use Core\Lib\Model;

class FormAnswer extends Model
{
	public function create(int $questionId, string $label, int $value, ?string $justification): int
	{
		return $this->insert('form_answers', [
			'question_id' => $questionId,
			'label' => $label,
			'value' => $value,
			'justification' => $justification
		]);
	}

	public function updateById(int $answerId, string $label, int $value, ?string $justification): int
	{
		return $this->update('form_answers', [
			'label' => $label,
			'value' => $value,
			'justification' => $justification
		], 'id = :id', ['id' => $answerId]);
	}

	public function deleteById(int $answerId): int
	{
		return $this->delete('form_answers', 'id = :id', ['id' => $answerId]);
	}
}
