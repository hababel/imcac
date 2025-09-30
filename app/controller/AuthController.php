<?php

namespace App\Controller;

use Core\Lib\Controller;
use Core\Lib\Csrf;
use Core\Lib\Flash;
use Core\Lib\Auth as AuthLib;
use Core\Lib\RateLimiter;
use Core\Lib\Url;
use App\Model\User;
use App\Model\PasswordReset;
use App\Model\AccessCode;
use Core\Lib\Mailer;

class AuthController extends Controller
{
	// No layout for auth pages

	public function showLogin()
	{
		$csrf = Csrf::token();
		$this->view('auth/login', ['title' => 'Iniciar sesión', 'csrf' => $csrf]);
	}

	public function showRegister()
	{
		$csrf = Csrf::token();
		$this->view('auth/register', ['title' => 'Crear cuenta', 'csrf' => $csrf]);
	}

	public function register()
	{
		if (!isset($_POST['_csrf']) || !\Core\Lib\Csrf::verify($_POST['_csrf'])) {
			http_response_code(400);
			echo "CSRF inválido";
			return;
		}
		$name = trim($_POST['name'] ?? '');
		$email = strtolower(trim($_POST['email'] ?? ''));
		$password = $_POST['password'] ?? '';
		$confirm  = $_POST['confirm'] ?? '';

		if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8 || $password !== $confirm) {
			Flash::set('danger', 'Datos inválidos. Revisa email y contraseña (mín. 8 y debe coincidir).');
			header('Location: ' . Url::to('/register'));
			exit;
		}

		$userModel = new User();
		if ($userModel->findByEmail($email)) {
			Flash::set('warning', 'Ese correo ya está registrado.');
			header('Location: ' . Url::to('/register'));
			exit;
		}

