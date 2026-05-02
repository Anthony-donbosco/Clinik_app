<?php

namespace App\Controllers\Web;

use App\Services\HistorialService;

/**
 * HistorialController — Controlador Web del modulo de Historial Medico.
 *
 * GET  /historial           → index()   — Lista historiales (Doctor ve los suyos, Paciente los suyos)
 * GET  /historial/atender   → atender() — Formulario para registrar historial de una cita (solo Doctor)
 * POST /historial/guardar   → guardar() — Persiste el historial y marca la cita como Atendida (solo Doctor)
 */
class HistorialController extends BaseController
{
    private HistorialService $service;

    public function __construct()
    {
        $this->service = new HistorialService();
    }

    // ──────────────────────────────────────────────────────────────────────
    //  GET /historial
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Muestra el historial medico segun el rol del usuario autenticado.
     * Doctor  → todos los historiales que el ha registrado.
     * Paciente → sus propios diagnosticos recibidos.
     */
    public function index(): void
    {
        $this->requireAuth();

        $rol = (int) ($_SESSION['id_rol'] ?? 0);
        $bp  = defined('BASE_PATH') ? BASE_PATH : '';

        if ($rol === 2) {
            // ── Doctor ──────────────────────────────────────────────────
            $idDoctor   = (int) $_SESSION['id_referencia'];
            $historiales = $this->service->getHistorialDoctor($idDoctor);
            $this->render('historial/doctor_historial', compact('historiales', 'bp'));

        } elseif ($rol === 1) {
            // ── Paciente ─────────────────────────────────────────────────
            $idPaciente  = (int) $_SESSION['id_referencia'];
            $historiales = $this->service->getHistorialPaciente($idPaciente);
            $this->render('historial/paciente_historial', compact('historiales', 'bp'));

        } else {
            // Secretaria u otro rol sin acceso especifico
            http_response_code(403);
            include dirname(__DIR__, 3) . '/views/errors/403.php';
        }
    }

    // ──────────────────────────────────────────────────────────────────────
    //  GET /historial/atender?cita=ID
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Muestra el formulario para que el Doctor registre el historial de una cita.
     * Solo accesible por doctores. Valida que la cita sea suya y este en estado activo.
     */
    public function atender(): void
    {
        $this->requireRole(2); // Solo Doctor

        $idCita   = (int) ($_GET['cita'] ?? 0);
        $idDoctor = (int) ($_SESSION['id_referencia'] ?? 0);
        $bp       = defined('BASE_PATH') ? BASE_PATH : '';

        if ($idCita <= 0) {
            $_SESSION['flash_error'] = 'Cita no especificada.';
            header('Location: ' . $bp . '/historial');
            exit;
        }

        $resultado = $this->service->getCitaParaAtender($idCita, $idDoctor);

        if (!$resultado['ok']) {
            $_SESSION['flash_error'] = $resultado['mensaje'];
            header('Location: ' . $bp . '/historial');
            exit;
        }

        $cita = $resultado['cita'];

        // Cargar historial previo del paciente (<<include>> Consultar historial)
        $idPaciente       = (int) $cita['id_paciente'];
        $historialPrevio  = $this->service->getHistorialPaciente($idPaciente);

        $this->render('historial/atender_cita', compact('cita', 'bp', 'historialPrevio'));
    }

    // ──────────────────────────────────────────────────────────────────────
    //  POST /historial/guardar
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Procesa el formulario y persiste el historial medico.
     * Solo accesible por doctores. Redirige con mensaje flash al terminar.
     */
    public function guardar(): void
    {
        $this->requireRole(2); // Solo Doctor

        $bp       = defined('BASE_PATH') ? BASE_PATH : '';
        $idDoctor = (int) ($_SESSION['id_referencia'] ?? 0);

        $data = [
            'id_cita'     => (int) ($_POST['id_cita']     ?? 0),
            'id_doctor'   => $idDoctor,
            'diagnostico' => $_POST['diagnostico'] ?? '',
            'tratamiento' => $_POST['tratamiento'] ?? '',
            'notas'       => $_POST['notas']       ?? '',
        ];

        $resultado = $this->service->guardarHistorial($data);

        if ($resultado['ok']) {
            $_SESSION['flash_success'] = $resultado['mensaje'];
            header('Location: ' . $bp . '/historial');
        } else {
            $_SESSION['flash_error'] = $resultado['mensaje'];
            header('Location: ' . $bp . '/historial/atender?cita=' . $data['id_cita']);
        }
        exit;
    }
}
