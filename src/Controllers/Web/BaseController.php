<?php

namespace App\Controllers\Web;

/**
 * BaseController — Controlador base con utilidades compartidas.
 * Todos los controladores Web que requieran autenticación deben
 * extender esta clase y llamar a $this->requireAuth() al inicio.
 */
abstract class BaseController
{
    /**
     * Verifica que el usuario tenga una sesión activa.
     * Si no, lo redirige al login con un mensaje claro.
     */
    protected function requireAuth(): void
    {
        if (!isset($_SESSION['id_usuario'])) {
            $_SESSION['login_error'] = 'Debes iniciar sesión para acceder a esa página.';
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login');
            exit;
        }
    }

    /**
     * Verifica que el usuario tenga el rol requerido.
     * @param int|int[] $rolesPermitidos ID(s) de rol(es) con acceso.
     */
    protected function requireRole(int|array $rolesPermitidos): void
    {
        $this->requireAuth();
        $rolesPermitidos = (array) $rolesPermitidos;

        if (!in_array($_SESSION['id_rol'], $rolesPermitidos, true)) {
            http_response_code(403);
            include dirname(__DIR__, 3) . '/views/errors/403.php';
            exit;
        }
    }

    /**
     * Renderiza una vista PHP pasándole variables de forma segura.
     * @param string $viewPath Ruta relativa desde /views/ (ej. 'auth/login')
     * @param array  $data     Variables disponibles dentro de la vista
     */
    protected function render(string $viewPath, array $data = []): void
    {
        extract($data, EXTR_SKIP); // No sobreescribe variables existentes
        $fullPath = dirname(__DIR__, 3) . '/views/' . $viewPath . '.php';

        if (!file_exists($fullPath)) {
            throw new \RuntimeException("Vista no encontrada: {$fullPath}");
        }

        include $fullPath;
    }
}

