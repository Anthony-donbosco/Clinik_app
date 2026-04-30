<?php

namespace App\Repositories;

use App\Config\Database;
use PDO;

/**
 * AuthRepository -- Repositorio de autenticacion.
 * Responsabilidad unica: consultar la base de datos para
 * validar credenciales de usuario y obtener su perfil.
 */
class AuthRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Busca un usuario activo por su numero de identificacion o correo.
     * NOTA: PDO/MySQL no permite reusar el mismo named param en un WHERE,
     * por eso se usan :cred1 y :cred2 con el mismo valor.
     *
     * @param  string $credencial Numero de cedula o correo electronico.
     * @return array|null  Array con los datos del usuario, o null si no existe.
     */
    public function findActiveUserByCredential(string $credencial): ?array
    {
        // id_estado = 8 = Activo segun catalogo definido en la BD
        // id_referencia apunta a doctor.id_doctor o paciente.id_paciente segun rol
        $sql = "SELECT u.id_usuario, u.nombre, u.apellido, u.correo,
                       u.contrasena_hash, u.id_rol, u.id_estado,
                       u.id_referencia,
                       r.nombre_rol
                FROM   usuarios u
                INNER JOIN roles r ON u.id_rol = r.id_rol
                WHERE  (u.numero_identificacion = :cred1 OR u.correo = :cred2)
                AND    u.id_estado = 8
                LIMIT  1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cred1' => $credencial, ':cred2' => $credencial]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }
}