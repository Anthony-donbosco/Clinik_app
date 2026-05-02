<?php

namespace App\Controllers\Web;

use App\Services\CitaService;
use App\Repositories\CitaRepository;

/**
 * CitaController — Controlador Web del módulo de Citas.
 * Sirve vistas HTML para los tres roles, con datos según permisos.
 */
class CitaController extends BaseController
{
    private CitaService    $service;
    private CitaRepository $repo;

    public function __construct()
    {
        $this->service = new CitaService();
        $this->repo    = new CitaRepository();
    }

    /** GET /citas — Vista principal (comportamiento distinto por rol) */
    public function index(): void
    {
        $this->requireAuth();

        $idRol       = (int) $_SESSION['id_rol'];
        $idRef       = (int) ($_SESSION['id_referencia'] ?? 0);

        // Admin no debe usar la vista de citas de pacientes; tiene su propia vista de supervisión
        if ($idRol === 4) {
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/admin/citas');
            exit;
        }

        $doctores    = $this->repo->getDoctoresParaSelect();
        $pacientes   = [];
        $citas       = [];
        $flashOk     = $_SESSION['flash_ok']    ?? null;
        $flashError  = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_ok'], $_SESSION['flash_error']);

        switch ($idRol) {
            case 3: // Secretaria — ve todo y puede crear citas para cualquiera
                $citas    = $this->repo->getAllCitas();
                $pacientes = $this->repo->getPacientesParaSelect();
                break;
            case 2: // Doctor — ve solo sus propias citas
                $citas = $this->repo->getCitasByDoctor($idRef);
                break;
            case 1: // Paciente — ve sus citas y puede solicitar nuevas
                $citas = $this->repo->getCitasByPaciente($idRef);
                break;
        }

        $this->render('citas/index', compact('citas', 'doctores', 'pacientes', 'flashOk', 'flashError'));
    }

    /** POST /citas/crear — Procesa el formulario de nueva cita */
    public function crear(): void
    {
        $this->requireAuth();

        $idRol = (int) $_SESSION['id_rol'];

        // El Doctor no puede crear citas (solo ver)
        if ($idRol === 2) {
            $_SESSION['flash_error'] = 'Los doctores no pueden crear citas directamente.';
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/citas');
            exit;
        }

        // Para Paciente, id_paciente viene de su propia sesión
        $idPaciente = ($idRol === 1)
            ? (int) $_SESSION['id_referencia']
            : (int) ($_POST['id_paciente'] ?? 0);

        $resultado = $this->service->validarYCrearCita([
            'id_doctor'   => $_POST['id_doctor']  ?? '',
            'id_paciente' => $idPaciente,
            'fecha'       => $_POST['fecha']       ?? '',
            'hora'        => $_POST['hora']        ?? '',
        ]);

        if ($resultado['ok']) {
            $_SESSION['flash_ok'] = $resultado['mensaje'];
        } else {
            $_SESSION['flash_error'] = $resultado['mensaje'];
        }

        header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/citas');
        exit;
    }
}

