<div class="container-fluid">
	<div class="d-flex justify-content-between align-items-center mb-4">
		<h1 class="h3 mb-0">Gestor de Diagnósticos</h1>
		<a href="<?= \Core\Lib\Url::to('/admin/forms/create') ?>" class="btn btn-primary">Crear Nuevo Diagnóstico</a>
	</div>

	<?php if (empty($forms)): ?>
		<div class="card">
			<div class="card-body text-center">
				<h5 class="card-title">No hay diagnósticos</h5>
				<p class="card-text">Aún no se ha creado ningún formulario de diagnóstico. ¡Crea el primero!</p>
				<a href="<?= \Core\Lib\Url::to('/admin/forms/create') ?>" class="btn btn-primary">Crear Nuevo Diagnóstico</a>
			</div>
		</div>
	<?php else: ?>
		<div class="card">
			<div class="list-group list-group-flush">
				<?php foreach ($forms as $form): ?>
					<a href="<?= \Core\Lib\Url::to('/admin/forms/edit/' . $form['id']) ?>" class="list-group-item list-group-item-action">
						<div class="d-flex w-100 justify-content-between">
							<h5 class="mb-1"><?= htmlspecialchars($form['name']) ?></h5>
							<?php
							$status = $form['status'] ?? 'DRAFT';
							$badgeClass = 'bg-secondary';
							if ($status === 'PUBLISHED') $badgeClass = 'bg-success';
							if ($status === 'DISABLED') $badgeClass = 'bg-danger';
							?>
							<span class="badge <?= $badgeClass ?>">
								<?= htmlspecialchars($status) ?>
							</span>
						</div>
						<p class="mb-1"><?= htmlspecialchars($form['description']) ?></p>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
</div>