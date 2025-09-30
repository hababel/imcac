<!doctype html>
<html lang="es">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="<?= \Core\Lib\Url::to('assets/css/custom.css') ?>">
	<title><?= htmlspecialchars($title ?? 'IMCAC') ?></title>
	<style>
		body {
			min-height: 100vh;
		}

		aside {
			min-width: 250px;
			min-height: 100vh;
		}
	</style>
</head>

<body>
	<div class="d-flex">
		<aside class="bg-dark text-white p-3">
			<h5 class="mb-3">IMCAC</h5>
			<nav class="nav nav-pills flex-column gap-1">
				<?php if (\Core\Lib\Auth::check()) : ?>
					<a class="nav-link text-white" href="<?= \Core\Lib\Url::to('/') ?>">Dashboard</a>
					<a class="nav-link text-white" href="<?= \Core\Lib\Url::to('/teams') ?>">Equipos</a>
					<a class="nav-link text-white" href="<?= \Core\Lib\Url::to('/health') ?>">Health</a>
					<?php if (\Core\Lib\Auth::user()['role'] === 'ADMIN'): ?>
						<hr class="my-2">
						<a class="nav-link text-white" href="<?= \Core\Lib\Url::to('/admin/forms') ?>">Gestor Diagnósticos</a>
						<a class="nav-link text-white" href="<?= \Core\Lib\Url::to('/admin/users') ?>">Gestión de Usuarios</a>
						<a class="nav-link text-white" href="<?= \Core\Lib\Url::to('/admin/settings') ?>">Configuración</a>
					<?php endif; ?>
				<?php else : ?>
					<a class="nav-link text-white" href="<?= \Core\Lib\Url::to('/login') ?>">Login</a>
					<a class="nav-link text-white" href="<?= \Core\Lib\Url::to('/register') ?>">Registro</a>
				<?php endif; ?>
			</nav>
		</aside>
		<main class="flex-grow-1 p-4">
			<header class="d-flex justify-content-between align-items-center mb-3">
				<div></div>
				<div>
					<?php if (\Core\Lib\Auth::check()): $u = \Core\Lib\Auth::user(); ?>
						<span class="me-2">Hola, <strong><?= htmlspecialchars($u['name']) ?></strong></span>
						<form method="post" action="<?= \Core\Lib\Url::to('/logout') ?>" class="d-inline">
							<?php $csrf = \Core\Lib\Csrf::token(); ?>
							<input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
							<button class="btn btn-outline-danger btn-sm">Salir</button>
						</form>
					<?php else: ?>
						<a href="<?= \Core\Lib\Url::to('/login') ?>" class="btn btn-outline-primary btn-sm">Entrar</a>
						<a href="<?= \Core\Lib\Url::to('/register') ?>" class="btn btn-primary btn-sm ms-2">Crear cuenta</a>
					<?php endif; ?>
				</div>
			</header>

			<?php foreach (\Core\Lib\Flash::getAll() as $f): ?>
				<?php if ($f['type'] === 'link'): ?>
					<div class="alert alert-info">
						<strong>Enlace de Previsualización (Temporal):</strong> <a href="<?= htmlspecialchars($f['msg']) ?>" target="_blank" class="alert-link">Ver correo generado</a>
					</div>
				<?php else: ?>
					<div class="alert alert-<?= htmlspecialchars($f['type']) ?>"><?= htmlspecialchars($f['msg']) ?></div>
				<?php endif; ?>
			<?php endforeach; ?>

			<?= $content ?>
		</main>
	</div>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>