<div class="container" style="max-width: 480px;">
	<div class="card mt-5">
		<div class="card-body p-4">
			<h1 class="h3 mb-3 text-center">Verificar Acceso</h1>
			<p class="text-center text-muted">Ingresa el código de 6 dígitos que enviamos a tu correo electrónico.</p>

			<form method="post" action="<?= \Core\Lib\Url::to('/login/verify') ?>">
				<input type="hidden" name="_csrf" value="<?= \Core\Lib\Csrf::token() ?>">

				<div class="mb-3">
					<label for="code" class="form-label">Código de Acceso</label>
					<input type="text" id="code" name="code" class="form-control form-control-lg text-center"
						maxlength="6" inputmode="numeric" pattern="[0-9]{6}" required autofocus
						autocomplete="one-time-code">
				</div>

				<div class="d-grid">
					<button type="submit" class="btn btn-primary btn-lg">Verificar</button>
				</div>
			</form>
		</div>
	</div>
</div>