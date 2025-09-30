<div class="survey-container">
	<div class="survey-header">
		<h1 class="h3 mb-1"><?= htmlspecialchars($form['name']) ?></h1>
		<p class="text-muted mb-0">Bienvenido/a, <?= htmlspecialchars($invitation['participant_name']) ?></p>
	</div>

	<form id="surveyForm" method="post" action="<?= \Core\Lib\Url::to('/survey/submit') ?>">
		<input type="hidden" name="_csrf" value="<?= \Core\Lib\Csrf::token() ?>">
		<input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

		<!-- Sticky Progress Bar -->
		<div class="progress-container-sticky">
			<div class="d-flex align-items-center">
				<div class="progress flex-grow-1" role="progressbar" aria-label="Progreso del formulario" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
					<div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%">0%</div>
				</div>
				<small id="progressCounter" class="ms-3 text-muted text-nowrap">0/0 respondidas</small>
				<button type="submit" id="submitBtn" class="btn btn-primary ms-3" disabled>Enviar</button>
			</div>
		</div>

		<!-- Información General -->
		<div class="card mb-4 info-card">
			<div class="card-header">
				<h2 class="h5 mb-0">Información General</h2>
			</div>
			<div class="card-body">
				<div class="readonly-row row mb-2">
					<div class="col-md-6"><label class="form-label small">Nombre</label><input type="text" class="form-control-plaintext" value="<?= htmlspecialchars($invitation['participant_name']) ?>" readonly></div>
					<div class="col-md-6"><label class="form-label small">Email</label><input type="text" class="form-control-plaintext" value="<?= htmlspecialchars($invitation['participant_email'] ?? 'preview@example.com') ?>" readonly></div>
					<div class="col-md-6"><label class="form-label small">Equipo</label><input type="text" class="form-control-plaintext" value="<?= htmlspecialchars($invitation['team_name'] ?? $form['name']) ?>" readonly></div>
					<div class="col-md-6"><label class="form-label small">Fecha</label><input type="text" class="form-control-plaintext" value="<?= date('d/m/Y H:i') ?>" readonly></div>
				</div>
				<hr>
				<!-- Campos Globales -->
				<?php foreach ($globalFields as $field): ?>
					<div class="mb-3">
						<label for="global_<?= $field['id'] ?>" class="form-label fw-bold">
							<?= htmlspecialchars($field['label']) ?>
							<?php if ($field['is_required']): ?><span class="text-danger">*</span><?php endif; ?>
						</label>
						<?php if ($field['field_type'] === 'text'): ?>
							<input type="text" id="global_<?= $field['id'] ?>" name="global[<?= $field['id'] ?>]" class="form-control" placeholder="<?= htmlspecialchars($field['placeholder'] ?? '') ?>" <?= $field['is_required'] ? 'required' : '' ?>>
						<?php elseif ($field['field_type'] === 'textarea'): ?>
							<textarea id="global_<?= $field['id'] ?>" name="global[<?= $field['id'] ?>]" class="form-control" placeholder="<?= htmlspecialchars($field['placeholder'] ?? '') ?>" <?= $field['is_required'] ? 'required' : '' ?>></textarea>
						<?php elseif ($field['field_type'] === 'select' && !empty($field['options'])): ?>
							<select id="global_<?= $field['id'] ?>" name="global[<?= $field['id'] ?>]" class="form-select" <?= $field['is_required'] ? 'required' : '' ?>>
								<option value="">Seleccionar...</option>
								<?php foreach (json_decode($field['options']) as $option): ?>
									<option value="<?= htmlspecialchars($option) ?>"><?= htmlspecialchars($option) ?></option>
								<?php endforeach; ?>
							</select>
						<?php elseif ($field['field_type'] === 'radio' && !empty($field['options'])): ?>
							<?php foreach (json_decode($field['options']) as $key => $option): ?>
								<div class="form-check">
									<input class="form-check-input" type="radio" name="global[<?= $field['id'] ?>]" id="global_<?= $field['id'] ?>_<?= $key ?>" value="<?= htmlspecialchars($option) ?>" <?= $field['is_required'] ? 'required' : '' ?>>
									<label class="form-check-label" for="global_<?= $field['id'] ?>_<?= $key ?>"><?= htmlspecialchars($option) ?></label>
								</div>
							<?php endforeach; ?>
						<?php elseif ($field['field_type'] === 'range' && !empty($field['options'])): $rangeOpts = json_decode($field['options'], true); ?>
							<input type="range" id="global_<?= $field['id'] ?>" name="global[<?= $field['id'] ?>]" class="form-range" min="<?= $rangeOpts['min'] ?? 0 ?>" max="<?= $rangeOpts['max'] ?? 10 ?>" <?= $field['is_required'] ? 'required' : '' ?>>
						<?php endif; ?>
						<?php if (!empty($field['placeholder']) && !in_array($field['field_type'], ['text', 'textarea'])): ?>
							<small class="form-text text-muted"><?= htmlspecialchars($field['placeholder']) ?></small>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- Preguntas del Diagnóstico -->
		<?php if (empty($structure)): ?>
			<div class="alert alert-warning">Este formulario aún no tiene contenido para mostrar.</div>
		<?php else: ?>
			<?php foreach ($structure as $group): ?>
				<div class="card question-card">
					<div class="card-header">
						<h2 class="h5 mb-0"><?= htmlspecialchars($group['name']) ?></h2>
						<?php if (!empty($group['description'])): ?>
							<small class="text-muted d-block"><?= htmlspecialchars($group['description']) ?></small>
						<?php endif; ?>
					</div>
					<div class="card-body">
						<?php foreach ($group['questions'] as $q_id => $question): ?>
							<div class="question-item">
								<h3 class="h6"><?= htmlspecialchars($question['question']) ?><span class="text-danger">*</span></h3>
								<div class="option-list" data-question-id="<?= $q_id ?>">
									<?php if (empty($question['answers'])): ?>
										<p class="small text-danger">Esta pregunta no tiene respuestas definidas.</p>
									<?php else: ?>
										<?php foreach ($question['answers'] as $answer) : ?>
											<?php
											// Determinar la clase de color y el icono según el puntaje
											$feedback_class = '';
											$feedback_icon = '';
											if ($answer['value'] == 10) {
												$feedback_class = 'text-success-emphasis';
												$feedback_icon = 'bi-check-circle-fill';
											} elseif ($answer['value'] == 5) {
												$feedback_class = 'text-warning-emphasis';
												$feedback_icon = 'bi-exclamation-triangle-fill';
											} else {
												$feedback_class = 'text-danger-emphasis';
												$feedback_icon = 'bi-x-circle-fill';
											}
											?>
											<div class="form-check option border rounded mb-2">
												<label class="form-check-label w-100 p-3" for="answer_<?= $answer['id'] ?>">
													<input class="form-check-input" type="radio" name="q[<?= $q_id ?>]" id="answer_<?= $answer['id'] ?>" value="<?= $answer['id'] ?>" data-points="<?= $answer['value'] ?>" required>
													<?= htmlspecialchars($answer['label']) ?>
													<?php if (!empty($answer['justification'])): ?>
														<div class="feedback <?= $feedback_class ?>"><i class="bi <?= $feedback_icon ?> me-2"></i><?= htmlspecialchars($answer['justification']) ?></div>
													<?php endif; ?>
												</label>
											</div>
										<?php endforeach; ?>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>

		<div class="d-grid gap-2 mt-4">
			<button type="submit" class="btn btn-primary btn-lg" id="enviarBtn">Enviar Diagnóstico</button>
		</div>
	</form>
