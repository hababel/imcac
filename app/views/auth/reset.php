<div class="container" style="max-width:520px;">
	<h1 class="h4 mb-3">Definir nueva contraseña</h1>

	<?php foreach (\Core\Lib\Flash::getAll() as $f): ?>
		<div class="alert alert-<?= htmlspecialchars($f['type']) ?>"><?= $f['msg'] ?></div>
	<?php endforeach; ?>

	<form method="post" action="<?= \Core\Lib\Url::to('/reset') ?>" class="card card-body">
		<?php $csrf = $csrf ?? \Core\Lib\Csrf::token();
		include __DIR__ . '/../partials/csrf.php'; ?>
		<input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
		<input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

		<div class="mb-3">
			<label class="form-label">Nueva contraseña (mín. 8)</label>
			<input type="password" name="password" class="form-control" required minlength="8">
		</div>
		<div class="mb-3">
			<label class="form-label">Confirmar nueva contraseña</label>
			<input type="password" name="confirm" class="form-control" required minlength="8">
		</div>
		<div class="d-flex justify-content-between">
			<button class="btn btn-success">Actualizar contraseña</button>
			<a href="<?= \Core\Lib\Url::to('/login') ?>" class="btn btn-link">Volver al login</a>
		</div>
	</form>
</div>