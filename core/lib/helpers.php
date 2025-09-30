<?php

/**
 * Genera una URL absoluta para la aplicación,
 * considerando el subdirectorio base.
 */
function url(string $path): string
{
	$path = '/' . ltrim($path, '/');
	return BASE_URL . $path;
}
