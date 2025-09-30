<?php

namespace Core\Lib;

class Controller
{
	/** @var string|null El nombre del archivo de layout a usar (sin .php) */
	protected ?string $layout = 'layout';

	public function __construct()
	{ /* Constructor vacío por ahora */
	}

	protected function view(string $template, array $data = []): void
	{
		extract($data);
		$title = $data['title'] ?? 'IMCAC';

		ob_start();
		require __DIR__ . '/../../app/views/' . $template . '.php';
		$content = ob_get_clean();

		// Determina qué layout cargar. Si el layout especificado no existe, no carga ninguno.
		$layoutFile = __DIR__ . '/../../app/views/layouts/' . ($this->layout ?? 'layout') . '.php';
		if (file_exists($layoutFile)) {
			require $layoutFile;
		}
	}
}