		$id = $userModel->create($name, $email, $password, 'ENCARGADO');
		$user = $userModel->findByEmail($email);
		AuthLib::login($user);
		Flash::set('success', 'Cuenta creada. ¡Bienvenido!');
		header('Location: ' . Url::to('/'));
		exit;
	}

	public function login()
	{
		if (!isset($_POST['_csrf']) || !Csrf::verify($_POST['_csrf'])) {
			http_response_code(400);
			echo "CSRF inválido";
			return;
		}
		$email = strtolower(trim($_POST['email'] ?? ''));
		$password = $_POST['password'] ?? '';
		$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

		$rl = new RateLimiter();
		if ($rl->isBlocked($email, $ip, 5, 10)) {
			Flash::set('danger', 'Demasiados intentos fallidos. Intenta de nuevo en 10 minutos.');
			header('Location: ' . Url::to('/login'));
			exit;
		}

		$userModel = new User();
		$user = $userModel->findByEmail($email);

		$ok = $user && password_verify($password, $user['password_hash']);
		$rl->logAttempt($email, $ip, $ok);

		if (!$ok) {
			Flash::set('danger', 'Credenciales inválidas.');
			header('Location: ' . Url::to('/login'));
			exit;
		}

		if ($user['status'] !== 'ACTIVE') {
			Flash::set('warning', 'Tu usuario está deshabilitado. Contacta al administrador.');
			header('Location: ' . Url::to('/login'));
			exit;
		}

		// Lógica de 2FA para Admin y Encargado
		if (in_array($user['role'], ['ADMIN', 'ENCARGADO'])) {
			$accessCodeModel = new AccessCode();
			$code = $accessCodeModel->createForUser((int)$user['id']);

			// Construir y enviar correo
			$bodyTemplate = file_get_contents(__DIR__ . '/../views/emails/access_code.php');
			$bodyTemplate = str_replace('{{access_code}}', $code, $bodyTemplate);

			$header = file_get_contents(__DIR__ . '/../views/emails/partials/header.php');
			$footer = file_get_contents(__DIR__ . '/../views/emails/partials/footer.php');
			$fullHtml = $header . $bodyTemplate . $footer;

			$_SESSION['2fa_user_id'] = $user['id'];
			$_SESSION['last_email_preview'] = $fullHtml;

			/* --- INICIO: Bloque temporal para mostrar correo en pantalla en lugar de enviarlo --- */
			$previewLink = Url::to('/preview/email');
			Flash::set('link', $previewLink);
			Flash::set('info', 'Se ha generado un código de acceso. Haz clic en el enlace de arriba para previsualizar el correo.');
			/* --- FIN: Bloque temporal --- */

			/* --- INICIO: Bloque original de envío de correo (actualmente comentado) ---
			$mailer = new Mailer();
			$sent = $mailer->send($user['email'], $user['name'], 'Tu código de acceso a IMCAC', $fullHtml);
			if ($sent) {
				Flash::set('info', 'Hemos enviado un código de acceso a tu correo electrónico.');
			}
			--- FIN: Bloque original --- */

			header('Location: ' . Url::to('/login/verify'));
			exit;
		}

		// Login directo para otros roles (si aplica)
		AuthLib::login($user);
		Flash::set('success', 'Ingreso correcto.');
		header('Location: ' . Url::to('/'));
		exit;
	}

	public function showVerifyCode()
	{
		if (empty($_SESSION['2fa_user_id'])) {
			header('Location: ' . Url::to('/login'));
			exit;
		}
		$this->view('auth/verify-code', ['title' => 'Verificar Código']);
	}

	public function verifyCode()
	{
		$userId = $_SESSION['2fa_user_id'] ?? null;
		$code = trim($_POST['code'] ?? '');

		if (!$userId || $code === '') {
			Flash::set('danger', 'La sesión ha expirado o el código es inválido.');
			header('Location: ' . Url::to('/login'));
			exit;
		}

		$accessCodeModel = new AccessCode();
		$validCode = $accessCodeModel->findValidByCode($userId, $code);

		if (!$validCode) {
			Flash::set('danger', 'El código es incorrecto o ha expirado.');
			header('Location: ' . Url::to('/login/verify'));
			exit;
		}

		// Código correcto: completar el login
		$accessCodeModel->markUsed((int)$validCode['id']);
		$user = (new User())->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
		AuthLib::login($user);

		unset($_SESSION['2fa_user_id']);

		Flash::set('success', 'Acceso verificado correctamente. ¡Bienvenido!');
		header('Location: ' . Url::to('/'));
		exit;
	}

	public function logout()
	{
		if (!isset($_POST['_csrf']) || !Csrf::verify($_POST['_csrf'])) {
			http_response_code(400);
			echo "CSRF inválido";
			return;
		}
		AuthLib::logout();
		Flash::set('info', 'Sesión cerrada.');
		header('Location: ' . Url::to('/login'));
		exit;
	}

	/* ===========================
   *   RECUPERAR CONTRASEÑA
   * =========================== */

	public function showForgot()
	{
		$csrf = Csrf::token();
		$this->view('auth/forgot', ['title' => 'Recuperar acceso', 'csrf' => $csrf]);
	}

	public function sendReset()
	{
		if (!isset($_POST['_csrf']) || !Csrf::verify($_POST['_csrf'])) {
			http_response_code(400);
			echo "CSRF inválido";
			return;
		}
		$email = strtolower(trim($_POST['email'] ?? ''));
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			Flash::set('danger', 'Ingresa un email válido.');
			header('Location: ' . Url::to('/forgot'));
			exit;
		}

		$userModel = new User();
		$user = $userModel->findByEmail($email);

		// No revelar si el email existe o no (seguridad)
		if (!$user) {
			Flash::set('info', 'Si el correo existe, recibirás instrucciones para recuperar el acceso.');
			header('Location: ' . Url::to('/forgot'));
			exit;
		}

		// Rate limit: max 3 solicitudes en 10 minutos por usuario
		$pr = new PasswordReset();
		$cnt = $pr->countRecentRequests((int)$user['id'], 10);
		if ($cnt >= 3) {
			Flash::set('warning', 'Has solicitado varios resets recientemente. Intenta más tarde.');
			header('Location: ' . Url::to('/forgot'));
			exit;
		}

		// Generar token y guardar hash
		$token = bin2hex(random_bytes(32));
		$pr->createToken((int)$user['id'], $token, 20); // 20 min

		// Enlace de reseteo
		$resetLink = Url::to('/reset') . '?token=' . urlencode($token) . '&email=' . urlencode($email);

		// "Enviar email": en desarrollo mostramos el link por conveniencia
		$env = getenv('APP_ENV') ?: 'local';
		if ($env !== 'production') {
			Flash::set('success', 'Link de recuperación (DEV): ' . $resetLink);
		} else {
			// Aquí integrarías el mail real con tu proveedor SMTP y plantilla HTML
			Flash::set('success', 'Si el correo existe, se enviaron instrucciones para recuperar el acceso.');
		}

		header('Location: ' . Url::to('/forgot'));
		exit;
	}

	public function showReset()
	{
		$csrf = Csrf::token();
		$token = trim($_GET['token'] ?? '');
		$email = strtolower(trim($_GET['email'] ?? ''));
		if ($token === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			Flash::set('danger', 'Enlace inválido.');
			header('Location: ' . Url::to('/forgot'));
			exit;
		}
		$this->view('auth/reset', ['title' => 'Definir nueva contraseña', 'csrf' => $csrf, 'token' => $token, 'email' => $email]);
	}

	public function performReset()
	{
		if (!isset($_POST['_csrf']) || !Csrf::verify($_POST['_csrf'])) {
			http_response_code(400);
			echo "CSRF inválido";
			return;
		}

		$token = trim($_POST['token'] ?? '');
		$email = strtolower(trim($_POST['email'] ?? ''));
		$password = $_POST['password'] ?? '';
		$confirm  = $_POST['confirm'] ?? '';

		if ($token === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			Flash::set('danger', 'Solicitud inválida.');
			header('Location: ' . Url::to('/forgot'));
			exit;
		}

		if (strlen($password) < 8 || $password !== $confirm) {
			Flash::set('danger', 'La contraseña debe tener al menos 8 caracteres y coincidir.');
			header('Location: ' . Url::to('/reset') . '?token=' . urlencode($token) . '&email=' . urlencode($email));
			exit;
		}

		$pr = new PasswordReset();
		$row = $pr->findValidByTokenAndEmail($token, $email);
		if (!$row) {
			Flash::set('danger', 'El token es inválido o ha expirado.');
			header('Location: ' . Url::to('/forgot'));
			exit;
		}

		// Actualizar la contraseña del usuario y marcar el token como usado
		$userModel = new User();
		$user = $userModel->findByEmail($email);
		if (!$user) {
			Flash::set('danger', 'Usuario no encontrado.');
			header('Location: ' . Url::to('/forgot'));
			exit;
		}

		$userModel->updatePassword((int)$user['id'], $password);
		$pr->markUsed((int)$row['id']);

		// Cerrar sesión actual si la hubiera
		AuthLib::logout();
		Flash::set('success', 'Tu contraseña fue actualizada. Ingresa con tus nuevas credenciales.');
		header('Location: ' . Url::to('/login'));
		exit;
	}
}
