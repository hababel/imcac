<?php

namespace Core\Lib;

class Url
{
	public static function base(): string
	{
		$dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
		if ($dir === '/' || $dir === '.') $dir = '';
		return $dir;
	}

	public static function to(string $path = '/'): string
	{
		$path = '/' . ltrim($path, '/');
		return self::base() . $path;
	}
}
