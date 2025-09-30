<div class="container-fluid">
	<h1 class="h3 mb-4">Editor de Diagnóstico: <?= htmlspecialchars($form['name']) ?></h1>

	<style>
		.collapsible-header::after {
			flex-shrink: 0;
			width: 1.25rem;
			height: 1.25rem;
			margin-left: auto;
			content: "";
			background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23212529'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
			background-repeat: no-repeat;
			background-size: 1.25rem;
			transition: transform .2s ease-in-out;
		}

		.collapsible-header[aria-expanded="false"]::after {
			transform: rotate(-90deg);
		}

		.calc-btn {
			font-family: monospace;
			font-size: 1.1rem;
		}
	</style>

	<div class="card bg-light mb-4">
		<div class="card-body d-flex justify-content-between align-items-center">
			<div>
				<strong>Estado actual:</strong>
				<?php
				$status = $form['status'] ?? 'DRAFT';
				$badgeClass = 'bg-secondary';
				if ($status === 'PUBLISHED') $badgeClass = 'bg-success';
				if ($status === 'DISABLED') $badgeClass = 'bg-danger';
				?>
				<span class="badge <?= $badgeClass ?> fs-6"><?= htmlspecialchars($status) ?></span>
			</div>
			<div class="d-flex gap-2">
				<a href="<?= \Core\Lib\Url::to('/admin/forms/preview/' . $form['id']) ?>" class="btn btn-info" target="_blank">Previsualizar</a>
				<div class="dropdown">
					<button class="btn btn-outline-primary dropdown-toggle" type="button" id="changeStatusBtn" data-bs-toggle="dropdown" aria-expanded="false">
						Cambiar Estado
					</button>
					<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="changeStatusBtn">
						<?php if ($status !== 'PUBLISHED'): ?>
							<li><button class="dropdown-item" onclick="submitStatusForm('PUBLISHED')">Publicar</button></li>
						<?php endif; ?>
						<?php if ($status !== 'DRAFT'): ?>
							<li><button class="dropdown-item" onclick="submitStatusForm('DRAFT')">Pasar a Borrador</button></li>
						<?php endif; ?>
						<?php if ($status !== 'DISABLED'): ?>
							<li><button class="dropdown-item text-danger" onclick="submitStatusForm('DISABLED')">Deshabilitar</button></li>
						<?php endif; ?>
					</ul>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<!-- Columna para crear grupos -->
		<div class="col-md-4">
			<div class="card sticky-top" style="top: 20px;">
				<div class="card-header">
					<h5 class="card-title mb-0">Nuevo Grupo de Preguntas</h5>
				</div>
				<div class="card-body">
					<form method="post" action="<?= \Core\Lib\Url::to('/admin/forms/groups') ?>">
						<input type="hidden" name="_csrf" value="<?= \Core\Lib\Csrf::token() ?>">
						<input type="hidden" name="form_id" value="<?= (int)$form['id'] ?>">
						<div class="mb-3">
							<label class="form-label">Nombre del Grupo</label>
							<input type="text" name="name" class="form-control" required>
						</div>
						<div class="mb-3">
							<label class="form-label">Descripción</label>
							<textarea name="description" class="form-control" rows="3"></textarea>
						</div>
						<div class="mb-3">
							<label class="form-label">Peso (%)</label>
							<input type="number" name="weight" class="form-control" step="0.01" value="0.00" required>
						</div>
						<button type="submit" class="btn btn-primary">Crear Grupo</button>
					</form>
				</div>
			</div>

			<div class="card mt-4 sticky-top" style="top: 380px;">
				<div class="card-header">
					<h5 class="card-title mb-0">Fórmula de Cálculo</h5>
				</div>
				<div class="card-body">
					<form method="post" action="<?= \Core\Lib\Url::to('/admin/forms/formula/' . $form['id']) ?>">
						<input type="hidden" name="_csrf" value="<?= \Core\Lib\Csrf::token() ?>">
						<div class="mb-3">
							<label class="form-label">Variables Disponibles</label>
							<div class="p-2 bg-light border rounded" style="max-height: 150px; overflow-y: auto;">
								<?php foreach ($structure as $group): ?>
									<a href="#" class="badge bg-primary text-decoration-none variable-tag" data-variable="SPG<?= $group['id'] ?>">SPG<?= $group['id'] ?></a>
									<a href="#" class="badge bg-info text-dark text-decoration-none variable-tag" data-variable="TPMG<?= $group['id'] ?>">TPMG<?= $group['id'] ?></a>
									<a href="#" class="badge bg-secondary text-decoration-none variable-tag" data-variable="WEIGHT<?= $group['id'] ?>">WEIGHT<?= $group['id'] ?></a>
								<?php endforeach; ?>
							</div>
							<small class="form-text text-muted">
								<b>SPG:</b> Suma Puntos Grupo<br>
								<b>TPMG:</b> Total Puntos Máximo Grupo<br>
								<b>WEIGHT:</b> Peso del Grupo (%)
							</small>
						</div>
						<div class="mb-3">
							<label for="formulaTextArea" class="form-label">Expresión Matemática</label>
							<textarea id="formulaTextArea" name="formula" class="form-control" rows="4" placeholder="Ej: (SPG1 * WEIGHT1 / 100) + (SPG2 * WEIGHT2 / 100)" <?= ($form['status'] === 'PUBLISHED') ? 'disabled' : '' ?>><?= htmlspecialchars($form['calculation_formula'] ?? '') ?></textarea>
						</div>
						<!-- Calculadora -->
						<div class="row g-2 text-center">
							<div class="col-3"><button type="button" class="btn btn-light w-100 calc-btn" data-char="7">7</button></div>
							<div class="col-3"><button type="button" class="btn btn-light w-100 calc-btn" data-char="8">8</button></div>
							<div class="col-3"><button type="button" class="btn btn-light w-100 calc-btn" data-char="9">9</button></div>
							<div class="col-3"><button type="button" class="btn btn-warning w-100 calc-btn" data-char="/">/</button></div>

							<div class="col-3"><button type="button" class="btn btn-light w-100 calc-btn" data-char="4">4</button></div>
							<div class="col-3"><button type="button" class="btn btn-light w-100 calc-btn" data-char="5">5</button></div>
							<div class="col-3"><button type="button" class="btn btn-light w-100 calc-btn" data-char="6">6</button></div>
							<div class="col-3"><button type="button" class="btn btn-warning w-100 calc-btn" data-char="*">*</button></div>

							<div class="col-3"><button type="button" class="btn btn-light w-100 calc-btn" data-char="1">1</button></div>
							<div class="col-3"><button type="button" class="btn btn-light w-100 calc-btn" data-char="2">2</button></div>
							<div class="col-3"><button type="button" class="btn btn-light w-100 calc-btn" data-char="3">3</button></div>
							<div class="col-3"><button type="button" class="btn btn-warning w-100 calc-btn" data-char="-">-</button></div>

							<div class="col-3"><button type="button" class="btn btn-light w-100 calc-btn" data-char="0">0</button></div>
							<div class="col-3"><button type="button" class="btn btn-light w-100 calc-btn" data-char=".">.</button></div>
							<div class="col-3">
								<button type="button" class="btn btn-light w-100 calc-btn" data-char="(">(</button>
								<button type="button" class="btn btn-light w-100 calc-btn mt-1" data-char=")">)</button>
							</div>
							<div class="col-3"><button type="button" class="btn btn-warning w-100 calc-btn" data-char="+">+</button></div>
						</div>
						<div class="mt-3">
							<button type="submit" class="btn btn-success w-100" <?= ($form['status'] === 'PUBLISHED') ? 'disabled' : '' ?>>Guardar Fórmula</button>
						</div>
					</form>
				</div>
				<script>
					document.addEventListener('DOMContentLoaded', function() {
						const formulaTextArea = document.getElementById('formulaTextArea');
						document.querySelectorAll('.variable-tag').forEach(tag => {
							tag.addEventListener('click', function(e) {
								e.preventDefault();
								const variable = this.getAttribute('data-variable');
								const cursorPos = formulaTextArea.selectionStart;
								const textBefore = formulaTextArea.value.substring(0, cursorPos);
								const textAfter = formulaTextArea.value.substring(cursorPos);
								formulaTextArea.value = textBefore + variable + textAfter;
								formulaTextArea.focus();
								formulaTextArea.selectionEnd = cursorPos + variable.length;
							});
						});

						document.querySelectorAll('.calc-btn').forEach(button => {
							button.addEventListener('click', function() {
								const char = this.getAttribute('data-char');
								const cursorPos = formulaTextArea.selectionStart;
								const textBefore = formulaTextArea.value.substring(0, cursorPos);
								const textAfter = formulaTextArea.value.substring(cursorPos);
								formulaTextArea.value = textBefore + char + textAfter;
								formulaTextArea.focus();
								formulaTextArea.selectionEnd = cursorPos + char.length;
							});
						});
					});
				</script>
			</div>
		</div>

		<!-- Columna para mostrar la estructura -->
		<div class="col-md-8">
			<!-- Editor de Plantilla de Correo -->
			<div class="card mb-4">
				<div class="card-header">
					<h5 class="card-title mb-0">Plantilla de Correo para Invitaciones</h5>
				</div>
				<div class="card-body">
					<form method="post" action="<?= \Core\Lib\Url::to('/admin/forms/template/' . $form['id']) ?>">
						<input type="hidden" name="_csrf" value="<?= \Core\Lib\Csrf::token() ?>">
						<div class="mb-3">
							<label class="form-label">Cuerpo del Correo</label>
							<textarea id="emailTemplateEditor" name="email_template">
								<?php
								if (!empty($form['email_template_html'])) {
									echo htmlspecialchars($form['email_template_html']);
								} else {
									// Cargar plantilla por defecto
									ob_start();
									include __DIR__ . '/../emails/invitation_default.php';
									echo htmlspecialchars(ob_get_clean());
								}
								?>
							</textarea>
						</div>
						<div class="mb-3">
							<label class="form-label">Variables disponibles para usar en la plantilla:</label>
							<div>
								<span class="badge bg-light text-dark"><code>{{participant_name}}</code></span>
								<span class="badge bg-light text-dark"><code>{{team_name}}</code></span>
								<span class="badge bg-light text-dark"><code>{{invite_link}}</code></span>
							</div>
						</div>
						<div class="d-flex gap-2">
							<button type="submit" class="btn btn-success" <?= ($form['status'] === 'PUBLISHED') ? 'disabled' : '' ?>>
								Guardar Plantilla
							</button>
							<button type="button" class="btn btn-info" id="previewEmailTemplateBtn" data-bs-toggle="modal" data-bs-target="#previewEmailModal">Previsualizar</button>
						</div>
					</form>
				</div>
			</div>

			<script src="https://cdn.tiny.cloud/1/tfo4olv1ivlou91cj5waoydp9lkaozu8fv8ql0a00kc051sb/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
			<script>
				document.addEventListener('DOMContentLoaded', function() {
					tinymce.init({
						selector: '#emailTemplateEditor',
						plugins: 'link lists image code',
						toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code'
					});
				});
			</script>

			<?php if (empty($structure)): ?>
				<div class="alert alert-info">Aún no hay grupos. Comienza creando uno a la izquierda.</div>
			<?php endif; ?>

			<?php foreach ($structure as $group): ?>
				<div class="card mb-4">
					<div class="card-header d-flex justify-content-between align-items-start p-0">
						<a class="d-flex align-items-center text-decoration-none text-body p-3 flex-grow-1 collapsible-header" data-bs-toggle="collapse" href="#group-collapse-<?= $group['id'] ?>" role="button" aria-expanded="true" aria-controls="group-collapse-<?= $group['id'] ?>">
							<div>
								<h5 class="card-title mb-0"><?= htmlspecialchars($group['name']) ?> <small class="text-muted">(Peso: <?= $group['weight'] ?>%)</small></h5>
								<p class="card-text text-muted small mb-0"><?= htmlspecialchars($group['description']) ?></p>
								<?php
								$groupQuestionCount = count($group['questions']);
								$groupMaxScore = 0;
								$groupMinScore = 0;
								foreach ($group['questions'] as $question) {
									if (!empty($question['answers'])) {
										$groupMaxScore += max(array_column($question['answers'], 'value'));
										$groupMinScore += min(array_column($question['answers'], 'value'));
									}
								}
								?>
								<div class="d-flex gap-3 mt-1">
									<small class="text-muted"><strong>Preguntas:</strong> <?= $groupQuestionCount ?></small>
									<small class="text-muted"><strong>Puntaje Máximo:</strong> <span class="fw-bold text-primary"><?= $groupMaxScore ?></span></small>
									<small class="text-muted"><strong>Puntaje Mínimo:</strong> <span class="fw-bold text-warning"><?= $groupMinScore ?></span></small>
								</div>
							</div>
						</a>
						<div class="dropdown p-3">
							<button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown" aria-expanded="false">
								&#x22EE;
							</button>
							<ul class="dropdown-menu dropdown-menu-end">
								<li><a class="dropdown-item <?= ($form['status'] === 'PUBLISHED') ? 'disabled' : '' ?>" href="#"
										data-bs-toggle="modal"
										data-bs-target="#editGroupModal"
										data-group-id="<?= $group['id'] ?>"
										data-group-name="<?= htmlspecialchars($group['name']) ?>"
										data-group-description="<?= htmlspecialchars($group['description']) ?>"
										data-group-weight="<?= $group['weight'] ?>">Editar Grupo</a></li>
								<li>
									<hr class="dropdown-divider">
								</li>
								<li><a class="dropdown-item text-danger" href="#">Eliminar Grupo</a></li>
							</ul>
						</div>
					</div>
					<div class="collapse show" id="group-collapse-<?= $group['id'] ?>">
						<div class="card-body border-bottom">
							<h6 class="card-subtitle mb-2 text-muted">Nueva Pregunta</h6>
							<form method="post" action="<?= \Core\Lib\Url::to('/admin/forms/questions') ?>" class="d-flex gap-2 state-preserving-form">
								<input type="hidden" name="_csrf" value="<?= \Core\Lib\Csrf::token() ?>">
								<input type="hidden" name="form_id" value="<?= (int)$form['id'] ?>">
								<input type="hidden" name="group_id" value="<?= (int)$group['id'] ?>">
								<div class="flex-grow-1">
									<input type="text" name="question" class="form-control form-control-sm" placeholder="Texto de la pregunta" required>
								</div>
								<button type="submit" class="btn btn-sm btn-secondary">Agregar</button>
							</form>
						</div>

						<ul class="list-group list-group-flush">
							<?php if (empty($group['questions'])): ?>
								<li class="list-group-item text-muted">No hay preguntas en este grupo.</li>
							<?php else: ?>
								<?php foreach ($group['questions'] as $q): ?>
									<li class="list-group-item" id="question-<?= $q['id'] ?>">
										<div class="d-flex justify-content-between align-items-start">
											<div>
												<p class="mb-1 fw-bold"><?= htmlspecialchars($q['question']) ?></p>
												<?php
												$maxValue = 0;
												if (!empty($q['answers'])) {
													$maxValue = max(array_column($q['answers'], 'value'));
												}
												?>
												<?php if ($maxValue > 0): ?>
													<small class="text-muted">Puntaje máximo obtenible: <span class="fw-bold text-success"><?= $maxValue ?></span></small>
												<?php endif; ?>
											</div>
											<div class="dropdown">
												<button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown" aria-expanded="false">&#x22EE;</button>
												<ul class="dropdown-menu dropdown-menu-end">
													<li><a class="dropdown-item" href="#">Editar Pregunta</a></li>
													<li><a class="dropdown-item text-danger" href="#">Eliminar Pregunta</a></li>
												</ul>
											</div>
										</div>
										<!-- Respuestas -->
										<div class="mt-3 ps-3">
											<h6 class="small text-muted">Respuestas posibles:</h6>
											<?php if (empty($q['answers'])): ?>
												<p class="small text-muted fst-italic">Aún no se han definido respuestas.</p>
											<?php else: ?>
												<ul class="list-group list-group-flush">
													<?php foreach ($q['answers'] as $answer): ?>
														<li class="list-group-item d-flex justify-content-between align-items-center px-0 py-1">
															<small>
																<b><?= $answer['value'] ?>:</b> <?= htmlspecialchars($answer['label']) ?>
																<?php if (!empty($answer['justification'])): ?>
																	<i class="text-muted">(<?= htmlspecialchars($answer['justification']) ?>)</i>
																<?php endif; ?>
															</small>
															<div class="dropdown">
																<button class="btn btn-sm btn-light py-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">&#x22EE;</button>
																<ul class="dropdown-menu dropdown-menu-end">
																	<li><a class="dropdown-item" href="#"
																			data-bs-toggle="modal"
																			data-bs-target="#editAnswerModal"
																			data-answer-id="<?= $answer['id'] ?>"
																			data-answer-label="<?= htmlspecialchars($answer['label']) ?>"
																			data-answer-value="<?= $answer['value'] ?>"
																			data-answer-justification="<?= htmlspecialchars($answer['justification'] ?? '') ?>"
																			data-form-id="<?= $form['id'] ?>"
																			data-question-id="<?= $q['id'] ?>">Editar</a></li>
																	<li><a class="dropdown-item text-danger" href="#">Eliminar</a></li>
																</ul>
															</div>
														</li>
													<?php endforeach; ?>
												</ul>
											<?php endif; ?>
											<!-- Formulario para nueva respuesta -->
											<form method="post" action="<?= \Core\Lib\Url::to('/admin/forms/answers') ?>" class="row gx-2 gy-2 align-items-center mt-2 state-preserving-form">
												<input type="hidden" name="_csrf" value="<?= \Core\Lib\Csrf::token() ?>">
												<input type="hidden" name="form_id" value="<?= (int)$form['id'] ?>">
												<input type="hidden" name="question_id" value="<?= (int)$q['id'] ?>">
												<div class="col-auto"><input type="number" name="value" class="form-control form-control-sm" placeholder="Valor" required style="width: 70px;"></div>
												<div class="col-sm"><input type="text" name="label" class="form-control form-control-sm" placeholder="Etiqueta de la respuesta" required></div>
												<div class="col-sm"><input type="text" name="justification" class="form-control form-control-sm" placeholder="Justificación (opcional)"></div>
												<div class="col-auto">
													<button type="submit" class="btn btn-sm btn-outline-secondary">Añadir</button>
												</div>
											</form>
										</div>
									</li>
								<?php endforeach; ?>
							<?php endif; ?>
						</ul>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>

