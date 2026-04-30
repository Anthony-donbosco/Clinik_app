<?php

namespace App\Controllers\Api;

use App\Services\CitaService;
use App\Repositories\CitaRepository;

/**
 * CitasApiController — Endpoints JSON del módulo de Citas.
 * Usado por JavaScript (fetch) para consultas AJAX sin recargar la página.
 */
class CitasApiController
{
    private CitaService    $service;
    private CitaRepository $repo;

    public function __construct()
    {
        $this->service = new CitaService();
        $this->repo    = new CitaRepository();
    }

    // ──────────────────────────────────────────────────────────────────────
    //  GET /api/citas/disponibilidad?id_doctor=X&fecha=YYYY-MM-DD
    //  Retorna los bloques del día con su estado de disponibilidad.
    // ──────────────────────────────────────────────────────────────────────
    public function disponibilidad(): void
    {
        $this->jsonHeaders();

        if (!isset($_SESSION['id_usuario'])) {
            $this->json(['error' => 'No autenticado.'], 401);
            return;
        }

        $idDoctor = (int) ($_GET['id_doctor'] ?? 0);
        $fecha    = trim($_GET['fecha'] ?? '');

        if ($idDoctor <= 0 || empty($fecha)) {
            $this->json(['error' => 'Parámetros incompletos: se requiere id_doctor y fecha.'], 400);
            return;
        }

        $horarios = $this->service->getHorariosDisponibles($idDoctor, $fecha);

        // Si el service retornó un error de negocio (array con 'error')
        if (isset($horarios['error'])) {
            $this->json(['error' => $horarios['error']], 422);
            return;
        }

        $this->json(['ok' => true, 'fecha' => $fecha, 'id_doctor' => $idDoctor, 'horarios' => $horarios]);
    }

    // ──────────────────────────────────────────────────────────────────────
    //  POST /api/citas/reservar
    // ──────────────────────────────────────────────────────────────────────
    public function reservar(): void
    {
        $this->jsonHeaders();
        if (!isset($_SESSION['id_usuario'])) { $this->json(['ok' => false, 'mensaje' => 'No autenticado.'], 401); return; }

        $body       = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $idRol      = (int) $_SESSION['id_rol'];
        $idPaciente = ($idRol === 1) ? (int) $_SESSION['id_referencia'] : (int) ($body['id_paciente'] ?? 0);

        $resultado  = $this->service->validarYCrearCita([
            'id_doctor'   => $body['id_doctor']  ?? '',
            'id_paciente' => $idPaciente,
            'fecha'       => $body['fecha']       ?? '',
            'hora'        => $body['hora']        ?? '',
        ]);
        $this->json($resultado, $resultado['ok'] ? 201 : 422);
    }

    // ──────────────────────────────────────────────────────────────────────
    //  POST /api/citas/cancelar   { id_cita: N }
    // ──────────────────────────────────────────────────────────────────────
    public function cancelar(): void
    {
        $this->jsonHeaders();
        if (!isset($_SESSION['id_usuario'])) { $this->json(['ok' => false, 'mensaje' => 'No autenticado.'], 401); return; }

        $body   = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $idCita = (int) ($body['id_cita'] ?? 0);

        if ($idCita <= 0) { $this->json(['ok' => false, 'mensaje' => 'ID de cita inválido.'], 400); return; }

        $resultado = $this->service->cancelarCita($idCita);
        $this->json($resultado, $resultado['ok'] ? 200 : 422);
    }

    // ──────────────────────────────────────────────────────────────────────
    //  Helpers privados
    // ──────────────────────────────────────────────────────────────────────

    private function jsonHeaders(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
    }

    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
