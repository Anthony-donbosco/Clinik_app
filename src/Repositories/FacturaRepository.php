<?php

namespace App\Repositories;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * FacturaRepository — CRUD sobre la tabla `factura`.
 * La secretaria genera facturas; el paciente las consulta.
 */
class FacturaRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // ══════════════════════════════════════════════════════════════════════
    //  LECTURA
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Todas las facturas del sistema (para Secretaria).
     */
    public function getAllFacturas(int $limite = 50): array
    {
        $sql = "SELECT f.id_factura, f.fecha, f.detalle, f.monto,
                       c.fecha AS fecha_cita, c.hora AS hora_cita,
                       CONCAT(p.primer_nombre, ' ', p.primer_apellido) AS nombre_paciente,
                       p.numeroIdentificacion,
                       CONCAT(d.primer_nombre, ' ', d.primer_apellido) AS nombre_doctor,
                       d.especialidad
                FROM   factura f
                INNER JOIN cita    c ON f.id_cita    = c.id_cita
                INNER JOIN paciente p ON c.id_paciente = p.id_paciente
                INNER JOIN doctor   d ON c.id_doctor   = d.id_doctor
                ORDER  BY f.fecha DESC, f.id_factura DESC
                LIMIT  :limite";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Facturas de un paciente específico (para vista del Paciente).
     */
    public function getFacturasByPaciente(int $idPaciente, int $limite = 20): array
    {
        $sql = "SELECT f.id_factura, f.fecha, f.detalle, f.monto,
                       c.fecha AS fecha_cita, c.hora AS hora_cita,
                       CONCAT(d.primer_nombre, ' ', d.primer_apellido) AS nombre_doctor,
                       d.especialidad
                FROM   factura f
                INNER JOIN cita    c ON f.id_cita   = c.id_cita
                INNER JOIN doctor  d ON c.id_doctor = d.id_doctor
                WHERE  c.id_paciente = :id
                ORDER  BY f.fecha DESC, f.id_factura DESC
                LIMIT  :limite";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id',     $idPaciente, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limite,      PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Citas atendidas que aún no tienen factura (para el select de crear factura).
     */
    public function getCitasSinFactura(): array
    {
        $sql = "SELECT c.id_cita,
                       c.fecha, c.hora,
                       CONCAT(p.primer_nombre, ' ', p.primer_apellido) AS nombre_paciente,
                       p.numeroIdentificacion,
                       CONCAT(d.primer_nombre, ' ', d.primer_apellido) AS nombre_doctor,
                       d.especialidad
                FROM   cita c
                INNER JOIN paciente p ON c.id_paciente = p.id_paciente
                INNER JOIN doctor   d ON c.id_doctor   = d.id_doctor
                WHERE  c.id_estado = 4
                  AND  c.id_cita NOT IN (SELECT id_cita FROM factura)
                ORDER  BY c.fecha DESC, c.hora DESC
                LIMIT  100";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica si una cita ya tiene factura emitida.
     */
    public function existeFacturaParaCita(int $idCita): bool
    {
        $sql  = "SELECT COUNT(*) FROM factura WHERE id_cita = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idCita]);
        return (int) $stmt->fetchColumn() > 0;
    }

    // ══════════════════════════════════════════════════════════════════════
    //  ESCRITURA
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Crea una nueva factura para una cita atendida.
     *
     * @return int ID de la factura creada
     */
    public function crearFactura(int $idCita, string $detalle, float $monto): int
    {
        $sql = "INSERT INTO factura (id_cita, fecha, detalle, monto)
                VALUES (:id_cita, CURDATE(), :detalle, :monto)";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id_cita'  => $idCita,
                ':detalle'  => $detalle,
                ':monto'    => $monto,
            ]);
            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                throw new \RuntimeException('Esta cita ya tiene una factura registrada.');
            }
            throw $e;
        }
    }
}
