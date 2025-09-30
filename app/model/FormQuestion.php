<?php

namespace App\Model;

use Core\Lib\Model;

class FormQuestion extends Model
{
	public function create(int $groupId, string $question): int
	{
		return $this->insert('form_questions', [
			'group_id' => $groupId,
			'question' => $question
		]);
	}
}
