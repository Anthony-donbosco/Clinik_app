<?php

namespace App\Controllers\Web;

use App\Repositories\DashboardRepository;
use App\Repositories\UsuarioRepository;

/**
 * PacienteController — Controlador Web del Paciente.
 *
 * GET  /dashboard/paciente    → dashboard()          — Panel principal
 * GET  /paciente/perfil       → perfil()             — Ver y editar datos de contacto
 * POST /paciente/perfil       → actualizarPerfil()   — Guarda cambios de teléfono/correo
 */
class PacienteController extends BaseController
{
    private DashboardRepository $dashRepo;
    private UsuarioRepository   $usuRepo;

    public function __construct()
    {
        $this->dashRepo = new DashboardRepository();
        $this->usuRepo  = new UsuarioRepository();
    }

    /** GET /dashboard/paciente */
    public function dashboard(): void
    {
        $this->requireRole(1);
        $idPaciente = (int) $_SESSION['id_referencia'];
        $stats      = $this->dashRepo->getStatsPaciente($idPaciente);
        $citas      = $this->dashRepo->getCitasPaciente($idPaciente);
        $historial  = $this->dashRepo->getHistorialPaciente($idPaciente);
        $this->render('paciente/dashboard', compact('stats', 'citas', 'historial'));
    }

    /** GET /paciente/perfil */
    public function perfil(): void
    {
        $this->requireRole(1);
        $bp         = defined('BASE_PATH') ? BASE_PATH : '';
        $idPaciente = (int) $_SESSION['id_referencia'];
        $perfil     = $this->usuRepo->getPerfilPaciente($idPaciente);
        $flashOk    = $_SESSION['flash_ok']    ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_ok'], $_SESSION['flash_error']);

        if (!$perfil) {
            http_response_code(404);
            include dirname(__DIR__, 3) . '/views/errors/404.php';
            return;
        }

        $this->render('paciente/perfil', compact('perfil', 'bp', 'flashOk', 'flashError'));
    }

    /** POST /paciente/perfil */
    public function actualizarPerfil(): void
    {
        $this->requireRole(1);
        $bp         = defined('BASE_PATH') ? BASE_PATH : '';
        $idPaciente = (int) $_SESSION['id_referencia'];
        $idUsuario  = (int) ($_SESSION['id_usuario'] ?? 0);

        $telefono = trim($_POST['telefono'] ?? '');
        $correo   = trim(strtolower($_POST['correo'] ?? ''));

        if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'El correo no es válido.';
            header('Location: ' . $bp . '/paciente/perfil');
            exit;
        }

        $resultado = $this->usuRepo->actualizarPerfilPaciente($idPaciente, $idUsuario, $telefono, $correo);

        if ($resultado['ok']) {
            // Actualizar correo en sesión
            $_SESSION['correo']   = $correo;
            $_SESSION['flash_ok'] = $resultado['mensaje'];
        } else {
            $_SESSION['flash_error'] = $resultado['mensaje'];
        }

        header('Location: ' . $bp . '/paciente/perfil');
        exit;
    }

    /** GET /pacientes — Lista para Secretaria/Admin */
    public function lista(): void
    {
        $this->requireRole([3, 4]);
        $pacientes = $this->dashRepo->getAllPacientesActivos();
        $bp        = defined('BASE_PATH') ? BASE_PATH : '';
        $this->render('secretaria/pacientes', compact('pacientes', 'bp'));
    }
}