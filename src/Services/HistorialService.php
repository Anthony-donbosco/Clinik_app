<?php

namespace App\Services;

use App\Repositories\HistorialRepository;
use App\Repositories\CitaRepository;

/**
 * HistorialService — Logica de negocio del modulo de Historial Medico.
 * Valida estado de cita, unicidad del historial y bloqueo de edicion a 24h.
 */
class HistorialService
{
    private HistorialRepository $historialRepo;
    private CitaRepository      $citaRepo;

    /** Tiempo maximo en segundos para editar/registrar un historial tras guardarlo (24 horas). */
    private const BLOQUEO_EDICION_HORAS = 24;

    public function __construct()
    {
        $this->historialRepo = new HistorialRepository();
        $this->citaRepo      = new CitaRepository();
    }

    // ══════════════════════════════════════════════════════════════════════
    //  CONSULTAS
    // ══════════════════════════════════════════════════════════════════════

    /** Historial completo de un paciente para mostrarlo en su dashboard. */
    public function getHistorialPaciente(int $idPaciente): array
    {
        return $this->historialRepo->getHistorialByPaciente($idPaciente);
    }

    /** Historiales registrados por un doctor. */
    public function getHistorialDoctor(int $idDoctor): array
    {
        return $this->historialRepo->getHistorialByDoctor($idDoctor);
    }

    /**
     * Obtiene la cita para el formulario "Atender".
     * Solo devuelve si la cita pertenece al doctor solicitante y esta en estado activo.
     *
     * @return array ['ok' => bool, 'cita' => array|null, 'mensaje' => string]
     */
    public function getCitaParaAtender(int $idCita, int $idDoctor): array
    {
        $cita = $this->citaRepo->getCitaById($idCita);

        if (!$cita) {
            return ['ok' => false, 'cita' => null, 'mensaje' => 'La cita no existe.'];
        }

        // Verificar que la cita pertenezca al doctor autenticado
        if ((int) $cita['id_doctor'] !== $idDoctor) {
            return ['ok' => false, 'cita' => null, 'mensaje' => 'No tienes permiso para atender esta cita.'];
        }

        if (in_array((int) $cita['id_estado'], [4, 5], true)) {
            $msg = (int) $cita['id_estado'] === 4
                ? 'Esta cita ya fue atendida.'
                : 'Esta cita fue cancelada.';
            return ['ok' => false, 'cita' => null, 'mensaje' => $msg];
        }

        if ($this->historialRepo->existeHistorialParaCita($idCita)) {
            return ['ok' => false, 'cita' => null, 'mensaje' => 'Esta cita ya tiene un historial registrado.'];
        }

        return ['ok' => true, 'cita' => $cita, 'mensaje' => ''];
    }

    // ══════════════════════════════════════════════════════════════════════
    //  ESCRITURA
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Valida y guarda el historial medico de una cita.
     * Cambia el estado de la cita a Atendida (4) de forma atomica.
     *
     * @param  array $data  ['id_cita', 'id_doctor', 'diagnostico', 'tratamiento', 'notas']
     * @return array ['ok' => bool, 'mensaje' => string, 'id_historial' => int|null]
     */
    public function guardarHistorial(array $data): array
    {
        // ── 1. Campos obligatorios ─────────────────────────────────────
        $idCita      = (int) ($data['id_cita']   ?? 0);
        $idDoctor    = (int) ($data['id_doctor']  ?? 0);
        $diagnostico = trim($data['diagnostico']  ?? '');
        $tratamiento = trim($data['tratamiento']  ?? '');
        $notas       = trim($data['notas']        ?? '');

        if ($idCita <= 0 || $idDoctor <= 0) {
            return ['ok' => false, 'mensaje' => 'Datos de sesion invalidos.', 'id_historial' => null];
        }
        if (mb_strlen($diagnostico) < 10) {
            return ['ok' => false, 'mensaje' => 'El diagnostico debe tener al menos 10 caracteres.', 'id_historial' => null];
        }
        if (mb_strlen($tratamiento) < 5) {
            return ['ok' => false, 'mensaje' => 'El tratamiento debe tener al menos 5 caracteres.', 'id_historial' => null];
        }

        // ── 2. Verificar que la cita exista y pertenezca al doctor ─────
        $citaRaw = $this->getCitaDirecta($idCita);
        if (!$citaRaw) {
            return ['ok' => false, 'mensaje' => 'La cita no existe.', 'id_historial' => null];
        }
        if ((int) $citaRaw['id_doctor'] !== $idDoctor) {
            return ['ok' => false, 'mensaje' => 'No tienes permiso para registrar este historial.', 'id_historial' => null];
        }

        // ── 3. Verificar estado de la cita ─────────────────────────────
        $estadoId = (int) $citaRaw['id_estado'];
        if ($estadoId === 4) {
            return ['ok' => false, 'mensaje' => 'Esta cita ya fue marcada como atendida.', 'id_historial' => null];
        }
        if ($estadoId === 5) {
            return ['ok' => false, 'mensaje' => 'No se puede registrar historial de una cita cancelada.', 'id_historial' => null];
        }

        // ── 4. Verificar que no haya historial duplicado ───────────────
        if ($this->historialRepo->existeHistorialParaCita($idCita)) {
            return ['ok' => false, 'mensaje' => 'Esta cita ya tiene un historial medico registrado.', 'id_historial' => null];
        }

        // ── 5. Insertar historial y marcar cita como atendida ──────────
        $idHistorial = $this->historialRepo->crearHistorial($idCita, $diagnostico, $tratamiento, $notas);
        $this->historialRepo->marcarCitaAtendida($idCita);

        return [
            'ok'          => true,
            'mensaje'     => 'Historial medico registrado correctamente. La cita fue marcada como Atendida.',
            'id_historial' => $idHistorial,
        ];
    }

    /**
     * Verifica si un historial ya no puede editarse (mas de 24h desde su creacion).
     */
    public function estaBloqueoEdicion(array $historial): bool
    {
        $fechaRegistro = strtotime($historial['fecha_registro'] ?? '');
        if (!$fechaRegistro) return true;
        return (time() - $fechaRegistro) > (self::BLOQUEO_EDICION_HORAS * 3600);
    }

    // ══════════════════════════════════════════════════════════════════════
    //  HELPERS PRIVADOS
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Wrapper interno para obtener una cita con todos sus campos via CitaRepository.
     * getCitaById devuelve c.* (que incluye id_doctor, id_paciente, id_estado) + joins.
     */
    private function getCitaDirecta(int $idCita): ?array
    {
        return $this->citaRepo->getCitaById($idCita);
    }
}
