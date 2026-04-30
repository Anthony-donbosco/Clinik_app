<?php

namespace App\Controllers\Web;

use App\Repositories\AuthRepository;

/**
 * AuthController - Controlador Web de Autenticacion.
 * Gestiona el flujo de login/logout y redirige segun el rol del usuario.
 */
class AuthController
{
    private AuthRepository $authRepo;

    public function __construct()
    {
        $this->authRepo = new AuthRepository();
    }

    private function bp(): string
    {
        return defined('BASE_PATH') ? BASE_PATH : '';
    }

    // GET /login
    public function showLogin(): void
    {
        if (isset($_SESSION['id_usuario'])) {
            $this->redirectByRole($_SESSION['id_rol']);
        }

        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);

        include dirname(__DIR__, 3) . '/views/auth/login.php';
    }

    // POST /login
    public function processLogin(): void
    {
        $credencial = trim($_POST['credencial'] ?? '');
        $contrasena = $_POST['contrasena'] ?? '';

        if (empty($credencial) || empty($contrasena)) {
            $_SESSION['login_error'] = 'Por favor, completa todos los campos.';
            header('Location: ' . $this->bp() . '/login');
            exit;
        }

        $user = $this->authRepo->findActiveUserByCredential($credencial);

        if ($user === null || !password_verify($contrasena, $user['contrasena_hash'])) {
            $_SESSION['login_error'] = 'Credenciales incorrectas o cuenta inactiva.';
            header('Location: ' . $this->bp() . '/login');
            exit;
        }

        session_regenerate_id(true);
        $_SESSION['id_usuario']    = $user['id_usuario'];
        $_SESSION['nombre']        = $user['nombre'] . ' ' . $user['apellido'];
        $_SESSION['correo']        = $user['correo'];
        $_SESSION['id_rol']        = (int) $user['id_rol'];
        $_SESSION['nombre_rol']    = $user['nombre_rol'];
        $_SESSION['id_referencia'] = $user['id_referencia'];

        $this->redirectByRole((int) $user['id_rol']);
    }

    // GET /logout
    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        header('Location: ' . $this->bp() . '/login');
        exit;
    }

    private function redirectByRole(int $idRol): void
    {
        $bp = $this->bp();
        $map = [
            1 => $bp . '/dashboard/paciente',
            2 => $bp . '/dashboard/doctor',
            3 => $bp . '/dashboard/secretaria',
            4 => $bp . '/admin',
        ];
        header('Location: ' . ($map[$idRol] ?? $bp . '/login'));
        exit;
    }
}