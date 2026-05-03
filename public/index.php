<?php

/**
 * ============================================================
 *  CLINI-K -- Front Controller
 *  Unico punto de entrada para todas las peticiones HTTP.
 *  Estructura:  public/index.php
 * ============================================================
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Detecta BASE_PATH (funciona en / y en subdirectorios como /clinik_app) ──
$scriptName = $_SERVER['SCRIPT_NAME'];
$publicPos  = strrpos($scriptName, '/public/');
$basePath   = ($publicPos !== false)
    ? substr($scriptName, 0, $publicPos)        // /clinik_app
    : rtrim(dirname(dirname($scriptName)), '/'); // fallback

define('BASE_PATH', $basePath);

// ── Extrae la ruta limpia de la URL ─────────────────────────────────────────
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = (BASE_PATH !== '' && str_starts_with($requestUri, BASE_PATH))
    ? substr($requestUri, strlen(BASE_PATH))
    : $requestUri;

$path = '/' . ltrim($path, '/');
$path = rtrim($path, '/') ?: '/';

// ── Tabla de rutas ────────────────────────────────────────────────────────────
$routes = [
    'GET' => [
        '/'                         => ['App\Controllers\Web\AuthController',           'showLogin'],
        '/login'                    => ['App\Controllers\Web\AuthController',           'showLogin'],
        '/logout'                   => ['App\Controllers\Web\AuthController',           'logout'],
        '/dashboard/paciente'       => ['App\Controllers\Web\PacienteController',      'dashboard'],
        '/dashboard/doctor'         => ['App\Controllers\Web\DoctorController',        'dashboard'],
        '/dashboard/secretaria'     => ['App\Controllers\Web\SecretariaController',    'dashboard'],
        '/citas'                    => ['App\Controllers\Web\CitaController',           'index'],
        '/pacientes'                => ['App\Controllers\Web\PacienteController',      'lista'], // RUTAS FALTANTES
        '/doctores'                 => ['App\Controllers\Web\DoctorController',        'lista'], // RUTAS FALTANTES
        '/historial'               => ['App\Controllers\Web\HistorialController',        'index'],
        '/historial/atender'       => ['App\Controllers\Web\HistorialController',        'atender'],
        '/historial/editar'        => ['App\Controllers\Web\HistorialController',        'editar'],
        '/registro'                => ['App\Controllers\Web\RegistroController',         'showForm'],
        '/admin'                   => ['App\Controllers\Web\AdminController',            'index'],
        '/admin/crear-usuario'     => ['App\Controllers\Web\AdminController',            'showCrear'],
        '/admin/editar-doctor'     => ['App\Controllers\Web\AdminController',            'showEditar'],
        '/admin/editar-usuario'    => ['App\Controllers\Web\AdminController',            'showEditarStaff'],
        '/admin/citas'             => ['App\Controllers\Web\AdminController',            'citas'],
        '/admin/editar-cita'       => ['App\Controllers\Web\AdminController',            'editarCita'],
        '/api/notificaciones'       => ['App\Controllers\Api\NotificacionesApiController', 'index'],
        '/api/citas/disponibilidad' => ['App\Controllers\Api\CitasApiController',         'disponibilidad'],
        '/facturas'                 => ['App\Controllers\Web\FacturaController',           'index'],
        '/paciente/perfil'          => ['App\Controllers\Web\PacienteController',          'perfil'],
    ],
    'POST' => [
        '/login'                => ['App\Controllers\Web\AuthController',      'processLogin'],
        '/citas/crear'          => ['App\Controllers\Web\CitaController',      'crear'],
        '/historial/guardar'    => ['App\Controllers\Web\HistorialController', 'guardar'],
        '/historial/actualizar' => ['App\Controllers\Web\HistorialController', 'actualizar'],
        '/registro/procesar'    => ['App\Controllers\Web\RegistroController',  'procesar'],
        '/admin/crear-usuario'  => ['App\Controllers\Web\AdminController',     'procesarCrear'],
        '/admin/editar-doctor'  => ['App\Controllers\Web\AdminController',     'procesarEditar'],
        '/admin/editar-usuario' => ['App\Controllers\Web\AdminController',     'procesarEditarStaff'],
        '/admin/desactivar'     => ['App\Controllers\Web\AdminController',     'desactivar'],
        '/admin/reactivar'      => ['App\Controllers\Web\AdminController',     'reactivar'],
        '/api/citas/reservar'   => ['App\Controllers\Api\CitasApiController',  'reservar'],
        '/api/citas/cancelar'   => ['App\Controllers\Api\CitasApiController',  'cancelar'],
        '/api/citas/aprobar'    => ['App\Controllers\Api\CitasApiController',  'aprobar'],
        '/api/citas/rechazar'   => ['App\Controllers\Api\CitasApiController',  'rechazar'],
        '/api/citas/reagendar'  => ['App\Controllers\Api\CitasApiController',  'reagendar'],
        '/facturas/crear'       => ['App\Controllers\Web\FacturaController',   'crear'],
        '/paciente/perfil'      => ['App\Controllers\Web\PacienteController',  'actualizarPerfil'],
    ],
];

// ── Despacha la peticion ──────────────────────────────────────────────────────
try {
    $method = $_SERVER['REQUEST_METHOD'];

    if (isset($routes[$method][$path])) {
        [$controllerClass, $action] = $routes[$method][$path];

        if (class_exists($controllerClass)) {
            $controller = new $controllerClass();
            $controller->$action();
        } else {
            // El archivo del controlador no existe en src/Controllers/...
            http_response_code(404);
            include dirname(__DIR__) . '/views/errors/404.php';
        }
    } else {
        http_response_code(404);
        include dirname(__DIR__) . '/views/errors/404.php';
    }

} catch (\App\Config\DatabaseConnectionException $e) {
    http_response_code(500);
    include dirname(__DIR__) . '/views/errors/500.php';

} catch (\Throwable $e) {
    error_log('[CLINIK ERROR] ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    include dirname(__DIR__) . '/views/errors/500.php';
}