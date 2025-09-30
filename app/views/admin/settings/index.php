<?php
// Asegúrate de que $displaySetId esté definido antes de usarlo en la vista
if (!isset($displaySetId)) {
	$displaySetId = isset($activeSetId) ? $activeSetId : (isset($allSets[0]['id']) ? $allSets[0]['id'] : null);
}
?>
<div class="container-fluid">
	<h1 class="h3 mb-4">Configuración General</h1>

	<div class="card mb-4">
		<div class="card-header">
			<h5 class="card-title mb-0">Gestión de Versiones de Campos Globales</h5>
		</div>
		<div class="card-body">
			<div class="row align-items-end">
				<div class="col-md-4">
					<label for="version_selector" class="form-label">Selecciona una versión para ver/editar:</label>
					<select id="version_selector" class="form-select" onchange="location = '<?= \Core\Lib\Url::to('/admin/settings') ?>?set_id=' + this.value;">
						<?php foreach ($allSets as $set): ?>
							<option value="<?= $set['id'] ?>" <?= $set['id'] == $displaySetId ? 'selected' : '' ?>>
								<?= htmlspecialchars($set['name']) ?>
								<?= $set['id'] == $activeSetId ? '(Activa)' : '' ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="col-md-4">
					<?php if ($displaySetId != $activeSetId): ?>
						<form method="post" action="<?= \Core\Lib\Url::to('/admin/settings/set-active') ?>" class="d-inline">
							<input type="hidden" name="_csrf" value="<?= \Core\Lib\Csrf::token() ?>">
							<input type="hidden" name="set_id" value="<?= $displaySetId ?>">
							<button type="submit" class="btn btn-success">Activar esta versión</button>
						</form>
					<?php endif; ?>
				</div>
				<div class="col-md-4 text-end">
					<a href="<?= \Core\Lib\Url::to('/admin/settings/preview/' . $displaySetId) ?>" class="btn btn-info" target="_blank">Previsualizar Diagnóstico Completo</a>
					<button class="btn btn-outline-primary ms-2" data-bs-toggle="modal" data-bs-target="#newVersionModal">Crear Nueva Versión</button>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-8">
			<div class="card">
				<div class="card-header">
					<h5 class="card-title mb-0">Campos de la Versión Seleccionada</h5>
				</div>
				<div class="card-body">
					<ul class="list-group">
						<?php if (empty($globalFields)): ?>
							<li class="list-group-item text-muted">No hay campos globales definidos.</li>
						<?php else: ?>
							<?php foreach ($globalFields as $field): ?>
								<li class="list-group-item d-flex justify-content-between align-items-center">
									<div>
										<strong><?= htmlspecialchars($field['label']) ?></strong>
										<span class="badge bg-info text-dark"><?= htmlspecialchars($field['field_type']) ?></span>
										<?php if ($field['is_required']): ?>
											<span class="badge bg-danger">Requerido</span>
										<?php endif; ?>
										<small class="d-block text-muted"><?= htmlspecialchars($field['placeholder']) ?></small>
									</div>
									<div>
										<?php if ($displaySetId != $activeSetId): ?>
											<button class="btn btn-sm btn-outline-secondary"
												data-bs-toggle="modal"
												data-bs-target="#editGlobalFieldModal"
												data-field-id="<?= $field['id'] ?>"
												data-field-label="<?= htmlspecialchars($field['label']) ?>"
												data-field-placeholder="<?= htmlspecialchars($field['placeholder']) ?>"
												data-field-type="<?= htmlspecialchars($field['field_type']) ?>"
												data-field-required="<?= $field['is_required'] ?>"
												data-field-options="<?= htmlspecialchars(
																							$field['options'] ? (
																								in_array($field['field_type'], ['select', 'radio']) ?
																								implode("\n", json_decode($field['options'], true)) :
																								$field['options']
																							) : ''
																						) ?>">Editar</button>
										<?php else: ?>
											<span class="badge bg-light text-dark">La versión activa no se puede editar</span>
										<?php endif; ?>
										<button class="btn btn-sm btn-outline-danger">Eliminar</button>
									</div>
								</li>
							<?php endforeach; ?>
						<?php endif; ?>
					</ul>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card sticky-top" style="top: 20px;">
				<div class="card-header">
					<h5 class="card-title mb-0">Nuevo Campo Global</h5>
				</div>
				<div class="card-body">
					<form method="post" action="<?= \Core\Lib\Url::to('/admin/settings/global-field') ?>">
						<input type="hidden" name="_csrf" value="<?= \Core\Lib\Csrf::token() ?>">
						<input type="hidden" name="display_set_id" value="<?= $displaySetId ?>">
						<div class="mb-3">
							<label for="label" class="form-label">Título (Label)</label>
							<input type="text" id="label" name="label" class="form-control" placeholder="Ej: Departamento" required>
						</div>
						<div class="mb-3">
							<label for="placeholder" class="form-label">Texto de Ayuda (Placeholder)</label>
							<input type="text" id="placeholder" name="placeholder" class="form-control" placeholder="Ej: El área donde trabajas">
						</div>
						<div class="mb-3">
							<label for="field_type" class="form-label">Tipo de Campo</label>
							<select id="field_type" name="field_type" class="form-select">
								<option value="text" selected>Texto</option>
								<option value="textarea">Área de Texto</option>
								<option value="select">Menú Desplegable (Select)</option>
								<option value="radio">Opciones de Radio</option>
								<option value="range">Rango</option>
							</select>
						</div>
						<div class="mb-3 d-none" id="options-container">
							<label for="options" class="form-label">Opciones (una por línea)</label>
							<textarea id="options" name="options" class="form-control" rows="4" placeholder="Opción 1&#10;Opción 2&#10;Opción 3"></textarea>
						</div>
						<div class="mb-3 d-none" id="range-container">
							<div class="row">
								<div class="col">
									<label for="min" class="form-label">Mínimo</label>
									<input type="number" id="min" name="min" class="form-control" value="0">
								</div>
								<div class="col">
									<label for="max" class="form-label">Máximo</label>
									<input type="number" id="max" name="max" class="form-control" value="10">
								</div>
							</div>
						</div>
						<div class="form-check mb-3">
							<input class="form-check-input" type="checkbox" value="1" id="is_required" name="is_required">
							<label class="form-check-label" for="is_required">Es un campo requerido</label>
						</div>
						<?php if ($displaySetId != $activeSetId): ?>
							<button type="submit" class="btn btn-primary">Añadir Campo a esta Versión</button>
						<?php else: ?>
							<p class="text-muted small">Para añadir campos, primero crea una nueva versión a partir de esta.</p>
						<?php endif; ?>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Modal para Editar Campo Global -->
