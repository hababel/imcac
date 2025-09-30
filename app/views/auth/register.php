<div class="container" style="max-width:520px;">
	<h1 class="h4 mb-3">Crear cuenta</h1>

	<?php foreach (\Core\Lib\Flash::getAll() as $f): ?>
		<div class="alert alert-<?= htmlspecialchars($f['type']) ?>"><?= htmlspecialchars($f['msg']) ?></div>
	<?php endforeach; ?>

	<form method="post" action="<?= \Core\Lib\Url::to('/register') ?>" class="card card-body">
		<?php $csrf = $csrf ?? \Core\Lib\Csrf::token();
		include __DIR__ . '/../partials/csrf.php'; ?>
		<div class="mb-3">
			<label class="form-label">Nombre</label>
			<input type="text" name="name" class="form-control" required minlength="2" maxlength="100">
		</div>
		<div class="mb-3">
			<label class="form-label">Email</label>
			<input type="email" name="email" class="form-control" required maxlength="150">
		</div>
		<div class="mb-3">
			<label class="form-label">Contraseña (mín. 8)</label>
			<input type="password" name="password" class="form-control" required minlength="8">
		</div>
		<div class="mb-3">
			<label class="form-label">Confirmar contraseña</label>
			<input type="password" name="confirm" class="form-control" required minlength="8">
		</div>
		<div class="d-flex justify-content-between">
			<button class="btn btn-success">Crear cuenta</button>
			<a href="<?= \Core\Lib\Url::to('/login') ?>" class="btn btn-link">Ya tengo cuenta</a>
		</div>
	</form>
</div>