<div class="container" style="max-width: 720px;">
	<h1 class="h3 mb-4">Crear Nuevo Usuario</h1>

	<div class="card">
		<div class="card-body">
			<form method="post" action="<?= \Core\Lib\Url::to('/admin/users') ?>">
				<input type="hidden" name="_csrf" value="<?= \Core\Lib\Csrf::token() ?>">
				<div class="mb-3">
					<label for="name" class="form-label">Nombre Completo</label>
					<input type="text" id="name" name="name" class="form-control" required>
				</div>
				<div class="mb-3">
					<label for="email" class="form-label">Correo Electrónico</label>
					<input type="email" id="email" name="email" class="form-control" required>
				</div>
				<div class="mb-3">
					<label for="password" class="form-label">Contraseña (mín. 8 caracteres)</label>
					<input type="password" id="password" name="password" class="form-control" required minlength="8">
				</div>
				<div class="mb-3">
					<label for="role" class="form-label">Rol</label>
					<select id="role" name="role" class="form-select">
						<option value="ENCARGADO" selected>Responsable de Equipo</option>
						<option value="ADMIN">Administrador</option>
					</select>
				</div>
				<a href="<?= \Core\Lib\Url::to('/admin/users') ?>" class="btn btn-secondary">Cancelar</a>
				<button type="submit" class="btn btn-primary">Crear Usuario</button>
			</form>
		</div>
	</div>
</div>