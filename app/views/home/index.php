<div class="container-fluid">
	<h1 class="h3 mb-4">Dashboard</h1>

	<?php if ($user['role'] === 'ADMIN'): ?>
		<a href="<?= \Core\Lib\Url::to('/preview/interactive-survey') ?>" class="btn btn-sm btn-outline-info" target="_blank">
			Previsualizar Diagnóstico Interactivo
		</a>
	<?php endif; ?>

	<?php if (\Core\Lib\Auth::user()['role'] === 'ADMIN' && isset($stats)) : ?>
		<!-- Dashboard de Administrador -->
		<div class="row">

			<!-- Formularios -->
			<div class="col-xl-4 col-md-6 mb-4">
				<div class="card border-left-primary shadow h-100 py-2">
					<div class="card-body">
						<a href="<?= \Core\Lib\Url::to('/admin/forms') ?>" class="text-decoration-none stretched-link"></a>
						<div class="row align-items-center">
							<div class="col">
								<div class="d-flex justify-content-between align-items-center">
									<div class="text-xs fw-bold text-primary text-uppercase mb-1">Formularios</div>
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-in-right text-primary opacity-75" viewBox="0 0 16 16">
										<path fill-rule="evenodd" d="M6 3.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 0-1 0v2A1.5 1.5 0 0 0 6.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-8A1.5 1.5 0 0 0 5 3.5v2a.5.5 0 0 0 1 0v-2z" />
										<path fill-rule="evenodd" d="M11.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H1.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z" />
									</svg>
								</div>
								<div class="h4 mb-0 fw-bold text-gray-800"><?= $stats['forms']['TOTAL'] ?></div>
							</div>
							<div class="col-auto">
								<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-input-cursor-text text-secondary opacity-50" viewBox="0 0 16 16">
									<path fill-rule="evenodd" d="M5 2a.5.5 0 0 1 .5.5v11a.5.5 0 0 1-1 0v-11A.5.5 0 0 1 5 2z" />
									<path d="M3.5 0A.5.5 0 0 1 4 1v14a.5.5 0 0 1-1 0V1a.5.5 0 0 1 .5-.5zm9 0a.5.5 0 0 1 .5.5v14a.5.5 0 0 1-1 0V.5a.5.5 0 0 1 .5-.5z" />
								</svg>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Respuestas Recibidas -->
			<div class="col-xl-4 col-md-6 mb-4">
				<div class="card border-left-success shadow h-100 py-2">
					<div class="card-body">
						<div class="row align-items-center">
							<div class="col">
								<div class="text-xs fw-bold text-success text-uppercase mb-1">Diagnósticos Recibidos</div>
								<div class="h4 mb-0 fw-bold text-gray-800"><?= $stats['submissions'] ?></div>
							</div>
							<div class="col-auto">
								<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-check2-all text-secondary opacity-50" viewBox="0 0 16 16">
									<path d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0l7-7zm-4.208 7-.896-.897.707-.707.543.543 6.646-6.647a.5.5 0 0 1 .708.708l-7 7a.5.5 0 0 1-.708 0z" />
									<path d="m5.354 7.146.896.897-.707.707-.897-.896a.5.5 0 1 1 .708-.708z" />
								</svg>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Equipos -->
			<div class="col-xl-4 col-md-6 mb-4">
				<div class="card border-left-info shadow h-100 py-2">
					<div class="card-body">
						<a href="<?= \Core\Lib\Url::to('/teams') ?>" class="text-decoration-none stretched-link"></a>
						<div class="row align-items-center">
							<div class="col">
								<div class="d-flex justify-content-between align-items-center">
									<div class="text-xs fw-bold text-info text-uppercase mb-1">Equipos Activos</div>
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-in-right text-info opacity-75" viewBox="0 0 16 16">
										<path fill-rule="evenodd" d="M6 3.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 0-1 0v2A1.5 1.5 0 0 0 6.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-8A1.5 1.5 0 0 0 5 3.5v2a.5.5 0 0 0 1 0v-2z" />
										<path fill-rule="evenodd" d="M11.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H1.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z" />
									</svg>
								</div>
								<div class="h4 mb-0 fw-bold text-gray-800"><?= $stats['teams'] ?></div>
							</div>
							<div class="col-auto">
								<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-collection text-secondary opacity-50" viewBox="0 0 16 16">
									<path d="M2.5 3.5a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1h-11zm2-2a.5.5 0 0 1 0-1h7a.5.5 0 0 1 0 1h-7zM0 13a1.5 1.5 0 0 0 1.5 1.5h13A1.5 1.5 0 0 0 16 13V6a1.5 1.5 0 0 0-1.5-1.5h-13A1.5 1.5 0 0 0 0 6v7zm1.5.5A.5.5 0 0 1 1 13V6a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-.5.5h-13z" />
								</svg>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Responsables -->
			<div class="col-xl-4 col-md-6 mb-4">
				<div class="card border-left-warning shadow h-100 py-2">
					<div class="card-body">
						<a href="<?= \Core\Lib\Url::to('/admin/users') ?>" class="text-decoration-none stretched-link"></a>
						<div class="row align-items-center">
							<div class="col">
								<div class="d-flex justify-content-between align-items-center">
									<div class="text-xs fw-bold text-warning text-uppercase mb-1">Responsables</div>
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-in-right text-warning opacity-75" viewBox="0 0 16 16">
										<path fill-rule="evenodd" d="M6 3.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 0-1 0v2A1.5 1.5 0 0 0 6.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-8A1.5 1.5 0 0 0 5 3.5v2a.5.5 0 0 0 1 0v-2z" />
										<path fill-rule="evenodd" d="M11.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H1.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z" />
									</svg>
								</div>
								<div class="h4 mb-0 fw-bold text-gray-800"><?= $stats['managers'] ?></div>
							</div>
							<div class="col-auto">
								<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-person-video3 text-secondary opacity-50" viewBox="0 0 16 16">
									<path d="M14 9.5a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm-6 5.7c-2.502 0-4.1-1.14-4.1-2.7C3.9 10.93 5.26 10 8 10s4.1 1.03 4.1 2.5c0 1.56-1.598 2.7-4.1 2.7Z" />
									<path d="M0 1a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1V1ZM4 10.285c.223.434.66.82 1.207 1.125.547.306 1.24.48 2.04.48.8 0 1.493-.174 2.04-.48.547-.305.984-.69 1.207-1.125A2.498 2.498 0 0 0 11.5 9.5a2.5 2.5 0 0 0-2.5-2.5h-2A2.5 2.5 0 0 0 4.5 9.5c0 .668.252 1.272.646 1.743Z" />
								</svg>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Invitados -->
			<div class="col-xl-4 col-md-6 mb-4">
				<div class="card border-left-secondary shadow h-100 py-2">
					<div class="card-body">
						<a href="<?= \Core\Lib\Url::to('/admin/users#participants') ?>" class="text-decoration-none stretched-link"></a>
						<div class="row align-items-center">
							<div class="col">
								<div class="d-flex justify-content-between align-items-center">
									<div class="text-xs fw-bold text-secondary text-uppercase mb-1">Integrantes</div>
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-in-right text-secondary opacity-75" viewBox="0 0 16 16">
										<path fill-rule="evenodd" d="M6 3.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 0-1 0v2A1.5 1.5 0 0 0 6.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-8A1.5 1.5 0 0 0 5 3.5v2a.5.5 0 0 0 1 0v-2z" />
										<path fill-rule="evenodd" d="M11.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H1.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z" />
									</svg>
								</div>
								<div class="h4 mb-0 fw-bold text-gray-800"><?= $stats['participants'] ?></div>
							</div>
							<div class="col-auto">
								<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-people text-secondary opacity-50" viewBox="0 0 16 16">
									<path d="M15 14v1H1v-1c-1.1 0-2-1-2-2V3c0-1.1.9-2 2-2h12c1.1 0 2 .9 2 2v9c0 1-1 2-2 2zm-6-6h1v1H9v-1zm-2 0h1v1H7v-1zm-2 0h1v1H5v-1zm-2 0h1v1H3v-1zm0-3h12v1H3V5zm0 4h1v1H3V9zm2 0h1v1H5V9zm2 0h1v1H7V9zm2 0h1v1H9V9zm2 0h1v1h-1V9zm2 0h1v1h-1V9zM3 3h12v1H3V3z" />
								</svg>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php else : ?>
		<!-- Dashboard de Encargado (o default) -->
		<div class="alert alert-info">Bienvenido a tu dashboard.</div>
	<?php endif; ?>
</div>