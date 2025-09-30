<div class="card">
	<div class="card-header text-center bg-white py-4">
		<h1 class="h3 mb-1"><?= htmlspecialchars($form['name']) ?></h1>
		<p class="text-muted mb-0">Bienvenido/a, <?= htmlspecialchars($invitation['participant_name']) ?></p>
	</div>
	<div class="card-body p-4 p-md-5">
		<form method="post" action="#">
			<input type="hidden" name="_csrf" value="<?= \Core\Lib\Csrf::token() ?>">
			<input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

			<!-- Campos Globales -->
			<fieldset class="mb-5">
				<legend class="h5 border-bottom pb-2 mb-4">Información adicional</legend>
				<?php foreach ($globalFields as $field): ?>
					<div class="mb-3">
						<label for="global_<?= $field['id'] ?>" class="form-label">
							<?= htmlspecialchars($field['label']) ?>
							<?php if ($field['is_required']): ?><span class="text-danger">*</span><?php endif; ?>
						</label>

						<?php if ($field['field_type'] === 'text'): ?>
							<input type="text" id="global_<?= $field['id'] ?>" name="global[<?= $field['id'] ?>]" class="form-control"
								placeholder="<?= htmlspecialchars($field['placeholder'] ?? '') ?>" <?= $field['is_required'] ? 'required' : '' ?>>

						<?php elseif ($field['field_type'] === 'textarea'): ?>
							<textarea id="global_<?= $field['id'] ?>" name="global[<?= $field['id'] ?>]" class="form-control"
								placeholder="<?= htmlspecialchars($field['placeholder'] ?? '') ?>" <?= $field['is_required'] ? 'required' : '' ?>></textarea>

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
									<input class="form-check-input" type="radio" name="global[<?= $field['id'] ?>]" id="global_<?= $field['id'] ?>_<?= $key ?>"
										value="<?= htmlspecialchars($option) ?>" <?= $field['is_required'] ? 'required' : '' ?>>
									<label class="form-check-label" for="global_<?= $field['id'] ?>_<?= $key ?>">
										<?= htmlspecialchars($option) ?>
									</label>
								</div>
							<?php endforeach; ?>

						<?php elseif ($field['field_type'] === 'range' && !empty($field['options'])):
							$rangeOpts = json_decode($field['options'], true); ?>
							<input type="range" id="global_<?= $field['id'] ?>" name="global[<?= $field['id'] ?>]" class="form-range"
								min="<?= $rangeOpts['min'] ?? 0 ?>" max="<?= $rangeOpts['max'] ?? 10 ?>" <?= $field['is_required'] ? 'required' : '' ?>>
						<?php endif; ?>

						<?php if (!empty($field['placeholder']) && !in_array($field['field_type'], ['text', 'textarea'])): ?>
							<small class="form-text text-muted"><?= htmlspecialchars($field['placeholder']) ?></small>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</fieldset>

			<!-- Preguntas del Diagnóstico -->
			<?php if (empty($structure)): ?>
				<div class="alert alert-warning">Este formulario aún no tiene contenido para mostrar.</div>
			<?php endif; ?>

			<?php foreach ($structure as $group): ?>
				<fieldset class="mb-5">
					<legend class="h5 border-bottom pb-2 mb-4"><?= htmlspecialchars($group['name']) ?></legend>
					<?php if (!empty($group['description'])): ?>
						<p class="text-muted"><?= htmlspecialchars($group['description']) ?></p>
					<?php endif; ?>

					<?php foreach ($group['questions'] as $q_id => $question): ?>
						<div class="card card-body mb-4">
							<p class="fw-bold mb-2"><?= htmlspecialchars($question['question']) ?><span class="text-danger">*</span></p>
							<?php if (empty($question['answers'])): ?>
								<p class="small text-danger">Esta pregunta no tiene respuestas definidas.</p>
							<?php else: ?>
								<div class="ps-3">
									<?php foreach ($question['answers'] as $answer): ?>
										<div class="form-check">
											<input class="form-check-input" type="radio" name="q[<?= $q_id ?>]" id="answer_<?= $answer['id'] ?>" value="<?= $answer['value'] ?>" required>
											<label class="form-check-label" for="answer_<?= $answer['id'] ?>">
												<?= htmlspecialchars($answer['label']) ?>
												<?php if (!empty($answer['justification'])): ?>
													<small class="text-muted d-block"><?= htmlspecialchars($answer['justification']) ?></small>
												<?php endif; ?>
											</label>
										</div>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</fieldset>
			<?php endforeach; ?>

			<div class="text-center">
				<button type="submit" class="btn btn-primary btn-lg">Enviar Diagnóstico</button>
			</div>
		</form>
	</div>
</div>