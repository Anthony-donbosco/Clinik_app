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

    /**
     * Lista todos los usuarios del sistema para el dashboard del Admin.
     * Para los Doctores (id_rol=2), enriquece con datos de la tabla doctor
     * (especialidad) a través del id_referencia.
     */
    public function getAllUsuarios(): array
    {
        $sql = "SELECT u.id_usuario, u.nombre, u.apellido, u.correo,
                       u.numero_identificacion, u.id_estado, u.id_referencia, u.id_rol,
                       r.nombre_rol, e.nombre AS estado_nombre,
                       d.especialidad, d.id_doctor
                FROM   usuarios u
                INNER JOIN roles  r ON u.id_rol    = r.id_rol
                INNER JOIN estado e ON u.id_estado = e.id_estado
                LEFT  JOIN doctor d ON u.id_referencia = d.id_doctor AND u.id_rol = 2
                ORDER  BY u.id_rol ASC, u.apellido ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Devuelve los doctores que ya tienen usuario de acceso creado.
     * Usado en el dashboard del Admin para la tabla de Doctores.
     */
    public function getDoctoresConUsuario(): array
    {
        $sql = "SELECT u.id_usuario, u.correo, u.numero_identificacion, u.id_estado,
                       d.id_doctor, d.primer_nombre, d.segundo_nombre,
                       d.primer_apellido, d.segundo_apellido, d.especialidad
                FROM   usuarios u
                INNER JOIN doctor d ON u.id_referencia = d.id_doctor
                WHERE  u.id_rol = 2
                ORDER  BY d.primer_apellido ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Devuelve los "doctores fantasma": existen en tabla doctor pero NO tienen
     * ningún registro en usuarios (no pueden iniciar sesión).
     * Son visibles para los pacientes pero inaccesibles para el propio doctor.
     */
    public function getDoctoresFantasma(): array
    {
        $sql = "SELECT d.id_doctor,
                       CONCAT(d.primer_nombre, ' ', COALESCE(d.segundo_nombre,''), ' ',
                              d.primer_apellido, ' ', COALESCE(d.segundo_apellido,'')) AS nombre_completo,
                       d.especialidad, d.id_estado
                FROM   doctor d
                WHERE  d.id_doctor NOT IN (
                    SELECT id_referencia
                    FROM   usuarios
                    WHERE  id_rol = 2 AND id_referencia IS NOT NULL
                )
                ORDER  BY d.primer_apellido ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Devuelve los datos de cualquier usuario (Staff o Admin) para prellenar
     * el formulario de edición.
     */
    public function getUsuarioById(int $idUsuario): ?array
    {
        $sql = "SELECT u.id_usuario, u.nombre, u.apellido, u.correo,
                       u.numero_identificacion, u.id_rol, u.id_estado,
                       r.nombre_rol
                FROM   usuarios u
                INNER JOIN roles r ON u.id_rol = r.id_rol
                WHERE  u.id_usuario = :id
                LIMIT  1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idUsuario]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Edita los datos de un usuario Staff (Secretaria=3 o Admin=4).
     * Solo actualiza: nombre, apellido, correo, numero_identificacion.
     * La contraseña se maneja por separado (si se requiere).
     */
    public function editarUsuarioStaff(int $idUsuario, array $data): bool
    {
        // Verificar que correo y NID no pertenezcan a otro usuario
        $stmtCorreo = $this->db->prepare(
            "SELECT COUNT(*) FROM usuarios WHERE correo = :correo AND id_usuario != :id"
        );
        $stmtCorreo->execute([':correo' => $data['correo'], ':id' => $idUsuario]);
        if ((int) $stmtCorreo->fetchColumn() > 0) {
            throw new \RuntimeException('El correo ya está en uso por otra cuenta.');
        }

        $stmtNid = $this->db->prepare(
            "SELECT COUNT(*) FROM usuarios WHERE numero_identificacion = :nid AND id_usuario != :id"
        );
        $stmtNid->execute([':nid' => $data['numero_identificacion'], ':id' => $idUsuario]);
        if ((int) $stmtNid->fetchColumn() > 0) {
            throw new \RuntimeException('La cédula/ID ya está en uso por otra cuenta.');
        }

        $sql = "UPDATE usuarios SET
                    nombre                = :nombre,
                    apellido              = :apellido,
                    correo                = :correo,
                    numero_identificacion = :nid
                WHERE id_usuario = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre'   => $data['nombre'],
            ':apellido' => $data['apellido'],
            ':correo'   => $data['correo'],
            ':nid'      => $data['numero_identificacion'],
            ':id'       => $idUsuario,
        ]);
        return $stmt->rowCount() >= 0; // true incluso si no hubo cambios
    }

    /**
     * Devuelve los datos completos de un doctor (tablas doctor + usuarios)
     * para prellenar el formulario de edición.
     */
    public function getDoctorById(int $idDoctor): ?array
    {
        $sql = "SELECT d.id_doctor, d.primer_nombre, d.segundo_nombre,
                       d.primer_apellido, d.segundo_apellido, d.especialidad, d.id_estado,
                       u.id_usuario, u.correo, u.numero_identificacion
                FROM   doctor d
                LEFT  JOIN usuarios u ON u.id_referencia = d.id_doctor AND u.id_rol = 2
                WHERE  d.id_doctor = :id
                LIMIT  1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idDoctor]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Crea SOLO las credenciales de acceso (usuarios) para un doctor que ya
     * existe en la tabla `doctor` ("doctor fantasma").
     * NO toca la tabla doctor — solo hace un INSERT en usuarios con id_referencia.
     *
     * @param int   $idDoctor — id_doctor existente en tabla doctor
     * @param array $data { nombre, apellido, correo, numero_identificacion, contrasena }
     * @return int  id_usuario creado
     */
    public function crearCredencialesParaDoctor(int $idDoctor, array $data): int
    {
        // Verificar que el doctor existe y no tiene usuario aún
        $stmtCheck = $this->db->prepare(
            "SELECT COUNT(*) FROM usuarios WHERE id_referencia = :id AND id_rol = 2"
        );
        $stmtCheck->execute([':id' => $idDoctor]);
        if ((int) $stmtCheck->fetchColumn() > 0) {
            throw new \RuntimeException('Este doctor ya tiene credenciales de acceso asignadas.');
        }

        $sql = "INSERT INTO usuarios
                    (nombre, apellido, correo, numero_identificacion,
                     contrasena_hash, id_rol, id_referencia, id_estado)
                VALUES
                    (:nombre, :apellido, :correo, :nid,
                     :hash, 2, :ref, 8)";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':nombre'   => $data['nombre'],
                ':apellido' => $data['apellido'],
                ':correo'   => $data['correo'],
                ':nid'      => $data['numero_identificacion'],
                ':hash'     => password_hash($data['contrasena'], PASSWORD_BCRYPT, ['cost' => 12]),
                ':ref'      => $idDoctor,
            ]);
            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                throw new \RuntimeException('El correo o la cédula/ID ya están registrados en el sistema.');
            }
            throw $e;
        }
    }



    /**
     * Crea un doctor desde cero:
     *   1. INSERT en tabla `doctor` (perfil médico)
     *   2. INSERT en tabla `usuarios` (credenciales de acceso, id_referencia → id_doctor)
     * Todo en una transacción atómica.
     *
     * @param array $data {
     *   primer_nombre, segundo_nombre, primer_apellido, segundo_apellido,
     *   especialidad, correo, numero_identificacion, contrasena
     * }
     * @return int id_usuario creado
     */
    public function crearDoctorCompleto(array $data): int
    {
        $this->db->beginTransaction();
        try {
            // 1. Insertar perfil en tabla doctor
            $sqlDoc = "INSERT INTO doctor
                           (primer_nombre, segundo_nombre, primer_apellido, segundo_apellido,
                            especialidad, id_estado)
                       VALUES
                           (:pnombre, :snombre, :papellido, :sapellido, :esp, 8)";
            $stmtDoc = $this->db->prepare($sqlDoc);
            $stmtDoc->execute([
                ':pnombre'   => $data['primer_nombre'],
                ':snombre'   => $data['segundo_nombre'] ?? null,
                ':papellido' => $data['primer_apellido'],
                ':sapellido' => $data['segundo_apellido'] ?? null,
                ':esp'       => $data['especialidad'],
            ]);
            $idDoctor = (int) $this->db->lastInsertId();

            // 2. Insertar credenciales en tabla usuarios
            $nombreCompleto  = trim($data['primer_nombre'] . ' ' . ($data['segundo_nombre'] ?? ''));
            $apellidoCompleto = trim($data['primer_apellido'] . ' ' . ($data['segundo_apellido'] ?? ''));

            $sqlUsr = "INSERT INTO usuarios
                           (nombre, apellido, correo, numero_identificacion,
                            contrasena_hash, id_rol, id_referencia, id_estado)
                       VALUES
                           (:nombre, :apellido, :correo, :nid,
                            :hash, 2, :ref, 8)";
            $stmtUsr = $this->db->prepare($sqlUsr);
            $stmtUsr->execute([
                ':nombre'   => $nombreCompleto,
                ':apellido' => $apellidoCompleto,
                ':correo'   => $data['correo'],
                ':nid'      => $data['numero_identificacion'],
                ':hash'     => password_hash($data['contrasena'], PASSWORD_BCRYPT, ['cost' => 12]),
                ':ref'      => $idDoctor,
            ]);
            $idUsuario = (int) $this->db->lastInsertId();

            $this->db->commit();
            return $idUsuario;

        } catch (PDOException $e) {
            $this->db->rollBack();
            if ($e->getCode() === '23000') {
                throw new \RuntimeException('El correo o la cédula/ID ya están registrados en el sistema.');
            }
            throw $e;
        }
    }

    /**
     * Edita los datos de un doctor existente en AMBAS tablas de forma atómica.
     * Actualiza la tabla `doctor` (datos del perfil) y `usuarios` (credenciales).
     * No cambia la contraseña (se manejaría por separado si fuera necesario).
     */
    public function editarDoctor(int $idDoctor, int $idUsuario, array $data): bool
    {
        $this->db->beginTransaction();
        try {
            // Actualizar perfil en tabla doctor
            $sqlDoc = "UPDATE doctor SET
                           primer_nombre   = :pnombre,
                           segundo_nombre  = :snombre,
                           primer_apellido = :papellido,
                           segundo_apellido= :sapellido,
                           especialidad    = :esp
                       WHERE id_doctor = :id";
            $stmtDoc = $this->db->prepare($sqlDoc);
            $stmtDoc->execute([
                ':pnombre'   => $data['primer_nombre'],
                ':snombre'   => $data['segundo_nombre'] ?? null,
                ':papellido' => $data['primer_apellido'],
                ':sapellido' => $data['segundo_apellido'] ?? null,
                ':esp'       => $data['especialidad'],
                ':id'        => $idDoctor,
            ]);

            // Actualizar credenciales en tabla usuarios (nombre, apellido, correo, nid)
            $nombreCompleto   = trim($data['primer_nombre'] . ' ' . ($data['segundo_nombre'] ?? ''));
            $apellidoCompleto = trim($data['primer_apellido'] . ' ' . ($data['segundo_apellido'] ?? ''));

            $sqlUsr = "UPDATE usuarios SET
                           nombre                = :nombre,
                           apellido              = :apellido,
                           correo                = :correo,
                           numero_identificacion = :nid
                       WHERE id_usuario = :id";
            $stmtUsr = $this->db->prepare($sqlUsr);
            $stmtUsr->execute([
                ':nombre'   => $nombreCompleto,
                ':apellido' => $apellidoCompleto,
                ':correo'   => $data['correo'],
                ':nid'      => $data['numero_identificacion'],
                ':id'       => $idUsuario,
            ]);

            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            $this->db->rollBack();
            if ($e->getCode() === '23000') {
                throw new \RuntimeException('El correo o la cédula/ID ya están en uso por otra cuenta.');
            }
            throw $e;
        }
    }

    /**
     * Crea un usuario de Staff (Secretaria=3 o Admin=4) — sin perfil en tabla doctor.
     */
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
        // Desactiva en usuarios y también en la tabla doctor si es un doctor
        $this->db->beginTransaction();
        try {
            $sqlUsr = "UPDATE usuarios SET id_estado = 9 WHERE id_usuario = :id AND id_estado = 8";
            $stmtUsr = $this->db->prepare($sqlUsr);
            $stmtUsr->execute([':id' => $idUsuario]);
            $affected = $stmtUsr->rowCount();

            // Si es doctor, también desactivar en tabla doctor
            $stmtRef = $this->db->prepare(
                "SELECT id_referencia FROM usuarios WHERE id_usuario = :id AND id_rol = 2"
            );
            $stmtRef->execute([':id' => $idUsuario]);
            $idDoctor = $stmtRef->fetchColumn();
            if ($idDoctor) {
                $sqlDoc = "UPDATE doctor SET id_estado = 9 WHERE id_doctor = :id";
                $this->db->prepare($sqlDoc)->execute([':id' => (int)$idDoctor]);
            }

            $this->db->commit();
            return $affected > 0;
        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function reactivarUsuario(int $idUsuario): bool
    {
        $this->db->beginTransaction();
        try {
            $sqlUsr = "UPDATE usuarios SET id_estado = 8 WHERE id_usuario = :id AND id_estado = 9";
            $stmtUsr = $this->db->prepare($sqlUsr);
            $stmtUsr->execute([':id' => $idUsuario]);
            $affected = $stmtUsr->rowCount();

            // Si es doctor, también reactivar en tabla doctor
            $stmtRef = $this->db->prepare(
                "SELECT id_referencia FROM usuarios WHERE id_usuario = :id AND id_rol = 2"
            );
            $stmtRef->execute([':id' => $idUsuario]);
            $idDoctor = $stmtRef->fetchColumn();
            if ($idDoctor) {
                $sqlDoc = "UPDATE doctor SET id_estado = 8 WHERE id_doctor = :id";
                $this->db->prepare($sqlDoc)->execute([':id' => (int)$idDoctor]);
            }

            $this->db->commit();
            return $affected > 0;
        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Registra un nuevo paciente (auto-registro público).
     * Inserta en paciente + usuarios en una transacción atómica.
     */
    public function registrarPaciente(array $data): int
    {
        $this->db->beginTransaction();
        try {
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
     *
     * @return array ['ok' => bool, 'mensaje' => string]
     */
    public function actualizarPerfilPaciente(int $idPaciente, int $idUsuario, string $telefono, string $correo): array
    {
        $stmtCheck = $this->db->prepare(
            "SELECT COUNT(*) FROM usuarios WHERE correo = :correo AND id_usuario != :id"
        );
        $stmtCheck->execute([':correo' => $correo, ':id' => $idUsuario]);
        if ((int) $stmtCheck->fetchColumn() > 0) {
            return ['ok' => false, 'mensaje' => 'Este correo ya está en uso por otra cuenta.'];
        }

        $this->db->beginTransaction();
        try {
            $sqlPac = "UPDATE paciente SET telefono = :tel WHERE id_paciente = :id";
            $stmtPac = $this->db->prepare($sqlPac);
            $stmtPac->execute([':tel' => $telefono, ':id' => $idPaciente]);

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