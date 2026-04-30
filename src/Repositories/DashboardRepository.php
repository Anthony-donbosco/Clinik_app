<?php

namespace App\Repositories;

use App\Config\Database;
use PDO;

class DashboardRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function countPacientesActivos(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM paciente WHERE id_estado = 8");
        return (int) $stmt->fetchColumn();
    }

    public function countDoctoresActivos(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM doctor WHERE id_estado = 8");
        return (int) $stmt->fetchColumn();
    }

    public function countCitasHoy(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM cita WHERE fecha = CURDATE() AND id_estado != 5");
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function countCitasPendientes(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM cita WHERE id_estado = 1");
        return (int) $stmt->fetchColumn();
    }

    public function getCitasDeHoy(): array
    {
        $sql = "SELECT c.id_cita,
                       c.hora,
                       CONCAT(p.primer_nombre, ' ', p.primer_apellido) AS nombre_paciente,
                       CONCAT(d.primer_nombre, ' ', d.primer_apellido) AS nombre_doctor,
                       d.especialidad,
                       e.nombre AS estado
                FROM   cita c
                INNER JOIN paciente p ON c.id_paciente = p.id_paciente
                INNER JOIN doctor   d ON c.id_doctor   = d.id_doctor
                INNER JOIN estado   e ON c.id_estado   = e.id_estado
                WHERE  c.fecha = CURDATE()
                AND    c.id_estado != 5
                ORDER  BY c.hora ASC
                LIMIT  20";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllPacientesActivos(): array
    {
        $sql = "SELECT p.id_paciente,
                       CONCAT(p.primer_nombre, ' ', COALESCE(p.segundo_nombre,''), ' ',
                              p.primer_apellido, ' ', COALESCE(p.segundo_apellido,'')) AS nombre_completo,
                       p.numeroIdentificacion,
                       p.telefono,
                       e.nombre AS estado
                FROM   paciente p
                INNER JOIN estado e ON p.id_estado = e.id_estado
                WHERE  p.id_estado = 8
                ORDER  BY p.primer_apellido ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllDoctoresActivos(): array
    {
        $sql = "SELECT d.id_doctor,
                       CONCAT(d.primer_nombre, ' ', d.primer_apellido) AS nombre_completo,
                       d.especialidad,
                       e.nombre AS estado
                FROM   doctor d
                INNER JOIN estado e ON d.id_estado = e.id_estado
                WHERE  d.id_estado = 8
                ORDER  BY d.primer_apellido ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStatsDoctor(int $idDoctor): array
    {
        $sql = "SELECT
                    SUM(CASE WHEN fecha = CURDATE() AND id_estado != 5 THEN 1 ELSE 0 END) AS hoy,
                    SUM(CASE WHEN id_estado = 1 THEN 1 ELSE 0 END)                         AS pendientes,
                    SUM(CASE WHEN id_estado = 4 THEN 1 ELSE 0 END)                         AS atendidas,
                    SUM(CASE WHEN fecha >= CURDATE() AND id_estado = 1 THEN 1 ELSE 0 END)  AS proximas
                FROM cita
                WHERE id_doctor = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idDoctor]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return array_map('intval', $row ?: ['hoy'=>0,'pendientes'=>0,'atendidas'=>0,'proximas'=>0]);
    }

    public function getAgendaDoctor(int $idDoctor): array
    {
        $sql = "SELECT c.id_cita, c.fecha, c.hora,
                       CONCAT(p.primer_nombre, ' ', p.primer_apellido) AS nombre_paciente,
                       p.numeroIdentificacion,
                       e.nombre AS estado, c.id_estado
                FROM   cita c
                INNER JOIN paciente p ON c.id_paciente = p.id_paciente
                INNER JOIN estado   e ON c.id_estado   = e.id_estado
                WHERE  c.id_doctor = :id
                AND    c.fecha BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                AND    c.id_estado NOT IN (5)
                ORDER  BY c.fecha ASC, c.hora ASC
                LIMIT  30";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idDoctor]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStatsPaciente(int $idPaciente): array
    {
        $sql = "SELECT
                    COUNT(*)                                                AS total,
                    SUM(CASE WHEN id_estado = 4 THEN 1 ELSE 0 END)         AS atendidas,
                    SUM(CASE WHEN id_estado = 1 THEN 1 ELSE 0 END)         AS pendientes
                FROM cita WHERE id_paciente = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idPaciente]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return array_map('intval', $row ?: ['total'=>0,'atendidas'=>0,'pendientes'=>0]);
    }

    public function getCitasPaciente(int $idPaciente): array
    {
        $sql = "SELECT c.id_cita, c.fecha, c.hora, c.id_estado,
                       CONCAT(d.primer_nombre, ' ', d.primer_apellido) AS nombre_doctor,
                       d.especialidad, e.nombre AS estado
                FROM   cita c
                INNER JOIN doctor  d ON c.id_doctor  = d.id_doctor
                INNER JOIN estado  e ON c.id_estado  = e.id_estado
                WHERE  c.id_paciente = :id
                ORDER  BY c.fecha DESC, c.hora DESC
                LIMIT  15";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idPaciente]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getHistorialPaciente(int $idPaciente): array
    {
        try {
            $sql = "SELECT h.id_historial, h.fecha,
                           CONCAT(d.primer_nombre, ' ', d.primer_apellido) AS nombre_doctor,
                           d.especialidad
                    FROM   historial h
                    INNER JOIN cita   c ON h.id_cita     = c.id_cita
                    INNER JOIN doctor d ON c.id_doctor   = d.id_doctor
                    WHERE  h.id_paciente = :id
                    ORDER  BY h.fecha DESC
                    LIMIT  10";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $idPaciente]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException) {
            return [];
        }
    }
}