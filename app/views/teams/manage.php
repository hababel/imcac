<div class="container-fluid">
	<div class="d-flex justify-content-between align-items-center mb-4">
		<style>
			.cupos-vaso {
				position: relative;
				width: 80px;
				height: 120px;
				border: 3px solid #dee2e6;
				border-top: none;
				border-radius: 0 0 10px 10px;
				background-color: #f8f9fa;
				display: flex;
				align-items: center;
				justify-content: center;
			}

			.cupos-vaso-fill {
				position: absolute;
				bottom: 0;
				left: 0;
				width: 100%;
				background-color: #198754;
				/* Bootstrap success color */
				border-radius: 0 0 7px 7px;
				transition: height 0.5s ease-out;
			}
		</style>
		<div>
			<a href="<?= \Core\Lib\Url::to('/teams') ?>" class="btn btn-link ps-0">&larr; Volver a Equipos</a>
			<h1 class="h3 mb-0">Gestionar Equipo: <?= htmlspecialchars($team['name']) ?></h1>
		</div>
	</div>

	<div class="row">
		<!-- Columna de Información General -->
		<div class="col-md-7">
			<div class="card mb-4">
				<div class="card-header">
					<h5 class="card-title mb-0">Detalles del Equipo</h5>
				</div>
				<div class="card-body">
					<dl class="row">
						<dt class="col-sm-4">Nombre del Equipo</dt>
						<dd class="col-sm-8"><?= htmlspecialchars($team['name']) ?></dd>

						<dt class="col-sm-4">Responsable (Manager)</dt>
						<dd class="col-sm-8"><?= htmlspecialchars($team['manager_name']) ?> (<?= htmlspecialchars($team['manager_email']) ?>)</dd>

						<dt class="col-sm-4">Fecha de Creación</dt>
						<dd class="col-sm-8"><?= date('d/m/Y H:i', strtotime($team['created_at'])) ?></dd>

						<dt class="col-sm-4">Etapa del Proyecto</dt>
						<dd class="col-sm-8"><span class="badge bg-primary"><?= htmlspecialchars($team['current_stage']) ?></span></dd>

						<dt class="col-sm-4">Estado del Diagnóstico</dt>
						<dd class="col-sm-8">
							<?php
							$participantCount = count($participants);
							$progress = $participantCount > 0 ? ($submissionCount / $participantCount) * 100 : 0;
							?>
							<div class="progress" style="height: 20px;">
								<div class="progress-bar" role="progressbar" style="width: <?= $progress ?>%;" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100">
									<?= $submissionCount ?> de <?= $participantCount ?> respondido
								</div>
							</div>
						</dd>
					</dl>
				</div>
			</div>

			<div class="card">
				<div class="card-header">
					<h5 class="card-title mb-0">Detalles del Plan y Uso</h5>
				</div>
				<div class="card-body">
					<dl class="row">
						<dt class="col-sm-4">Plan Contratado</dt>
						<dd class="col-sm-8"><?= htmlspecialchars($team['plan_name']) ?></dd>

						<dt class="col-sm-4">Límite de Integrantes</dt>
						<dd class="col-sm-8"><?= (int)$team['size_limit'] ?></dd>

						<dt class="col-sm-4">Integrantes Actuales</dt>
						<dd class="col-sm-8"><?= count($participants) ?></dd>

						<dt class="col-sm-4">Uso de Cupos</dt>
						<dd class="col-sm-8">
							<?php
							$totalSlots = (int)$team['size_limit'];
							$usedSlots = count($participants);
							$fillPercentage = $totalSlots > 0 ? ($usedSlots / $totalSlots) * 100 : 0;
							?>
							<div class="cupos-vaso">
								<div class="cupos-vaso-fill" style="height: <?= $fillPercentage ?>%;"></div>
								<div class="position-relative text-center">
									<span class="fs-4 fw-bold"><?= $usedSlots ?></span>
									<span class="text-muted">/</span>
									<span class="text-muted"><?= $totalSlots ?></span>
								</div>
							</div>
						</dd>
					</dl>
				</div>
			</div>

			<!-- Card de pago -->
			<div class="card">
				<div class="card-header">
					<h5 class="card-title mb-0">Detalles de Pago</h5>
				</div>
				<div class="card-body">
					<?php if ($payment): ?>
						<dl class="row">
							<dt class="col-sm-4">Monto</dt>
							<dd class="col-sm-8">$<?= number_format($payment['amount'], 2) ?> USD</dd>

							<dt class="col-sm-4">Estado</dt>
							<dd class="col-sm-8">
								<?php
								$statusClass = 'text-muted';
								if ($payment['status'] === 'PAID') $statusClass = 'text-success';
								if ($payment['status'] === 'PENDING') $statusClass = 'text-warning';
								if ($payment['status'] === 'FAILED') $statusClass = 'text-danger';
								?>
								<span class="fw-bold <?= $statusClass ?>"><?= htmlspecialchars($payment['status']) ?></span>
							</dd>

							<dt class="col-sm-4">Fecha de Pago</dt>
							<dd class="col-sm-8">
								<?=  ($payment['status'] === 'PAID' && !empty($payment['created_at'])) ? date('d/m/Y H:i', strtotime($payment['created_at'])) : 'N/A' ?>
							</dd>
						</dl>
						<?php if ($payment['status'] === 'PENDING' || $payment['status'] === 'FAILED'): ?>
							<hr>
							<a href="#" class="btn btn-success">Realizar Pago Ahora</a>
							<small class="text-muted d-block mt-2">Esto te redirigirá a nuestra pasarela de pagos segura.</small>
						<?php endif; ?>
					<?php else: ?>
						<div class="alert alert-light">No hay información de pago para este equipo.</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- Columna de Participantes -->
		<div class="col-md-5">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<h5 class="card-title mb-0">Integrantes (<?= count($participants) ?>)</h5>
					<?php
					$uninvitedCount = 0;
					foreach ($participants as $p) {
						if (!$p['has_invitation'] && !$p['has_submission']) $uninvitedCount++;
					}
					?>
					<?php if ($uninvitedCount > 0): ?>
						<form method="post" action="<?= \Core\Lib\Url::to('/teams/invite/bulk') ?>" class="d-inline">
							<input type="hidden" name="_csrf" value="<?= \Core\Lib\Csrf::token() ?>">
							<input type="hidden" name="team_id" value="<?= (int)$team['id'] ?>">
							<button type="submit" class="btn btn-sm btn-outline-primary">Invitar a todos (<?= $uninvitedCount ?>)</button>
						</form>
					<?php endif; ?>
				</div>
				<ul class="list-group list-group-flush">
					<?php if (empty($participants)): ?>
						<li class="list-group-item">No hay integrantes registrados en este equipo.</li>
					<?php else: ?>
						<?php foreach ($participants as $p) : ?>
							<li class="list-group-item d-flex justify-content-between align-items-center">
								<div>
									<?= htmlspecialchars($p['name']) ?>
									<small class="text-muted d-block"><?= htmlspecialchars($p['email']) ?></small>
								</div>
								<?php
								if ($p['has_submission']) {
									echo '<span class="badge bg-success">Respuesta Recibida</span>';
								} elseif ($p['has_invitation']) {
									echo '<span class="badge bg-info text-dark">Invitación Enviada</span>';
								} else {
								?>
									<form method="post" action="<?= \Core\Lib\Url::to('/teams/invite/single/' . $p['id']) ?>" class="d-inline">
										<input type="hidden" name="_csrf" value="<?= \Core\Lib\Csrf::token() ?>">
										<button type="submit" class="btn btn-sm btn-link p-0 text-decoration-none">
											<span class="badge bg-light text-dark">Agregado <small>(Enviar invitación)</small></span>
										</button>
									</form>
								<?php
								}
								?>
							</li>
						<?php endforeach; ?>
					<?php endif; ?>
				</ul>
				<div class="card-footer">
					<h6 class="card-subtitle mb-2 text-muted">Nuevo integrante</h6>
					<form id="add-participant-form" method="post" action="<?= \Core\Lib\Url::to('/teams/add-participant') ?>">
						<input type="hidden" name="_csrf" value="<?= \Core\Lib\Csrf::token() ?>">
						<input type="hidden" name="team_id" value="<?= (int)$team['id'] ?>">
						<div class="mb-2">
							<input type="text" name="name" class="form-control form-control-sm" placeholder="Nombre" required>
						</div>
						<div class="mb-2">
							<input type="email" name="email" class="form-control form-control-sm" placeholder="Email" required>
						</div>
						<div class="btn-group d-flex">
							<button type="submit" class="btn btn-sm btn-secondary w-100">Agregar a la lista</button>
							<button type="button" class="btn btn-sm btn-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
								<span class="visually-hidden">Más opciones</span>
							</button>
							<ul class="dropdown-menu dropdown-menu-end">
								<li><a class="dropdown-item" href="#" onclick="submitForm(event, '<?= \Core\Lib\Url::to('/teams/invite') ?>')">Agregar y enviar invitación</a></li>
							</ul>
						</div>
					</form>
					<script>
						function submitForm(event, actionUrl) {
							event.preventDefault();
							const form = document.getElementById('add-participant-form');
							form.action = actionUrl;
							form.submit();
						}
					</script>
				</div>
			</div>
		</div>
	</div>
</div>