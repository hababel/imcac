<div class="container-fluid">
	<div class="d-flex justify-content-between align-items-center mb-4">
		<div>
			<a href="<?= \Core\Lib\Url::to('/admin/forms/edit/' . $form['id']) ?>" class="btn btn-link ps-0">&larr; Volver al Editor</a>
			<h1 class="h3 mb-0">Previsualización: <?= htmlspecialchars($form['name']) ?></h1>
			<p class="text-muted">Esta es una vista estática del contenido del formulario. Los campos están deshabilitados.</p>
		</div>
	</div>

	<div class="card">
		<div class="card-body">
			<?php if (empty($structure)): ?>
				<div class="alert alert-warning">Este formulario aún no tiene contenido para mostrar.</div>
			<?php else: ?>
				<?php foreach ($structure as $group): ?>
					<fieldset class="mb-5">
						<legend class="h5 border-bottom pb-2 mb-4"><?= htmlspecialchars($group['name']) ?></legend>
						<?php if (!empty($group['description'])): ?>
							<p class="text-muted"><?= htmlspecialchars($group['description']) ?></p>
						<?php endif; ?>

						<?php foreach ($group['questions'] as $q_id => $question): ?>
							<div class="card card-body mb-4 bg-light">
								<p class="fw-bold mb-2"><?= htmlspecialchars($question['question']) ?></p>
								<?php if (empty($question['answers'])): ?>
									<p class="small text-danger">Esta pregunta no tiene respuestas definidas.</p>
								<?php else: ?>
									<div class="ps-3">
										<?php foreach ($question['answers'] as $answer): ?>
											<div class="form-check">
												<input class="form-check-input" type="radio" name="q_preview[<?= $q_id ?>]" id="answer_preview_<?= $answer['id'] ?>" disabled>
												<label class="form-check-label" for="answer_preview_<?= $answer['id'] ?>">
													<?= htmlspecialchars($answer['label']) ?> (Valor: <?= $answer['value'] ?>)
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
			<?php endif; ?>
		</div>
	</div>
</div>