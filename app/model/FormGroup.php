<?php

namespace App\Model;

use Core\Lib\Model;

class FormGroup extends Model
{
	public function create(int $formId, string $name, string $description, float $weight): int
	{
		return $this->insert('form_groups', [
			'form_id' => $formId,
			'name' => $name,
			'description' => $description,
			'weight' => $weight
		]);
	}

	public function updateById(int $groupId, string $name, string $description, float $weight): int
	{
		return $this->update('form_groups', [
			'name' => $name,
			'description' => $description,
			'weight' => $weight
		], 'id = :id', ['id' => $groupId]);
	}
}
