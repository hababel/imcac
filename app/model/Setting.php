<?php

namespace App\Model;

use Core\Lib\Model;

class Setting extends Model
{
	/**
	 * Obtiene todas las configuraciones como un array asociativo.
	 */
	public function getAllAsAssoc(): array
	{
		$rows = $this->fetchAll("SELECT setting_key, setting_value FROM settings");
		$settings = [];
		foreach ($rows as $row) {
			$settings[$row['setting_key']] = $row['setting_value'];
		}
		return $settings;
	}

	/**
	 * Guarda un valor de configuración (actualiza si existe, inserta si no).
	 */
	public function set(string $key, string $value): void
	{
		$exists = $this->fetchOne("SELECT id FROM settings WHERE setting_key = ?", [$key]);
		if ($exists) {
			$this->update('settings', ['setting_value' => $value], 'setting_key = :key', ['key' => $key]);
		} else {
			$this->insert('settings', ['setting_key' => $key, 'setting_value' => $value]);
		}
	}
}
