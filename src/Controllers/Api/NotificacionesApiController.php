<?php

namespace App\Controllers\Api;

use App\Config\Database;
use PDO;

/**
 * NotificacionesApiController
 * Endpoint que notificaciones.js consulta cada 10 segundos via polling.
 *
 * Genera notificaciones contextuales reales segun el rol:
 *   Rol 1 (Paciente)   → citas proximas en los proximos 2 dias
 *   Rol 2 (Doctor)     → citas de hoy pendientes de atender
 *   Rol 3 (Secretaria) → citas pendientes de confirmacion global
 */
class NotificacionesApiController
{
    private PDO $db;

    public function index(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['id_usuario'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado.']);
            exit;
        }

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        header('X-Content-Type-Options: nosniff');

        $this->db = Database::getInstance()->getConnection();

        $rol          = (int) ($_SESSION['id_rol']        ?? 0);
        $idReferencia = (int) ($_SESSION['id_referencia'] ?? 0);

        $items = match ($rol) {
            1 => $this->notifPaciente($idReferencia),
            2 => $this->notifDoctor($idReferencia),
            3 => $this->notifSecretaria(),
            default => [],
        };

        echo json_encode([
            'ok'    => true,
            'count' => count($items),
            'items' => $items,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ── Paciente: citas en los proximos 2 dias ─────────────────────────
    private function notifPaciente(int $idPaciente): array
    {
        $sql = "SELECT c.id_cita, c.fecha, c.hora,
                       CONCAT(d.primer_nombre, ' ', d.primer_apellido) AS nombre_doctor,
                       d.especialidad,
                       e.nombre AS estado, c.id_estado
                FROM   cita c
                INNER JOIN doctor d ON c.id_doctor = d.id_doctor
                INNER JOIN estado e ON c.id_estado = e.id_estado
                WHERE  c.id_paciente = :id
                AND    c.fecha BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 2 DAY)
                AND    c.id_estado NOT IN (5)
                ORDER  BY c.fecha ASC, c.hora ASC
                LIMIT  5";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idPaciente]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $items = [];
        foreach ($rows as $r) {
            $esHoy   = ($r['fecha'] === date('Y-m-d'));
            $fechaStr = $esHoy ? 'hoy' : 'manana';
            $horaStr  = substr($r['hora'], 0, 5);
            $estado   = (int) $r['id_estado'];

            if ($estado === 4) {
                $items[] = [
                    'tipo'    => 'success',
                    'icono'   => '📋',
                    'titulo'  => 'Consulta completada',
                    'mensaje' => 'Tu consulta del ' . date('d/m/Y', strtotime($r['fecha'])) . ' con ' . $r['nombre_doctor'] . ' ya fue registrada. Revisa tu historial medico.',
                    'tiempo'  => $this->tiempoRelativo($r['fecha'] . ' ' . $r['hora']),
                    'accion'  => ['label' => 'Ver historial', 'href' => '/historial'],
                ];
            } else {
                $items[] = [
                    'tipo'    => 'info',
                    'icono'   => '📅',
                    'titulo'  => 'Cita ' . $fechaStr,
                    'mensaje' => 'Tienes cita con ' . $r['nombre_doctor'] . ' (' . ($r['especialidad'] ?? 'General') . ') a las ' . $horaStr . '.',
                    'tiempo'  => $horaStr,
                    'accion'  => ['label' => 'Ver mis citas', 'href' => '/citas'],
                ];
            }
        }
        return $items;
    }

    // ── Doctor: citas de hoy pendientes de atender ─────────────────────
    private function notifDoctor(int $idDoctor): array
    {
        $sql = "SELECT c.id_cita, c.hora,
                       CONCAT(p.primer_nombre, ' ', p.primer_apellido) AS nombre_paciente
                FROM   cita c
                INNER JOIN paciente p ON c.id_paciente = p.id_paciente
                WHERE  c.id_doctor  = :id
                AND    c.fecha      = CURDATE()
                AND    c.id_estado  NOT IN (4, 5)
                ORDER  BY c.hora ASC
                LIMIT  5";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idDoctor]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) return [];

        $items = [];
        foreach ($rows as $r) {
            $horaStr = substr($r['hora'], 0, 5);
            $ahora   = time();
            $citaTs  = strtotime(date('Y-m-d') . ' ' . $r['hora']);
            $diff    = $citaTs - $ahora;

            if ($diff <= 0) {
                $tipo   = 'warning';
                $titulo = 'Cita pendiente de atender';
                $msg    = 'La cita de las ' . $horaStr . ' con ' . $r['nombre_paciente'] . ' esta pendiente.';
            } elseif ($diff <= 1800) {
                $tipo   = 'warning';
                $titulo = 'Proxima en ' . ceil($diff / 60) . ' min';
                $msg    = $r['nombre_paciente'] . ' a las ' . $horaStr . '.';
            } else {
                $tipo   = 'info';
                $titulo = 'Agenda de hoy';
                $msg    = $r['nombre_paciente'] . ' — ' . $horaStr . '.';
            }

            $items[] = [
                'tipo'    => $tipo,
                'icono'   => '🩺',
                'titulo'  => $titulo,
                'mensaje' => $msg,
                'tiempo'  => $horaStr,
                'accion'  => ['label' => 'Atender', 'href' => '/historial/atender?cita=' . (int) $r['id_cita']],
            ];
        }
        return $items;
    }

    // ── Secretaria: resumen global pendientes ──────────────────────────
    private function notifSecretaria(): array
    {
        $pendientes = (int) $this->db->query("SELECT COUNT(*) FROM cita WHERE id_estado = 1")->fetchColumn();
        $citasHoy   = (int) $this->db->query("SELECT COUNT(*) FROM cita WHERE fecha = CURDATE() AND id_estado NOT IN (4,5)")->fetchColumn();

        $items = [];

        if ($pendientes > 0) {
            $s = $pendientes > 1 ? 's' : '';
            $items[] = [
                'tipo'    => 'warning',
                'icono'   => '⏳',
                'titulo'  => $pendientes . ' cita' . $s . ' pendiente' . $s,
                'mensaje' => 'Hay ' . $pendientes . ' cita' . $s . ' esperando confirmacion en el sistema.',
                'tiempo'  => 'Ahora',
                'accion'  => ['label' => 'Ver citas', 'href' => '/citas'],
            ];
        }

        if ($citasHoy > 0) {
            $s = $citasHoy > 1 ? 's' : '';
            $items[] = [
                'tipo'    => 'info',
                'icono'   => '📅',
                'titulo'  => $citasHoy . ' cita' . $s . ' hoy',
                'mensaje' => 'Hay ' . $citasHoy . ' cita' . $s . ' programada' . $s . ' para hoy.',
                'tiempo'  => date('d/m'),
                'accion'  => ['label' => 'Ver agenda', 'href' => '/dashboard/secretaria'],
            ];
        }

        return $items;
    }

    // ── Helper: tiempo relativo ────────────────────────────────────────
    private function tiempoRelativo(string $fechaHora): string
    {
        $diff = time() - strtotime($fechaHora);
        if ($diff < 60)    return 'Hace un momento';
        if ($diff < 3600)  return 'Hace ' . floor($diff / 60) . ' min';
        if ($diff < 86400) return 'Hace ' . floor($diff / 3600) . 'h';
        return 'Hace ' . floor($diff / 86400) . ' dia' . (floor($diff / 86400) > 1 ? 's' : '');
    }
}
