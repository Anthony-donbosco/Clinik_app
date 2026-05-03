<?php

namespace App\Repositories;

use App\Config\Database;
use PDO;

/**
 * HistorialRepository — Repositorio exclusivo del módulo de Historial Médico.
 * Ejecuta SQL sobre las tablas historial, diagnostico, tratamiento y receta.
 */
class HistorialRepository
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
     * Retorna el historial de un paciente uniendo diagnóstico, tratamiento y receta.
     */
    public function getHistorialByPaciente(int $idPaciente): array
    {
        $sql = "SELECT h.id_historial,
                       h.id_cita,
                       d.descripcion AS diagnostico,
                       t.descripcion AS tratamiento,
                       r.indicaciones AS notas,
                       h.fecha AS fecha_registro,
                       c.fecha AS fecha_cita,
                       c.hora AS hora_cita,
                       CONCAT(doc.primer_nombre, ' ', doc.primer_apellido) AS nombre_doctor,
                       doc.especialidad
                FROM   historial h
                INNER JOIN cita c ON h.id_cita = c.id_cita
                INNER JOIN doctor doc ON c.id_doctor = doc.id_doctor
                LEFT JOIN diagnostico d ON h.id_historial = d.id_historial
                LEFT JOIN tratamiento t ON h.id_historial = t.id_historial
                LEFT JOIN receta r ON h.id_historial = r.id_historial
                WHERE  h.id_paciente = :id
                ORDER  BY h.fecha DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idPaciente]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna todos los historiales registrados por un doctor.
     */
    public function getHistorialByDoctor(int $idDoctor): array
    {
        $sql = "SELECT h.id_historial,
                       h.id_cita,
                       d.descripcion AS diagnostico,
                       t.descripcion AS tratamiento,
                       r.indicaciones AS notas,
                       h.fecha AS fecha_registro,
                       c.fecha AS fecha_cita,
                       c.hora AS hora_cita,
                       CONCAT(p.primer_nombre, ' ', p.primer_apellido) AS nombre_paciente,
                       p.numeroIdentificacion
                FROM   historial h
                INNER JOIN cita c ON h.id_cita = c.id_cita
                INNER JOIN paciente p ON h.id_paciente = p.id_paciente
                LEFT JOIN diagnostico d ON h.id_historial = d.id_historial
                LEFT JOIN tratamiento t ON h.id_historial = t.id_historial
                LEFT JOIN receta r ON h.id_historial = r.id_historial
                WHERE  c.id_doctor = :id
                ORDER  BY h.fecha DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idDoctor]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna un registro de historial específico por su ID.
     */
    public function getHistorialById(int $idHistorial): ?array
    {
        $sql = "SELECT h.id_historial, h.id_cita, h.fecha AS fecha_registro,
                       d.descripcion AS diagnostico,
                       t.descripcion AS tratamiento,
                       r.indicaciones AS notas,
                       c.fecha AS fecha_cita, c.hora AS hora_cita,
                       c.id_paciente, c.id_doctor,
                       CONCAT(p.primer_nombre, ' ', p.primer_apellido) AS nombre_paciente,
                       CONCAT(doc.primer_nombre, ' ', doc.primer_apellido) AS nombre_doctor,
                       doc.especialidad
                FROM   historial h
                INNER JOIN cita c ON h.id_cita = c.id_cita
                INNER JOIN paciente p ON h.id_paciente = p.id_paciente
                INNER JOIN doctor doc ON c.id_doctor = doc.id_doctor
                LEFT JOIN diagnostico d ON h.id_historial = d.id_historial
                LEFT JOIN tratamiento t ON h.id_historial = t.id_historial
                LEFT JOIN receta r ON h.id_historial = r.id_historial
                WHERE  h.id_historial = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idHistorial]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Verifica si una cita ya tiene historial registrado.
     */
    public function existeHistorialParaCita(int $idCita): bool
    {
        $sql  = "SELECT COUNT(*) FROM historial WHERE id_cita = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idCita]);
        return (int) $stmt->fetchColumn() > 0;
    }

    // ══════════════════════════════════════════════════════════════════════
    //  ESCRITURA
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Inserta el historial y distribuye los datos en las tablas correspondientes.
     * Utiliza una transacción para asegurar que todas las tablas se guarden correctamente.
     */
    public function crearHistorial(int $idCita, string $diagnostico, string $tratamiento, string $notas): int
    {
        try {
            $this->db->beginTransaction();

            // 1. Necesitamos el id_paciente que requiere la tabla historial
            $stmtCita = $this->db->prepare("SELECT id_paciente FROM cita WHERE id_cita = :id_cita");
            $stmtCita->execute([':id_cita' => $idCita]);
            $idPaciente = $stmtCita->fetchColumn();

            if (!$idPaciente) {
                throw new \Exception("Cita no encontrada.");
            }

            // 2. Insertar en la tabla principal `historial`
            $sqlHist = "INSERT INTO historial (id_paciente, id_cita, fecha) VALUES (:id_paciente, :id_cita, CURDATE())";
            $stmtHist = $this->db->prepare($sqlHist);
            $stmtHist->execute([
                ':id_paciente' => $idPaciente,
                ':id_cita'     => $idCita
            ]);
            $idHistorial = (int) $this->db->lastInsertId();

            // 3. Insertar en tabla `diagnostico`
            $sqlDiag = "INSERT INTO diagnostico (id_historial, descripcion) VALUES (:id_historial, :descripcion)";
            $stmtDiag = $this->db->prepare($sqlDiag);
            $stmtDiag->execute([
                ':id_historial' => $idHistorial,
                ':descripcion'  => $diagnostico
            ]);

            // 4. Insertar en tabla `tratamiento`
            $sqlTrat = "INSERT INTO tratamiento (id_historial, descripcion) VALUES (:id_historial, :descripcion)";
            $stmtTrat = $this->db->prepare($sqlTrat);
            $stmtTrat->execute([
                ':id_historial' => $idHistorial,
                ':descripcion'  => $tratamiento
            ]);

            // 5. Insertar en tabla `receta` (usado para las notas adicionales)
            if (!empty(trim($notas))) {
                $sqlRec = "INSERT INTO receta (id_historial, indicaciones) VALUES (:id_historial, :indicaciones)";
                $stmtRec = $this->db->prepare($sqlRec);
                $stmtRec->execute([
                    ':id_historial' => $idHistorial,
                    ':indicaciones' => trim($notas)
                ]);
            }

            $this->db->commit();
            return $idHistorial;

        } catch (\Exception $e) {
            // Si algo falla, revertimos todos los inserts para no dejar datos a medias
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Actualiza un historial medico existente.
     * Modifica diagnostico, tratamiento y receta.
     */
    public function actualizarHistorial(int $idHistorial, string $diagnostico, string $tratamiento, string $notas): bool
    {
        try {
            $this->db->beginTransaction();

            // 1. Actualizar diagnostico
            $sqlDiag = "UPDATE diagnostico SET descripcion = :descripcion WHERE id_historial = :id_historial";
            $stmtDiag = $this->db->prepare($sqlDiag);
            $stmtDiag->execute([':id_historial' => $idHistorial, ':descripcion' => $diagnostico]);

            // 2. Actualizar tratamiento
            $sqlTrat = "UPDATE tratamiento SET descripcion = :descripcion WHERE id_historial = :id_historial";
            $stmtTrat = $this->db->prepare($sqlTrat);
            $stmtTrat->execute([':id_historial' => $idHistorial, ':descripcion' => $tratamiento]);

            // 3. Actualizar receta (si no existia, habria que hacer INSERT ON DUPLICATE KEY, o simplemente asumiendo que existe o no existe)
            // Ya que el esquema no tiene ON DUPLICATE KEY, verificamos si existe receta:
            $stmtCheck = $this->db->prepare("SELECT COUNT(*) FROM receta WHERE id_historial = :id");
            $stmtCheck->execute([':id' => $idHistorial]);
            $existeReceta = (int) $stmtCheck->fetchColumn() > 0;

            if ($existeReceta) {
                if (empty(trim($notas))) {
                    $this->db->prepare("DELETE FROM receta WHERE id_historial = :id")->execute([':id' => $idHistorial]);
                } else {
                    $this->db->prepare("UPDATE receta SET indicaciones = :notas WHERE id_historial = :id")
                             ->execute([':id' => $idHistorial, ':notas' => trim($notas)]);
                }
            } else {
                if (!empty(trim($notas))) {
                    $this->db->prepare("INSERT INTO receta (id_historial, indicaciones) VALUES (:id, :notas)")
                             ->execute([':id' => $idHistorial, ':notas' => trim($notas)]);
                }
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Marca la cita como Atendida (id_estado = 4).
     */
    public function marcarCitaAtendida(int $idCita): bool
    {
        $sql  = "UPDATE cita SET id_estado = 4 WHERE id_cita = :id AND id_estado IN (1, 2)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idCita]);
        return $stmt->rowCount() > 0;
    }
}