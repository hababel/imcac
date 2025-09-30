<div class="container" style="max-width:520px;">
	<h1 class="h4 mb-3">Iniciar sesión</h1>

	<?php foreach (\Core\Lib\Flash::getAll() as $f): ?>
		<div class="alert alert-<?= htmlspecialchars($f['type']) ?>"><?= htmlspecialchars($f['msg']) ?></div>
	<?php endforeach; ?>

	<form method="post" action="<?= \Core\Lib\Url::to('/login') ?>" class="card card-body">
		<?php $csrf = $csrf ?? \Core\Lib\Csrf::token();
		include __DIR__ . '/../partials/csrf.php'; ?>
		<div class="mb-3">
			<label class="form-label">Email</label>
			<input type="email" name="email" class="form-control" required autofocus>
		</div>
		<div class="mb-3">
			<label class="form-label">Contraseña</label>
			<input type="password" name="password" class="form-control" required minlength="8">
		</div>
		<div class="d-flex justify-content-between align-items-center">
			<div class="d-flex gap-2">
				<button class="btn btn-primary">Entrar</button>
				<a href="<?= \Core\Lib\Url::to('/register') ?>" class="btn btn-link">Crear cuenta</a>
			</div>
			<a href="<?= \Core\Lib\Url::to('/forgot') ?>" class="small">¿Olvidaste tu contraseña?</a>
		</div>

	</form>
</div>