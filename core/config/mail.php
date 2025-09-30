<?php

// Configuración para el envío de correos electrónicos a través de Brevo (Sendinblue).
// Obtén tu clave API desde tu cuenta de Brevo: https://app.brevo.com/settings/keys/api

return [
	'api_key' => getenv('BREVO_API_KEY') ?: '',
	'from' => [
		'name' => getenv('MAIL_FROM_NAME') ?: 'IMCAC Platform',
		'email' => getenv('MAIL_FROM_ADDRESS') ?: 'no-reply@imcac.com',
	],
];
