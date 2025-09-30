<div class="container" style="max-width:520px;">
	<h1 class="h4 mb-3">Recuperar acceso</h1>

	<?php foreach (\Core\Lib\Flash::getAll() as $f): ?>
		<div class="alert alert-<?= htmlspecialchars($f['type']) ?>"><?= $f['msg'] ?></div>
	<?php endforeach; ?>

	<form method="post" action="<?= \Core\Lib\Url::to('/forgot') ?>" class="card card-body">
		<?php $csrf = $csrf ?? \Core\Lib\Csrf::token();
		include __DIR__ . '/../partials/csrf.php'; ?>
		<div class="mb-3">
			<label class="form-label">Correo electrónico</label>
			<input type="email" name="email" class="form-control" required autofocus>
		</div>
		<div class="d-flex justify-content-between">
			<button class="btn btn-primary">Enviar instrucciones</button>
			<a href="<?= \Core\Lib\Url::to('/login') ?>" class="btn btn-link">Volver al login</a>
		</div>
	</form>
</div>