<?php

use App\Controller\HomeController;
use App\Controller\TeamController;
use App\Controller\AuthController;

$routes = [
	['GET', '/', 'HomeController@index'],
	['GET', '/health', 'HomeController@health'],

	// Auth
	['GET', '/login', 'AuthController@showLogin'],
	['POST', '/login', 'AuthController@login'],
	['GET', '/login/verify', 'AuthController@showVerifyCode'],
	['POST', '/login/verify', 'AuthController@verifyCode'],
	['GET', '/register', 'AuthController@showRegister'],
	['POST', '/register', 'AuthController@register'],
	['POST', '/logout', 'AuthController@logout'],

	// Password reset
	['GET', '/forgot', 'AuthController@showForgot'],
	['POST', '/forgot', 'AuthController@sendReset'],
	['GET', '/reset', 'AuthController@showReset'],      // token + email en querystring
	['POST', '/reset', 'AuthController@performReset'],  // token + email + new password

	// Teams (protegidas)
	['GET', '/teams', 'TeamController@index'],
	['POST', '/teams', 'TeamController@store'],
	['GET', '/teams/manage/{id}', 'TeamController@showManage'],
	['POST', '/teams/invite', 'TeamController@invite'],
	['POST', '/teams/add-participant', 'TeamController@addParticipant'],
	['POST', '/teams/invite/single/{participantId}', 'TeamController@sendSingleInvitation'],
	['POST', '/teams/invite/bulk', 'TeamController@sendBulkInvitations'],

	// Admin - Form Builder (RESTful)
	['GET', '/admin/forms', 'FormController@index'],          // List all forms
	['GET', '/admin/forms/create', 'FormController@create'],   // Show creation form
	['POST', '/admin/forms', 'FormController@store'],          // Store a new form
	['GET', '/admin/forms/edit/{id}', 'FormController@edit'],  // Show editor for a form
	['GET', '/admin/forms/preview/{id}', 'FormController@preview'], // Preview a form
	['POST', '/admin/forms/groups', 'FormController@storeGroup'], // Store a new group
	['POST', '/admin/forms/formula/{id}', 'FormController@updateFormula'], // Update the calculation formula
	['POST', '/admin/forms/template/{id}', 'FormController@updateEmailTemplate'], // Update the email template
	['POST', '/admin/forms/questions', 'FormController@storeQuestion'], // Store a new question
	['POST', '/admin/forms/groups/{id}', 'FormController@updateGroup'], // Update a group
	['POST', '/admin/forms/answers', 'FormController@storeAnswer'], // Store a new answer
	['POST', '/admin/forms/status/{id}', 'FormController@updateStatus'], // Update form status
	['POST', '/admin/forms/answers/{id}', 'FormController@updateAnswer'], // Update an existing answer

	// Admin - User Management
	['GET', '/admin/users', 'AdminController@listUsers'],
	['GET', '/admin/users/create', 'AdminController@showCreateUser'],
	['POST', '/admin/users', 'AdminController@storeUser'],

	// Admin - General Settings
	['GET', '/admin/settings', 'AdminController@showSettings'],
	['POST', '/admin/settings', 'AdminController@saveSettings'],
	['POST', '/admin/settings/global-field', 'AdminController@storeGlobalField'],
	['POST', '/admin/settings/global-field/{id}', 'AdminController@updateGlobalField'],
	['POST', '/admin/settings/set-active', 'AdminController@setActiveFieldSet'],
	['POST', '/admin/settings/new-version', 'AdminController@createNewFieldVersion'],
	['GET', '/admin/settings/preview/{setId}', 'AdminController@previewFullSurvey'],

	// Survey (Public)
	['GET', '/survey/start', 'SurveyController@start'],
	 ['POST', '/survey/submit', 'SurveyController@submit'], // Futura ruta para guardar respuestas
	['GET', '/preview/email', 'HomeController@previewEmail'], // Ruta temporal para previsualizar correos
	['GET', '/preview/interactive-survey', 'HomeController@previewInteractiveSurvey'], // Ruta temporal para previsualizar el diagnóstico interactivo

	// DEBUG temporal (si lo sigues usando)
	// ['GET','/__debug','HomeController@debug'],
];

function dispatch(array $routes)
{
	// 1) Normaliza y recorta subcarpeta/base path
	$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
	$scriptDir  = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
	if ($scriptDir === '/' || $scriptDir === '.') $scriptDir = '';

	$path = $requestUri;
	if ($scriptDir && strpos($requestUri, $scriptDir) === 0) {
		$path = substr($requestUri, strlen($scriptDir));
	}
	if ($path === '' || $path === false) $path = '/';
	$path = '/' . ltrim($path, '/');
	if ($path === '//') $path = '/';

	// 2) Match exacto método + ruta
	// Soporte para parámetros dinámicos (ej: /teams/manage/{id})
	$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
	foreach ($routes as [$m, $routePath, $handler]) {
		$pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $routePath);
		$pattern = '#^' . $pattern . '$#';

		$matches = [];
		if ($m === $method && preg_match($pattern, $path, $matches)) {
			[$class, $action] = explode('@', $handler);
			$fqcn = "App\\Controller\\{$class}";
			$obj = new $fqcn();

			// Filtra los parámetros para pasar solo los nombrados
			$params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

			// Llama al método con los parámetros extraídos
			return call_user_func_array([$obj, $action], $params);
		}
	}

	http_response_code(404);
	// Para una mejor experiencia de usuario, podrías mostrar una vista 404
	// $this->view('errors/404');
	echo "404 Not Found (path: {$path})";
}