</div>

<script>
	document.addEventListener('DOMContentLoaded', function() {
		const form = document.getElementById('surveyForm');
		const progressBar = document.getElementById('progressBar');
		const progressCounter = document.getElementById('progressCounter');
		const submitBtn = document.getElementById('submitBtn');

		const requiredInputs = form.querySelectorAll('input[required], select[required], textarea[required]');
		const totalQuestions = new Set(Array.from(requiredInputs).map(input => input.name)).size;

		function updateProgress() {
			const answeredQuestions = new Set(
				Array.from(form.querySelectorAll('input[required]:checked, select[required], textarea[required]'))
				.filter(input => {
					if (input.type === 'radio' || input.type === 'checkbox') return input.checked;
					return input.value.trim() !== '';
				})
				.map(input => input.name)
			).size;

			const percentage = totalQuestions > 0 ? (answeredQuestions / totalQuestions) * 100 : 0;

			progressBar.style.width = percentage + '%';
			progressBar.textContent = Math.round(percentage) + '%';
			progressCounter.textContent = `${answeredQuestions}/${totalQuestions} respondidas`;

			if (answeredQuestions === totalQuestions) {
				submitBtn.disabled = false;
				progressBar.classList.add('bg-success');
			} else {
				submitBtn.disabled = true;
				progressBar.classList.remove('bg-success');
			}
		}

		form.addEventListener('change', updateProgress);
		form.addEventListener('input', updateProgress); // For textareas and text inputs

		// Initial check
		updateProgress();
	});
</script>