<?php

namespace App\Controllers\Web;

use App\Repositories\UsuarioRepository;

/**
 * RegistroController — Auto-registro publico de Pacientes.
 *
 * GET  /registro          → showForm()  — Muestra el formulario de registro
 * POST /registro/procesar → procesar()  — Valida, crea el paciente y el usuario
 */
class RegistroController
{
    private UsuarioRepository $repo;

    public function __construct()
    {
        $this->repo = new UsuarioRepository();
    }

    private function bp(): string
    {
        return defined('BASE_PATH') ? BASE_PATH : '';
    }

    // ── GET /registro ──────────────────────────────────────────────────
    public function showForm(): void
    {
        // Si ya tiene sesion, redirigir a su dashboard
        if (isset($_SESSION['id_usuario'])) {
            $map = [1 => '/dashboard/paciente', 2 => '/dashboard/doctor', 3 => '/dashboard/secretaria', 4 => '/admin'];
            header('Location: ' . $this->bp() . ($map[$_SESSION['id_rol']] ?? '/login'));
            exit;
        }

        $error   = $_SESSION['registro_error']   ?? null;
        $success = $_SESSION['registro_success'] ?? null;
        $old     = $_SESSION['registro_old']     ?? [];
        unset($_SESSION['registro_error'], $_SESSION['registro_success'], $_SESSION['registro_old']);

        include dirname(__DIR__, 3) . '/views/auth/registro.php';
    }

    // ── POST /registro/procesar ────────────────────────────────────────
    public function procesar(): void
    {
        $bp = $this->bp();

        $nombre  = trim($_POST['nombre']  ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $correo  = trim(strtolower($_POST['correo'] ?? ''));
        $telefono = trim($_POST['telefono'] ?? '');
        $nid     = trim($_POST['numero_identificacion'] ?? '');
        $pass    = $_POST['contrasena']         ?? '';
        $pass2   = $_POST['contrasena_confirm'] ?? '';

        // ── Guardar valores para repoblar el form si falla ─────────────
        $_SESSION['registro_old'] = compact('nombre', 'apellido', 'correo', 'telefono', 'nid');

        // ── Validaciones ───────────────────────────────────────────────
        if (empty($nombre) || empty($apellido) || empty($correo) || empty($nid) || empty($pass)) {
            $_SESSION['registro_error'] = 'Todos los campos obligatorios deben completarse.';
            header('Location: ' . $bp . '/registro');
            exit;
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['registro_error'] = 'El formato del correo no es valido.';
            header('Location: ' . $bp . '/registro');
            exit;
        }
        if (mb_strlen($pass) < 8) {
            $_SESSION['registro_error'] = 'La contrasena debe tener al menos 8 caracteres.';
            header('Location: ' . $bp . '/registro');
            exit;
        }
        if ($pass !== $pass2) {
            $_SESSION['registro_error'] = 'Las contrasenas no coinciden.';
            header('Location: ' . $bp . '/registro');
            exit;
        }
        if ($this->repo->existeCorreo($correo)) {
            $_SESSION['registro_error'] = 'Este correo ya esta registrado. Intenta iniciar sesion.';
            header('Location: ' . $bp . '/registro');
            exit;
        }
        if ($this->repo->existeIdentificacion($nid)) {
            $_SESSION['registro_error'] = 'Esta cedula ya esta registrada en el sistema.';
            header('Location: ' . $bp . '/registro');
            exit;
        }

        // ── Crear paciente + usuario ───────────────────────────────────
        try {
            $this->repo->registrarPaciente([
                'nombre'                => $nombre,
                'apellido'              => $apellido,
                'correo'                => $correo,
                'telefono'              => $telefono,
                'numero_identificacion' => $nid,
                'contrasena'            => $pass,
            ]);

            unset($_SESSION['registro_old']);
            $_SESSION['registro_success'] = '¡Cuenta creada exitosamente! Ya puedes iniciar sesion.';
            header('Location: ' . $bp . '/registro');
            exit;

        } catch (\RuntimeException $e) {
            $_SESSION['registro_error'] = $e->getMessage();
            header('Location: ' . $bp . '/registro');
            exit;
        }
    }
}