<div class="modal fade" id="editGlobalFieldModal" tabindex="-1" aria-labelledby="editGlobalFieldModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="editGlobalFieldModalLabel">Editar Campo Global</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<form id="editGlobalFieldForm" method="post" action="">
				<div class="modal-body">
					<input type="hidden" name="_csrf" value="<?= \Core\Lib\Csrf::token() ?>">
					<input type="hidden" name="display_set_id" value="<?= $displaySetId ?>">
					<div class="mb-3">
						<label for="edit_label" class="form-label">Título (Label)</label>
						<input type="text" id="edit_label" name="label" class="form-control" required>
					</div>
					<div class="mb-3">
						<label for="edit_placeholder" class="form-label">Texto de Ayuda (Placeholder)</label>
						<input type="text" id="edit_placeholder" name="placeholder" class="form-control">
					</div>
					<div class="mb-3">
						<label for="edit_field_type" class="form-label">Tipo de Campo</label>
						<select id="edit_field_type" name="field_type" class="form-select">
							<option value="text">Texto</option>
							<option value="textarea">Área de Texto</option>
							<option value="select">Menú Desplegable (Select)</option>
							<option value="radio">Opciones de Radio</option>
							<option value="range">Rango</option>
						</select>
					</div>
					<div class="mb-3 d-none" id="edit_options-container">
						<label for="edit_options" class="form-label">Opciones (una por línea)</label>
						<textarea id="edit_options" name="options" class="form-control" rows="4"></textarea>
					</div>
					<div class="mb-3 d-none" id="edit_range-container">
						<div class="row">
							<div class="col">
								<label for="edit_min" class="form-label">Mínimo</label>
								<input type="number" id="edit_min" name="min" class="form-control">
							</div>
							<div class="col">
								<label for="edit_max" class="form-label">Máximo</label>
								<input type="number" id="edit_max" name="max" class="form-control">
							</div>
						</div>
					</div>
					<div class="form-check mb-3">
						<input class="form-check-input" type="checkbox" value="1" id="edit_is_required" name="is_required">
						<label class="form-check-label" for="edit_is_required">Es un campo requerido</label>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
					<button type="submit" class="btn btn-primary">Guardar Cambios</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Modal para Nueva Versión -->
