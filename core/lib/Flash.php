<?php

namespace Core\Lib;

class Flash
{
	public static function set(string $type, string $msg): void
	{
		$_SESSION['_flash'][] = ['type' => $type, 'msg' => $msg];
	}
	public static function getAll(): array
	{
		$all = $_SESSION['_flash'] ?? [];
		unset($_SESSION['_flash']);
		return $all;
	}
}