<!-- Modal para Previsualizar Email -->
<div class="modal fade" id="previewEmailModal" tabindex="-1" aria-labelledby="previewEmailModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="previewEmailModalLabel">Previsualización de Correo</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body p-0">
				<iframe id="previewEmailFrame" style="width: 100%; height: 70vh; border: none;"></iframe>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
			</div>
		</div>
	</div>
</div>


<!-- Formulario oculto para cambiar estado -->
<form id="statusForm" method="post" action="<?= \Core\Lib\Url::to('/admin/forms/status/' . $form['id']) ?>" style="display: none;">
	<input type="hidden" name="_csrf" value="<?= \Core\Lib\Csrf::token() ?>">
	<input type="hidden" name="status" id="statusInput">
</form>
<script>
	function submitStatusForm(newStatus) {
		document.getElementById('statusInput').value = newStatus;
		document.getElementById('statusForm').submit();
	}
</script>

<!-- Modal para Editar Grupo -->
<div class="modal fade" id="editGroupModal" tabindex="-1" aria-labelledby="editGroupModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="editGroupModalLabel">Editar Grupo de Preguntas</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<form id="editGroupForm" method="post" action="">
				<div class="modal-body">
					<input type="hidden" name="_csrf" value="<?= \Core\Lib\Csrf::token() ?>">
					<input type="hidden" name="form_id" value="<?= (int)$form['id'] ?>">

					<div class="mb-3">
						<label for="editGroupName" class="form-label">Nombre del Grupo</label>
						<input type="text" class="form-control" id="editGroupName" name="name" required>
					</div>
					<div class="mb-3">
						<label for="editGroupDescription" class="form-label">Descripción</label>
						<textarea class="form-control" id="editGroupDescription" name="description" rows="3"></textarea>
					</div>
					<div class="mb-3">
						<label for="editGroupWeight" class="form-label">Peso (%)</label>
						<input type="number" class="form-control" id="editGroupWeight" name="weight" step="0.01" required>
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

