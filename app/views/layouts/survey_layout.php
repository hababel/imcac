<!doctype html>
<html lang="es">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="<?= \Core\Lib\Url::to('assets/css/survey.css') ?>">
	<title><?= htmlspecialchars($title ?? 'IMCAC') ?></title>
</head>

<body class="bg-light">
	<main class="container py-5" style="max-width: 800px;">
		<?= $content ?>

	</main>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>