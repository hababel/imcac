<?php

namespace Core\Lib;

class Mailer
{
	private string $apiKey;
	private array $from;

	public function __construct()
	{
		$config = require __DIR__ . '/../config/mail.php';
		$this->apiKey = $config['api_key'];
		$this->from = $config['from'];
	}

	/**
	 * Envía un correo electrónico usando la API de Brevo.
	 *
	 * @param string $toEmail Email del destinatario.
	 * @param string|null $toName Nombre del destinatario (opcional).
	 * @param string $subject Asunto del correo.
	 * @param string $htmlContent Contenido HTML del correo.
	 * @return bool True si el correo fue aceptado por la API, false en caso de error.
	 */
	public function send(string $toEmail, ?string $toName, string $subject, string $htmlContent): bool
	{
		if (empty($this->apiKey) || $this->apiKey === 'TU_API_KEY_DE_BREVO') {
			// No intentar enviar si la API key no está configurada.
			// En un entorno de producción, esto debería registrar un error.
			error_log("Mailer Error: Brevo API Key is not configured.");
			return false;
		}

		$url = 'https://api.brevo.com/v3/smtp/email';

		$data = [
			'sender' => $this->from,
			'to' => [
				['email' => $toEmail, 'name' => $toName]
			],
			'subject' => $subject,
			'htmlContent' => $htmlContent
		];

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'accept: application/json',
			'api-key: ' . $this->apiKey,
			'content-type: application/json'
		]);

		curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		// Brevo devuelve 201 Created en caso de éxito.
		return $httpCode === 201;
	}
}
