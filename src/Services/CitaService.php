<?php

namespace App\Services;

use App\Repositories\CitaRepository;

/**
 * CitaService — Lógica de negocio del módulo de Citas.
 * Genera bloques de 30 min, valida disponibilidad y orquesta creación/cancelación.
 */
class CitaService
{
    private CitaRepository $citaRepo;

    private const HORA_INICIO  = '08:00';
    private const HORA_FIN     = '15:30';
    private const DURACION_MIN = 30;

    public function __construct()
    {
        $this->citaRepo = new CitaRepository();
    }

    /** Genera los 16 bloques de 30 min: ['08:00', '08:30', ..., '15:30'] */
    public function generarBloques(): array
    {
        $bloques = [];
        $inicio  = strtotime(self::HORA_INICIO);
        $fin     = strtotime(self::HORA_FIN);
        while ($inicio <= $fin) {
            $bloques[] = date('H:i', $inicio);
            $inicio   += self::DURACION_MIN * 60;
        }
        return $bloques;
    }

    /**
     * Horarios disponibles de un doctor en una fecha.
     * Filtra pasados si es hoy, y fines de semana.
     */
    public function getHorariosDisponibles(int $idDoctor, string $fecha): array
    {
        if ($idDoctor <= 0) return ['error' => 'Doctor inválido.'];
        if ($fecha < date('Y-m-d')) return ['error' => 'No se pueden crear citas en fechas pasadas.'];

        $diaSemana = (int) date('N', strtotime($fecha));
        if ($diaSemana >= 6) return ['error' => 'La clínica no atiende los fines de semana.'];

        $ocupadas  = array_map(fn($h) => substr($h, 0, 5), $this->citaRepo->getHorasOcupadas($idDoctor, $fecha));
        $esHoy     = ($fecha === date('Y-m-d'));
        $resultado = [];

        foreach ($this->generarBloques() as $bloque) {
            $disponible = !in_array($bloque, $ocupadas, true);
            if ($esHoy && $disponible) {
                $disponible = (strtotime($fecha . ' ' . $bloque) > (time() + 15 * 60));
            }
            $resultado[] = ['hora' => $bloque, 'disponible' => $disponible];
        }
        return $resultado;
    }

    /**
     * Valida reglas de negocio y crea la cita.
     * Doble capa: PHP (5 validaciones) + UNIQUE MySQL capturado como excepción.
     *
     * @return array ['ok' => bool, 'mensaje' => string, 'id_cita' => int|null]
     */
    public function validarYCrearCita(array $data): array
    {
        foreach (['id_doctor', 'id_paciente', 'fecha', 'hora'] as $f) {
            if (empty($data[$f])) return ['ok' => false, 'mensaje' => "Campo '{$f}' obligatorio.", 'id_cita' => null];
        }

        $idDoctor   = (int) $data['id_doctor'];
        $idPaciente = (int) $data['id_paciente'];
        $fecha      = trim($data['fecha']);
        $hora       = trim($data['hora']) . ':00';
        $horaHHMM   = substr($hora, 0, 5);

        if ($fecha < date('Y-m-d'))
            return ['ok' => false, 'mensaje' => 'No se pueden crear citas en fechas pasadas.', 'id_cita' => null];

        if ((int) date('N', strtotime($fecha)) >= 6)
            return ['ok' => false, 'mensaje' => 'La clínica no atiende los fines de semana.', 'id_cita' => null];

        if (!in_array($horaHHMM, $this->generarBloques(), true))
            return ['ok' => false, 'mensaje' => 'El horario seleccionado no es válido.', 'id_cita' => null];

        if ($this->citaRepo->existeConflicto($idDoctor, $fecha, $hora))
            return ['ok' => false, 'mensaje' => 'El horario ' . $horaHHMM . ' ya está reservado. Elige otro.', 'id_cita' => null];

        try {
            $idCita = $this->citaRepo->crearCita($idDoctor, $idPaciente, $fecha, $hora);
            return ['ok' => true, 'mensaje' => 'Cita reservada para el ' . date('d/m/Y', strtotime($fecha)) . ' a las ' . $horaHHMM . '.', 'id_cita' => $idCita];
        } catch (\RuntimeException $e) {
            return ['ok' => false, 'mensaje' => $e->getMessage(), 'id_cita' => null];
        }
    }

    /**
     * Cancela una cita con validaciones de negocio.
     * Casos de borde: ya atendida, ya cancelada, menos de 2h de anticipación.
     *
     * @return array ['ok' => bool, 'mensaje' => string]
     */
    public function cancelarCita(int $idCita): array
    {
        $cita = $this->citaRepo->getCitaById($idCita);
        if (!$cita) return ['ok' => false, 'mensaje' => 'La cita no existe.'];
        if ((int) $cita['id_estado'] === 4) return ['ok' => false, 'mensaje' => 'No se puede cancelar una cita ya atendida.'];
        if ((int) $cita['id_estado'] === 5) return ['ok' => false, 'mensaje' => 'Esta cita ya estaba cancelada.'];

        if (strtotime($cita['fecha'] . ' ' . $cita['hora']) <= (time() + 2 * 3600))
            return ['ok' => false, 'mensaje' => 'No se puede cancelar con menos de 2 horas de anticipación. Contacta a la secretaria.'];

        $ok = $this->citaRepo->cancelarCita($idCita);
        return ['ok' => $ok, 'mensaje' => $ok ? 'Cita cancelada correctamente.' : 'No se pudo cancelar la cita.'];
    }
}
