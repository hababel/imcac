<div class="container" style="max-width: 720px;">
	<h1 class="h3 mb-4">Crear Nuevo Diagnóstico</h1>

	<div class="card">
		<div class="card-body">
			<form method="post" action="<?= \Core\Lib\Url::to('/admin/forms') ?>">
				<input type="hidden" name="_csrf" value="<?= \Core\Lib\Csrf::token() ?>">
				<div class="mb-3">
					<label for="name" class="form-label">Nombre del Diagnóstico</label>
					<input type="text" id="name" name="name" class="form-control" required>
				</div>
				<div class="mb-3">
					<label for="description" class="form-label">Descripción</label>
					<textarea id="description" name="description" class="form-control" rows="3"></textarea>
				</div>
				<a href="<?= \Core\Lib\Url::to('/admin/forms') ?>" class="btn btn-secondary">Cancelar</a>
				<button type="submit" class="btn btn-primary">Crear y Continuar al Editor</button>
			</form>
		</div>
	</div>
</div>