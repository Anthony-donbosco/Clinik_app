<?php

namespace App\Repositories;

use App\Config\Database;
use PDO;
use PDOException;

class CitaRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getHorasOcupadas(int $idDoctor, string $fecha): array
    {
        $sql = "SELECT hora
                FROM   cita
                WHERE  id_doctor = :doctor
                AND    fecha     = :fecha
                AND    id_estado != 5";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':doctor' => $idDoctor, ':fecha' => $fecha]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function existeConflicto(int $idDoctor, string $fecha, string $hora): bool
    {
        $sql = "SELECT COUNT(*) FROM cita
                WHERE  id_doctor = :doctor
                AND    fecha     = :fecha
                AND    hora      = :hora
                AND    id_estado != 5";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':doctor' => $idDoctor, ':fecha' => $fecha, ':hora' => $hora]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function getCitaById(int $idCita): ?array
    {
        $sql = "SELECT c.*,
                       CONCAT(p.primer_nombre, ' ', p.primer_apellido) AS nombre_paciente,
                       p.numeroIdentificacion, 
                       CONCAT(d.primer_nombre, ' ', d.primer_apellido) AS nombre_doctor,
                       d.especialidad,
                       e.nombre AS estado
                FROM   cita c
                INNER JOIN paciente p ON c.id_paciente = p.id_paciente
                INNER JOIN doctor   d ON c.id_doctor   = d.id_doctor
                INNER JOIN estado   e ON c.id_estado   = e.id_estado
                WHERE  c.id_cita = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idCita]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getAllCitas(int $limite = 150, int $offset = 0, string $fecha = ''): array
    {
        $fechaCond = $fecha ? "AND c.fecha = :fecha" : "";
        $sql = "SELECT c.id_cita, c.fecha, c.hora, c.id_estado,
                       CONCAT(p.primer_nombre, ' ', p.primer_apellido) AS nombre_paciente,
                       CONCAT(d.primer_nombre, ' ', d.primer_apellido) AS nombre_doctor,
                       d.especialidad,
                       e.nombre AS estado
                FROM   cita c
                INNER JOIN paciente p ON c.id_paciente = p.id_paciente
                INNER JOIN doctor   d ON c.id_doctor   = d.id_doctor
                INNER JOIN estado   e ON c.id_estado   = e.id_estado
                WHERE  c.id_estado != 9 $fechaCond
                ORDER  BY c.id_cita DESC
                LIMIT  :limite OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limite',  $limite,  PDO::PARAM_INT);
        $stmt->bindValue(':offset',  $offset,  PDO::PARAM_INT);
        if ($fecha) $stmt->bindValue(':fecha', $fecha);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCitasByDoctor(int $idDoctor, int $limite = 150, string $fecha = ''): array
    {
        $fechaCond = $fecha ? "AND c.fecha = :fecha" : "";
        $sql = "SELECT c.id_cita, c.fecha, c.hora, c.id_estado,
                       CONCAT(p.primer_nombre, ' ', p.primer_apellido) AS nombre_paciente,
                       p.numeroIdentificacion,
                       e.nombre AS estado
                FROM   cita c
                INNER JOIN paciente p ON c.id_paciente = p.id_paciente
                INNER JOIN estado   e ON c.id_estado   = e.id_estado
                WHERE  c.id_doctor  = :id
                AND    c.id_estado  IN (2, 4) $fechaCond
                ORDER  BY c.id_cita DESC
                LIMIT  :limite";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id',     $idDoctor, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limite,   PDO::PARAM_INT);
        if ($fecha) $stmt->bindValue(':fecha', $fecha);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCitasByPaciente(int $idPaciente, int $limite = 150, string $fecha = ''): array
    {
        $fechaCond = $fecha ? "AND c.fecha = :fecha" : "";
        $sql = "SELECT c.id_cita, c.fecha, c.hora, c.id_estado,
                       CONCAT(d.primer_nombre, ' ', d.primer_apellido) AS nombre_doctor,
                       d.especialidad,
                       e.nombre AS estado
                FROM   cita c
                INNER JOIN doctor  d ON c.id_doctor  = d.id_doctor
                INNER JOIN estado  e ON c.id_estado  = e.id_estado
                WHERE  c.id_paciente = :id $fechaCond
                ORDER  BY c.id_cita DESC
                LIMIT  :limite";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id',     $idPaciente, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limite,      PDO::PARAM_INT);
        if ($fecha) $stmt->bindValue(':fecha', $fecha);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDoctoresParaSelect(): array
    {
        $sql = "SELECT id_doctor,
                       CONCAT(primer_nombre, ' ', primer_apellido) AS nombre_completo,
                       especialidad
                FROM   doctor
                WHERE  id_estado = 8
                ORDER  BY primer_apellido ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPacientesParaSelect(): array
    {
        $sql = "SELECT id_paciente,
                       CONCAT(primer_nombre, ' ', primer_apellido) AS nombre_completo,
                       numeroIdentificacion
                FROM   paciente
                WHERE  id_estado = 8
                ORDER  BY primer_apellido ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearCita(int $idDoctor, int $idPaciente, string $fecha, string $hora, int $idEstado = 1): int
    {
        $sql = "INSERT INTO cita (id_doctor, id_paciente, fecha, hora, id_estado)
                VALUES (:doctor, :paciente, :fecha, :hora, :estado)";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':doctor'   => $idDoctor,
                ':paciente' => $idPaciente,
                ':fecha'    => $fecha,
                ':hora'     => $hora,
                ':estado'   => $idEstado,
            ]);
            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                throw new \RuntimeException('El horario seleccionado ya fue reservado. Por favor, elige otro.');
            }
            throw $e;
        }
    }

    public function cancelarCita(int $idCita): bool
    {
        $sql  = "UPDATE cita SET id_estado = 5 WHERE id_cita = :id AND id_estado NOT IN (4, 5)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idCita]);
        return $stmt->rowCount() > 0;
    }

    public function marcarAtendida(int $idCita): bool
    {
        $sql  = "UPDATE cita SET id_estado = 4 WHERE id_cita = :id AND id_estado = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idCita]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Cambia la cita a estado Aprobada (2) — solo Secretaria.
     * Solo aplica si la cita está en estado Pendiente (1).
     */
    public function aprobarCita(int $idCita): bool
    {
        $sql  = "UPDATE cita SET id_estado = 2 WHERE id_cita = :id AND id_estado = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idCita]);
        return $stmt->rowCount() > 0;
    }

    public function rechazarCita(int $idCita): bool
    {
        $sql  = "UPDATE cita SET id_estado = 3 WHERE id_cita = :id AND id_estado IN (1, 2)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idCita]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Reagenda una cita a una nueva fecha y hora, y reinicia el estado a Pendiente (1).
     */
    public function reagendarCita(int $idCita, string $fecha, string $hora): bool
    {
        $sql = "UPDATE cita 
                SET fecha = :fecha, hora = :hora, id_estado = 1 
                WHERE id_cita = :id AND id_estado NOT IN (4, 5)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $idCita,
            ':fecha' => $fecha,
            ':hora' => $hora
        ]);
        return $stmt->rowCount() > 0;
    }
}