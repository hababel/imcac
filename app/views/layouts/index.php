<div class="container-fluid">
	<h1 class="h3 mb-4">Configuración General</h1>

	<form method="post" action="<?= \Core\Lib\Url::to('/admin/settings') ?>">
		<input type="hidden" name="_csrf" value="<?= \Core\Lib\Csrf::token() ?>">

		<div class="card">
			<div class="card-header">
				<h5 class="card-title mb-0">Campos Globales en Formularios</h5>
			</div>
			<div class="card-body">
				<p class="card-text text-muted">
					Selecciona los campos de información que se solicitarán al inicio de cada diagnóstico.
				</p>

				<div class="form-check form-switch mb-2">
					<input class="form-check-input" type="checkbox" role="switch" id="showName" name="survey_header_show_name" value="1"
						<?= ($settings['survey_header_show_name'] ?? '0') === '1' ? 'checked' : '' ?>>
					<label class="form-check-label" for="showName">Mostrar campo "Nombre Completo"</label>
				</div>

				<div class="form-check form-switch mb-2">
					<input class="form-check-input" type="checkbox" role="switch" id="showEmail" name="survey_header_show_email" value="1"
						<?= ($settings['survey_header_show_email'] ?? '0') === '1' ? 'checked' : '' ?>>
					<label class="form-check-label" for="showEmail">Mostrar campo "Correo Electrónico"</label>
				</div>

				<div class="form-check form-switch mb-2">
					<input class="form-check-input" type="checkbox" role="switch" id="showRole" name="survey_header_show_role" value="1"
						<?= ($settings['survey_header_show_role'] ?? '0') === '1' ? 'checked' : '' ?>>
					<label class="form-check-label" for="showRole">Mostrar campo "Puesto / Rol en el equipo"</label>
				</div>

			</div>
			<div class="card-footer text-end">
				<button type="submit" class="btn btn-primary">Guardar Configuración</button>
			</div>
		</div>
	</form>
</div>