<?php

namespace App\Model;

use Core\Lib\Model;

class GlobalFormField extends Model
{
	public function findAllOrderedBySetId(int $setId): array
	{
		return $this->fetchAll("SELECT * FROM global_form_fields WHERE field_set_id = ? ORDER BY sort_order ASC, id ASC", [$setId]);
	}

	public function create(string $label, ?string $placeholder, string $fieldType, bool $isRequired, ?string $optionsJson, int $setId): int
	{
		return $this->insert('global_form_fields', [
			'label' => $label,
			'placeholder' => $placeholder,
			'field_type' => $fieldType,
			'is_required' => $isRequired ? 1 : 0,
			'options' => $optionsJson,
			'field_set_id' => $setId,
			'is_active' => 1
		]);
	}

	public function updateById(int $id, string $label, ?string $placeholder, string $fieldType, bool $isRequired, ?string $optionsJson): int
	{
		return $this->update('global_form_fields', [
			'label' => $label,
			'placeholder' => $placeholder,
			'field_type' => $fieldType,
			'is_required' => $isRequired ? 1 : 0,
			'options' => $optionsJson
		], 'id = :id', ['id' => $id]);
	}
}
