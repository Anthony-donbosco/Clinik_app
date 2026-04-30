<?php

namespace App\Controllers\Web;

use App\Repositories\UsuarioRepository;

/**
 * AdminController — Panel de administracion del sistema.
 * Solo accesible para id_rol = 4 (Admin).
 *
 * GET  /admin                    → index()         — Dashboard Admin con lista de usuarios
 * GET  /admin/crear-usuario      → showCrear()     — Formulario crear usuario (doctor/secretaria/admin)
 * POST /admin/crear-usuario      → procesarCrear() — Guarda el nuevo usuario
 * POST /admin/desactivar         → desactivar()    — Desactiva un usuario (soft delete)
 * POST /admin/reactivar          → reactivar()     — Reactiva un usuario
 */
class AdminController extends BaseController
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

    // ── GET /admin ─────────────────────────────────────────────────────
    public function index(): void
    {
        $this->requireRole(4); // Solo Admin

        $usuarios    = $this->repo->getAllUsuarios();
        $flashOk     = $_SESSION['flash_ok']    ?? null;
        $flashError  = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_ok'], $_SESSION['flash_error']);

        $bp = $this->bp();
        $this->render('admin/dashboard', compact('usuarios', 'flashOk', 'flashError', 'bp'));
    }

    // ── GET /admin/crear-usuario ───────────────────────────────────────
    public function showCrear(): void
    {
        $this->requireRole(4);

        $doctoresLibres = $this->repo->getDoctoresLibres();
        $flashError     = $_SESSION['flash_error'] ?? null;
        $old            = $_SESSION['admin_old']   ?? [];
        unset($_SESSION['flash_error'], $_SESSION['admin_old']);

        $bp = $this->bp();
        $this->render('admin/crear_usuario', compact('doctoresLibres', 'flashError', 'old', 'bp'));
    }

    // ── POST /admin/crear-usuario ──────────────────────────────────────
    public function procesarCrear(): void
    {
        $this->requireRole(4);
        $bp = $this->bp();

        $tipoRol = (int) ($_POST['tipo_rol'] ?? 0);
        $nombre  = trim($_POST['nombre']  ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $correo  = trim(strtolower($_POST['correo'] ?? ''));
        $nid     = trim($_POST['numero_identificacion'] ?? '');
        $pass    = $_POST['contrasena'] ?? '';

        $_SESSION['admin_old'] = compact('nombre', 'apellido', 'correo', 'nid', 'tipoRol');

        // Validaciones basicas
        if (empty($nombre) || empty($apellido) || empty($correo) || empty($nid) || empty($pass)) {
            $_SESSION['flash_error'] = 'Todos los campos son obligatorios.';
            header('Location: ' . $bp . '/admin/crear-usuario');
            exit;
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'El correo no tiene un formato valido.';
            header('Location: ' . $bp . '/admin/crear-usuario');
            exit;
        }
        if (mb_strlen($pass) < 8) {
            $_SESSION['flash_error'] = 'La contrasena debe tener al menos 8 caracteres.';
            header('Location: ' . $bp . '/admin/crear-usuario');
            exit;
        }
        if ($this->repo->existeCorreo($correo)) {
            $_SESSION['flash_error'] = 'El correo ya esta registrado en el sistema.';
            header('Location: ' . $bp . '/admin/crear-usuario');
            exit;
        }
        if ($this->repo->existeIdentificacion($nid)) {
            $_SESSION['flash_error'] = 'La cedula/ID ya esta registrada.';
            header('Location: ' . $bp . '/admin/crear-usuario');
            exit;
        }

        $data = compact('nombre', 'apellido', 'correo', 'nid') + ['contrasena' => $pass];

        try {
            if ($tipoRol === 2) {
                // Doctor — necesita id_referencia a tabla doctor
                $idRef = (int) ($_POST['id_referencia'] ?? 0);
                if ($idRef <= 0) {
                    $_SESSION['flash_error'] = 'Debes seleccionar un Doctor del catalogo.';
                    header('Location: ' . $bp . '/admin/crear-usuario');
                    exit;
                }
                $data['id_referencia'] = $idRef;
                $this->repo->crearUsuarioDoctor($data);
                $rolNombre = 'Doctor';

            } elseif (in_array($tipoRol, [3, 4], true)) {
                // Secretaria (3) o Admin (4)
                $this->repo->crearUsuarioStaff($data, $tipoRol);
                $rolNombre = $tipoRol === 3 ? 'Secretaria' : 'Admin';

            } else {
                $_SESSION['flash_error'] = 'Tipo de rol no valido.';
                header('Location: ' . $bp . '/admin/crear-usuario');
                exit;
            }

            unset($_SESSION['admin_old']);
            $_SESSION['flash_ok'] = "Usuario {$rolNombre} '{$nombre} {$apellido}' creado correctamente.";
            header('Location: ' . $bp . '/admin');
            exit;

        } catch (\RuntimeException $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            header('Location: ' . $bp . '/admin/crear-usuario');
            exit;
        }
    }

    // ── POST /admin/desactivar ─────────────────────────────────────────
    public function desactivar(): void
    {
        $this->requireRole(4);
        $bp = $this->bp();

        $idUsuario = (int) ($_POST['id_usuario'] ?? 0);

        // No puede desactivarse a si mismo
        if ($idUsuario === (int) $_SESSION['id_usuario']) {
            $_SESSION['flash_error'] = 'No puedes desactivar tu propia cuenta.';
            header('Location: ' . $bp . '/admin');
            exit;
        }

        $ok = $this->repo->desactivarUsuario($idUsuario);
        $_SESSION[$ok ? 'flash_ok' : 'flash_error'] = $ok
            ? 'Usuario desactivado correctamente.'
            : 'No se pudo desactivar el usuario.';

        header('Location: ' . $bp . '/admin');
        exit;
    }

    // ── POST /admin/reactivar ──────────────────────────────────────────
    public function reactivar(): void
    {
        $this->requireRole(4);
        $bp = $this->bp();

        $idUsuario = (int) ($_POST['id_usuario'] ?? 0);
        $ok = $this->repo->reactivarUsuario($idUsuario);
        $_SESSION[$ok ? 'flash_ok' : 'flash_error'] = $ok
            ? 'Usuario reactivado correctamente.'
            : 'No se pudo reactivar el usuario.';

        header('Location: ' . $bp . '/admin');
        exit;
    }
}