<div class="modal fade" id="newVersionModal" tabindex="-1" aria-labelledby="newVersionModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="newVersionModalLabel">Crear Nueva Versión de Campos</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<form method="post" action="<?= \Core\Lib\Url::to('/admin/settings/new-version') ?>">
				<div class="modal-body">
					<p>Se creará una copia de la versión actual (<strong><?= htmlspecialchars(current(array_filter($allSets, fn($s) => $s['id'] == $displaySetId))['name'] ?? '') ?></strong>) para que puedas modificarla sin afectar a la versión activa.</p>
					<input type="hidden" name="_csrf" value="<?= \Core\Lib\Csrf::token() ?>">
					<input type="hidden" name="source_set_id" value="<?= $displaySetId ?>">
					<div class="mb-3">
						<label for="version_name" class="form-label">Nombre de la nueva versión</label>
						<input type="text" id="version_name" name="version_name" class="form-control" value="<?= htmlspecialchars($suggestedVersionName) ?>" required>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
					<button type="submit" class="btn btn-primary">Crear y Copiar</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
	document.addEventListener('DOMContentLoaded', function() {
		function handleFieldTypeChange(selectElement, optionsContainer, rangeContainer) {
			const selectedType = selectElement.value;
			optionsContainer.classList.toggle('d-none', !['select', 'radio'].includes(selectedType));
			rangeContainer.classList.toggle('d-none', selectedType !== 'range');
		}

		const createFieldType = document.getElementById('field_type');
		const createOptionsContainer = document.getElementById('options-container');
		const createRangeContainer = document.getElementById('range-container');
		createFieldType.addEventListener('change', () => handleFieldTypeChange(createFieldType, createOptionsContainer, createRangeContainer));

		const editFieldType = document.getElementById('edit_field_type');
		const editOptionsContainer = document.getElementById('edit_options-container');
		const editRangeContainer = document.getElementById('edit_range-container');
		editFieldType.addEventListener('change', () => handleFieldTypeChange(editFieldType, editOptionsContainer, editRangeContainer));


		const editModal = document.getElementById('editGlobalFieldModal');
		editModal.addEventListener('show.bs.modal', function(event) {
			const button = event.relatedTarget;
			const form = document.getElementById('editGlobalFieldForm');

			const fieldId = button.getAttribute('data-field-id');
			form.action = '<?= \Core\Lib\Url::to('/admin/settings/global-field/') ?>' + fieldId;

			const fieldType = button.getAttribute('data-field-type');
			const options = button.getAttribute('data-field-options');

			document.getElementById('edit_label').value = button.getAttribute('data-field-label');
			document.getElementById('edit_placeholder').value = button.getAttribute('data-field-placeholder');
			document.getElementById('edit_field_type').value = fieldType;
			document.getElementById('edit_is_required').checked = button.getAttribute('data-field-required') === '1';

			if (['select', 'radio'].includes(fieldType)) {
				document.getElementById('edit_options').value = options;
			} else if (fieldType === 'range' && options) {
				const rangeOptions = JSON.parse(options);
				document.getElementById('edit_min').value = rangeOptions.min;
				document.getElementById('edit_max').value = rangeOptions.max;
			}

			// Simular el evento change para mostrar/ocultar el campo de opciones
			document.getElementById('edit_field_type').dispatchEvent(new Event('change'));
		});
	});
</script>