<!-- Modal para Editar Respuesta -->
<div class="modal fade" id="editAnswerModal" tabindex="-1" aria-labelledby="editAnswerModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="editAnswerModalLabel">Editar Respuesta</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<form id="editAnswerForm" method="post" action="">
				<div class="modal-body">
					<input type="hidden" name="_csrf" value="<?= \Core\Lib\Csrf::token() ?>">
					<input type="hidden" name="form_id" id="editAnswerFormId">
					<input type="hidden" name="question_id" id="editAnswerQuestionId">

					<div class="mb-3">
						<label for="editAnswerValue" class="form-label">Valor</label>
						<input type="number" class="form-control" id="editAnswerValue" name="value" required>
					</div>
					<div class="mb-3">
						<label for="editAnswerLabel" class="form-label">Etiqueta</label>
						<input type="text" class="form-control" id="editAnswerLabel" name="label" required>
					</div>
					<div class="mb-3">
						<label for="editAnswerJustification" class="form-label">Justificación (opcional)</label>
						<input type="text" class="form-control" id="editAnswerJustification" name="justification">
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


<script>
	document.addEventListener('DOMContentLoaded', function() {
		// --- Lógica para preservar el estado de los grupos colapsables ---

		// Al cargar la página, restaurar el estado
		const openGroupsJSON = sessionStorage.getItem('openFormGroups');
		if (openGroupsJSON) {
			const openGroups = JSON.parse(openGroupsJSON);
			document.querySelectorAll('.collapse').forEach(el => {
				if (!openGroups.includes(el.id)) {
					var collapseInstance = new bootstrap.Collapse(el, {
						toggle: false
					});
					collapseInstance.hide();
				}
			});
			sessionStorage.removeItem('openFormGroups'); // Limpiar para no afectar otras páginas
		}

		// Antes de enviar un formulario, guardar el estado
		document.querySelectorAll('.state-preserving-form').forEach(form => {
			form.addEventListener('submit', function() {
				const openGroups = Array.from(document.querySelectorAll('.collapse.show')).map(el => el.id);
				sessionStorage.setItem('openFormGroups', JSON.stringify(openGroups));
			});
		});
		// --- Fin de la lógica ---

		var editGroupModal = document.getElementById('editGroupModal');
		editGroupModal.addEventListener('show.bs.modal', function(event) {
			var button = event.relatedTarget;
			var form = document.getElementById('editGroupForm');
			var url = '<?= \Core\Lib\Url::to('/admin/forms/groups/') ?>' + button.getAttribute('data-group-id');
			form.action = url;

			document.getElementById('editGroupName').value = button.getAttribute('data-group-name');
			document.getElementById('editGroupDescription').value = button.getAttribute('data-group-description');
			document.getElementById('editGroupWeight').value = button.getAttribute('data-group-weight');
		});


		var editAnswerModal = document.getElementById('editAnswerModal');
		editAnswerModal.addEventListener('show.bs.modal', function(event) {
			var button = event.relatedTarget;
			var form = document.getElementById('editAnswerForm');
			var url = '<?= \Core\Lib\Url::to('/admin/forms/answers/') ?>' + button.getAttribute('data-answer-id');
			form.action = url;

			document.getElementById('editAnswerFormId').value = button.getAttribute('data-form-id');
			document.getElementById('editAnswerQuestionId').value = button.getAttribute('data-question-id');
			document.getElementById('editAnswerValue').value = button.getAttribute('data-answer-value');
			document.getElementById('editAnswerLabel').value = button.getAttribute('data-answer-label');
			document.getElementById('editAnswerJustification').value = button.getAttribute('data-answer-justification');
		});

		// --- Lógica para previsualizar plantilla de correo ---
		var previewEmailModal = document.getElementById('previewEmailModal');
		previewEmailModal.addEventListener('show.bs.modal', function() {
			let bodyHtml = tinymce.get('emailTemplateEditor').getContent();

			// Reemplazar placeholders con datos de ejemplo
			bodyHtml = bodyHtml.replace(/{{participant_name}}/g, 'Juan Pérez (Ejemplo)');
			bodyHtml = bodyHtml.replace(/{{team_name}}/g, 'Equipo de Ejemplo');
			bodyHtml = bodyHtml.replace(/{{invite_link}}/g, '#');

			<?php
			ob_start();
			include __DIR__ . '/../emails/partials/header.php';
			$header = ob_get_clean();
			ob_start();
			include __DIR__ . '/../emails/partials/footer.php';
			$footer = ob_get_clean();
			?>
			const headerHtml = <?= json_encode($header) ?>;
			const footerHtml = <?= json_encode($footer) ?>;
			const fullEmailHtml = headerHtml + bodyHtml + footerHtml;

			const iframe = document.getElementById('previewEmailFrame');
			const iframeDoc = iframe.contentWindow.document;

			iframeDoc.open();
			iframeDoc.write(fullEmailHtml);
			iframeDoc.close();
		});

	});
</script>