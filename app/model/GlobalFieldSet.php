<?php

namespace App\Model;

use Core\Lib\Model;

class GlobalFieldSet extends Model
{
	public function findAll(): array
	{
		return $this->fetchAll("SELECT * FROM global_field_sets ORDER BY created_at DESC");
	}

	public function create(string $name): int
	{
		return $this->insert('global_field_sets', ['name' => $name]);
	}

	public function copyFields(int $sourceSetId, int $newSetId): void
	{
		$sql = "INSERT INTO global_form_fields (label, placeholder, field_type, is_required, options, field_set_id, is_active, sort_order) SELECT label, placeholder, field_type, is_required, options, ?, is_active, sort_order FROM global_form_fields WHERE field_set_id = ?";
		$this->prepared($sql, [$newSetId, $sourceSetId]);
	}
}
