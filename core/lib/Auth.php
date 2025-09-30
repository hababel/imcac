<?php

namespace Core\Lib;

class Auth
{
	public static function check(): bool
	{
		return isset($_SESSION['user']);
	}
	public static function user(): ?array
	{
		return $_SESSION['user'] ?? null;
	}
	public static function login(array $user): void
	{
		// Regenerar ID para evitar fijación de sesión
		session_regenerate_id(true);
		$_SESSION['user'] = [
			'id'    => $user['id'],
			'name'  => $user['name'],
			'email' => $user['email'],
			'role'  => $user['role'],
		];
	}
	public static function logout(): void
	{
		$_SESSION = [];
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
		}
		session_destroy();
	}
}
