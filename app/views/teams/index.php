<div class="container-fluid">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h1 class="h4 mb-0">Equipos</h1>
		<form class="d-flex gap-2" method="post" action="<?= \Core\Lib\Url::to('/teams') ?>">
			<?php $csrf = \Core\Lib\Csrf::token(); ?>
			<input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
			<input name="name" class="form-control" placeholder="Nombre del equipo" required>
			<button class="btn btn-primary">Crear</button>
		</form>
	</div>
	<!-- Los mensajes flash se mostrarán automáticamente desde el layout -->

	<?php if (empty($teams)): ?>
		<?php if (isset($user) && $user['role'] === 'ENCARGADO'): ?>
			<div class="alert alert-info">
				<h4 class="alert-heading">¡Bienvenido!</h4>
				<p>Aún no tienes equipos asignados. Para comenzar, crea tu primer equipo utilizando el formulario que se encuentra en la parte superior derecha.</p>
			</div>
		<?php else: ?>
			<div class="alert alert-secondary">No se encontraron equipos.</div>
		<?php endif; ?>
	<?php else: ?>
		<div class="card">
			<div class="card-body p-0">
				<table class="table table-striped mb-0">
					<thead>
						<tr>
							<th>Nombre</th>
							<th>Estado</th>
							<th>Etapa</th>
							<?php if (isset($user) && ($user['role'] === 'ADMIN' || $user['role'] === 'ENCARGADO')): ?>
								<th>Acciones</th>
							<?php endif; ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($teams as $t): ?>
							<tr>
								<td><?= htmlspecialchars($t['name']) ?></td>
								<td><?= htmlspecialchars($t['status']) ?></td>
								<td><?= htmlspecialchars($t['current_stage']) ?></td>
								<?php if (isset($user) && ($user['role'] === 'ADMIN' || $user['role'] === 'ENCARGADO')): ?>
									<td>
										<a href="<?= \Core\Lib\Url::to('/teams/manage/' . $t['id']) ?>" class="btn btn-sm btn-outline-secondary">Gestionar</a>
									</td>
								<?php endif; ?>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	<?php endif; ?>
</div>