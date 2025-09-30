<div class="container-fluid">
	<div class="d-flex justify-content-between align-items-center mb-4">
		<h1 class="h3 mb-0">Gestión de Usuarios</h1>
		<a href="<?= \Core\Lib\Url::to('/admin/users/create') ?>" class="btn btn-primary">Crear Nuevo Responsable</a>
	</div>

	<!-- Nav tabs -->
	<ul class="nav nav-tabs" id="userTabs" role="tablist">
		<li class="nav-item" role="presentation">
			<button class="nav-link active" id="managers-tab" data-bs-toggle="tab" data-bs-target="#managers" type="button" role="tab" aria-controls="managers" aria-selected="true">Responsables (<?= count($managers) ?>)</button>
		</li>
		<li class="nav-item" role="presentation">
			<button class="nav-link" id="participants-tab" data-bs-toggle="tab" data-bs-target="#participants" type="button" role="tab" aria-controls="participants" aria-selected="false">Integrantes (<?= count($participants) ?>)</button>
		</li>
	</ul>

	<!-- Tab panes -->
	<div class="tab-content">
		<div class="tab-pane active" id="managers" role="tabpanel" aria-labelledby="managers-tab">
			<div class="card">
				<div class="card-body p-0">
					<table class="table table-striped mb-0">
						<thead>
							<tr>
								<th>Nombre</th>
								<th>Email</th>
								<th>Estado</th>
								<th>Fecha de Registro</th>
								<th>Acciones</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($managers as $user): ?>
								<tr>
									<td><?= htmlspecialchars($user['name']) ?></td>
									<td><?= htmlspecialchars($user['email']) ?></td>
									<td><span class="badge bg-<?= $user['status'] === 'ACTIVE' ? 'success' : 'secondary' ?>"><?= $user['status'] ?></span></td>
									<td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
									<td><a href="#" class="btn btn-sm btn-outline-secondary">Editar</a></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="tab-pane" id="participants" role="tabpanel" aria-labelledby="participants-tab">
			<div class="card">
				<div class="card-body p-0">
					<table class="table table-striped mb-0">
						<thead>
							<tr>
								<th>Nombre</th>
								<th>Email</th>
								<th>Equipo</th>
								<th>Responsable del Equipo</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($participants as $user): ?>
								<tr>
									<td><?= htmlspecialchars($user['name']) ?></td>
									<td><?= htmlspecialchars($user['email']) ?></td>
									<td><?= htmlspecialchars($user['team_name'] ?? 'N/A') ?></td>
									<td><?= htmlspecialchars($user['manager_name'] ?? 'N/A') ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	document.addEventListener('DOMContentLoaded', function() {
		// Activar la pestaña correcta si viene en la URL
		const hash = window.location.hash;
		if (hash) {
			const tabTrigger = document.querySelector('.nav-tabs button[data-bs-target="' + hash + '"]');
			if (tabTrigger) {
				new bootstrap.Tab(tabTrigger).show();
			}
		}
	});
</script>