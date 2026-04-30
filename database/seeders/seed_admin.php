<?php
/**
 * ============================================================
 *  CLINI-K — Seeder de Usuario Admin
 *  database/seeders/seed_admin.php
 *
 *  Crea el rol Admin (id_rol=4) en la tabla `roles` si no existe,
 *  luego inserta el usuario administrador del sistema.
 *
 *  Ejecutar UNA SOLA VEZ:
 *    C:\xampp\php\php.exe database/seeders/seed_admin.php
 * ============================================================
 */

declare(strict_types=1);

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'clinik_bd');
define('DB_USER', 'root');
define('DB_PASS', '');

const ADMIN_NID  = 'ADMIN-001';
const ADMIN_PASS = 'Admin@Clinik2024';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "\n✅ Conexion exitosa a '{" . DB_NAME . "}'\n\n";
} catch (PDOException $e) {
    die("❌ Error de conexion: " . $e->getMessage() . "\n");
}

// ── 1. Asegurar que el rol Admin (id_rol=4) existe ─────────────────────────
$existeRol = $pdo->query("SELECT COUNT(*) FROM roles WHERE id_rol = 4")->fetchColumn();
if (!$existeRol) {
    $pdo->exec("INSERT INTO roles (id_rol, nombre_rol) VALUES (4, 'Admin')");
    echo "   ✓ Rol 'Admin' (id_rol=4) creado en tabla roles.\n";
} else {
    echo "   ✓ Rol 'Admin' ya existe.\n";
}

// ── 2. Insertar/actualizar usuario Admin ───────────────────────────────────
$hash = password_hash(ADMIN_PASS, PASSWORD_BCRYPT, ['cost' => 12]);

$sql = "INSERT INTO usuarios
            (nombre, apellido, correo, numero_identificacion,
             contrasena_hash, id_rol, id_referencia, id_estado)
        VALUES
            ('Super', 'Admin', 'admin@clinik.com', :nid,
             :hash, 4, NULL, 8)
        ON DUPLICATE KEY UPDATE
            contrasena_hash = VALUES(contrasena_hash),
            id_rol          = 4";

$stmt = $pdo->prepare($sql);
$stmt->execute([':nid' => ADMIN_NID, ':hash' => $hash]);

echo "   ✓ Usuario Admin creado/actualizado.\n";

// ── Resumen ────────────────────────────────────────────────────────────────
echo "\n════════════════════════════════════════\n";
echo "  CREDENCIALES DEL ADMINISTRADOR:\n";
echo "  Credencial : " . ADMIN_NID  . "\n";
echo "  Correo     : admin@clinik.com\n";
echo "  Contrasena : " . ADMIN_PASS . "\n";
echo "════════════════════════════════════════\n";
echo "\n⚠  IMPORTANTE: Cambia la contrasena del Admin antes de produccion.\n\n";
