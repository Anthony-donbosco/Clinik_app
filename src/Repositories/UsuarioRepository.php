<?php

namespace App\Repositories;

use App\Config\Database;
use PDO;
use PDOException;

class UsuarioRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function existeCorreo(string $correo): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM usuarios WHERE correo = :c");
        $stmt->execute([':c' => $correo]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function existeIdentificacion(string $nid): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM usuarios WHERE numero_identificacion = :n");
        $stmt->execute([':n' => $nid]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function getAllUsuarios(): array
    {
        $sql = "SELECT u.id_usuario, u.nombre, u.apellido, u.correo,
                       u.numero_identificacion, u.id_estado,
                       r.nombre_rol, e.nombre AS estado_nombre
                FROM   usuarios u
                INNER JOIN roles  r ON u.id_rol    = r.id_rol
                INNER JOIN estado e ON u.id_estado = e.id_estado
                ORDER  BY u.id_rol ASC, u.apellido ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDoctoresLibres(): array
    {
        $sql = "SELECT d.id_doctor,
                       CONCAT(d.primer_nombre, ' ', d.primer_apellido) AS nombre_completo,
                       d.especialidad
                FROM   doctor d
                WHERE  d.id_estado = 8
                AND    d.id_doctor NOT IN (
                    SELECT id_referencia FROM usuarios
                    WHERE id_rol = 2 AND id_referencia IS NOT NULL
                )
                ORDER  BY d.primer_apellido ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarPaciente(array $data): int
    {
        $this->db->beginTransaction();
        try {
            // Se quitó 'correo' y se agregó 'fecha_nacimiento' por defecto
            $sqlPac = "INSERT INTO paciente
                           (primer_nombre, primer_apellido, telefono, fecha_nacimiento,
                            numeroIdentificacion, id_estado)
                       VALUES
                           (:nombre, :apellido, :telefono, '1990-01-01', :nid, 8)";
            $stmtPac = $this->db->prepare($sqlPac);
            $stmtPac->execute([
                ':nombre'   => $data['nombre'],
                ':apellido' => $data['apellido'],
                ':telefono' => $data['telefono'] ?? '',
                ':nid'      => $data['numero_identificacion'],
            ]);
            $idPaciente = (int) $this->db->lastInsertId();

            $sqlUsr = "INSERT INTO usuarios
                           (nombre, apellido, correo, numero_identificacion,
                            contrasena_hash, id_rol, id_referencia, id_estado)
                       VALUES
                           (:nombre, :apellido, :correo, :nid,
                            :hash, 1, :ref, 8)";
            $stmtUsr = $this->db->prepare($sqlUsr);
            $stmtUsr->execute([
                ':nombre'   => $data['nombre'],
                ':apellido' => $data['apellido'],
                ':correo'   => $data['correo'],
                ':nid'      => $data['numero_identificacion'],
                ':hash'     => password_hash($data['contrasena'], PASSWORD_BCRYPT, ['cost' => 12]),
                ':ref'      => $idPaciente,
            ]);
            $idUsuario = (int) $this->db->lastInsertId();

            $this->db->commit();
            return $idUsuario;

        } catch (PDOException $e) {
            $this->db->rollBack();
            if ($e->getCode() === '23000') {
                throw new \RuntimeException('El correo o la cédula ya están registrados.');
            }
            throw $e;
        }
    }

    public function crearUsuarioDoctor(array $data): int
    {
        $sql = "INSERT INTO usuarios
                    (nombre, apellido, correo, numero_identificacion,
                     contrasena_hash, id_rol, id_referencia, id_estado)
                VALUES
                    (:nombre, :apellido, :correo, :nid,
                     :hash, 2, :ref, 8)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre'   => $data['nombre'],
            ':apellido' => $data['apellido'],
            ':correo'   => $data['correo'],
            ':nid'      => $data['numero_identificacion'],
            ':hash'     => password_hash($data['contrasena'], PASSWORD_BCRYPT, ['cost' => 12]),
            ':ref'      => (int) $data['id_referencia'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function crearUsuarioStaff(array $data, int $idRol): int
    {
        $sql = "INSERT INTO usuarios
                    (nombre, apellido, correo, numero_identificacion,
                     contrasena_hash, id_rol, id_referencia, id_estado)
                VALUES
                    (:nombre, :apellido, :correo, :nid,
                     :hash, :rol, NULL, 8)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre'   => $data['nombre'],
            ':apellido' => $data['apellido'],
            ':correo'   => $data['correo'],
            ':nid'      => $data['numero_identificacion'],
            ':hash'     => password_hash($data['contrasena'], PASSWORD_BCRYPT, ['cost' => 12]),
            ':rol'      => $idRol,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function desactivarUsuario(int $idUsuario): bool
    {
        $sql  = "UPDATE usuarios SET id_estado = 9 WHERE id_usuario = :id AND id_estado = 8";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idUsuario]);
        return $stmt->rowCount() > 0;
    }

    public function reactivarUsuario(int $idUsuario): bool
    {
        $sql  = "UPDATE usuarios SET id_estado = 8 WHERE id_usuario = :id AND id_estado = 9";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idUsuario]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Retorna los datos del perfil de un paciente (join paciente + usuarios).
     */
    public function getPerfilPaciente(int $idPaciente): ?array
    {
        $sql = "SELECT p.id_paciente, p.primer_nombre, p.segundo_nombre,
                       p.primer_apellido, p.segundo_apellido,
                       p.telefono, p.numeroIdentificacion,
                       u.correo, u.id_usuario
                FROM   paciente p
                INNER JOIN usuarios u ON u.id_referencia = p.id_paciente AND u.id_rol = 1
                WHERE  p.id_paciente = :id
                LIMIT  1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idPaciente]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Actualiza teléfono y correo de un paciente.
     * No permite cambiar cédula ni nombre (datos sensibles).
     *
     * @return array ['ok' => bool, 'mensaje' => string]
     */
    public function actualizarPerfilPaciente(int $idPaciente, int $idUsuario, string $telefono, string $correo): array
    {
        // Verificar que el correo no pertenezca a otro usuario
        $stmtCheck = $this->db->prepare(
            "SELECT COUNT(*) FROM usuarios WHERE correo = :correo AND id_usuario != :id"
        );
        $stmtCheck->execute([':correo' => $correo, ':id' => $idUsuario]);
        if ((int) $stmtCheck->fetchColumn() > 0) {
            return ['ok' => false, 'mensaje' => 'Este correo ya está en uso por otra cuenta.'];
        }

        $this->db->beginTransaction();
        try {
            // Actualizar teléfono en tabla paciente
            $sqlPac = "UPDATE paciente SET telefono = :tel WHERE id_paciente = :id";
            $stmtPac = $this->db->prepare($sqlPac);
            $stmtPac->execute([':tel' => $telefono, ':id' => $idPaciente]);

            // Actualizar correo en tabla usuarios
            $sqlUsr = "UPDATE usuarios SET correo = :correo WHERE id_usuario = :id";
            $stmtUsr = $this->db->prepare($sqlUsr);
            $stmtUsr->execute([':correo' => $correo, ':id' => $idUsuario]);

            $this->db->commit();
            return ['ok' => true, 'mensaje' => 'Perfil actualizado correctamente.'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['ok' => false, 'mensaje' => 'Error al actualizar el perfil. Inténtalo de nuevo.'];
        }
    }
}