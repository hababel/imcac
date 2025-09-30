<?php

require __DIR__ . '/core/autoload.php';

/** ── Sesión segura ───────────────────────────────────────── */
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_set_cookie_params([
	'lifetime' => 0,
	'path' => '/',
	'domain' => 'localhost',
	'secure' => $secure,
	'httponly' => true,
	'samesite' => 'Strict'
]);
if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

/** ── URL Base para subdirectorios ────────────────────────── */
$baseUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
define('BASE_URL', $baseUrl);

/** ── Cargar helpers globales ─────────────────────────────── */
require __DIR__ . '/core/lib/helpers.php';

/** ── Cargar .env simple ──────────────────────────────────── */
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
	foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
		if (str_starts_with($line, '#') || !str_contains($line, '=')) continue;
		[$k, $v] = array_map('trim', explode('=', $line, 2));
		$_ENV[$k] = $v;
		putenv("$k=$v");
	}
}

/** ── Despachar router ────────────────────────────────────── */
require __DIR__ . '/core/config/router.php';
dispatch($routes);
