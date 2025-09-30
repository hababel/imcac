<?php

namespace Core\Lib;

class Csrf
{
	public static function token(): string
	{
		if (session_status() !== PHP_SESSION_ACTIVE) {
			session_start();
		}
		$t = bin2hex(random_bytes(16));
		$_SESSION['_csrf'][$t] = time();
		return $t;
	}
	public static function verify(string $token, int $ttl = 1200): bool
	{
		if (session_status() !== PHP_SESSION_ACTIVE) {
			session_start();
		}
		if (!isset($_SESSION['_csrf'][$token])) return false;
		$ok = (time() - $_SESSION['_csrf'][$token]) <= $ttl;
		unset($_SESSION['_csrf'][$token]);
		return $ok;
	}
}
