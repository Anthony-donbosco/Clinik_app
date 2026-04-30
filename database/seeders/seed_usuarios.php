<?php
/**
 * ============================================================
 *  CLINI-K — Seeder de Usuarios
 *  database/seeders/seed_usuarios.php
 *
 *  Ejecutar UNA SOLA VEZ desde la línea de comandos:
 *    C:\xampp\php\php.exe database/seeders/seed_usuarios.php
 *
 *  Este script:
 *    1. Genera hashes bcrypt reales con password_hash()
 *    2. Inserta los usuarios de prueba en la tabla `usuarios`
 *    3. Reporta el resultado en consola
 * ============================================================
 */

declare(strict_types=1);

// ── Configuración de conexión ──────────────────────────────────────────────
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'clinik_bd');
define('DB_USER', 'root');
define('DB_PASS', '');

// ── Contraseña por defecto para todos los usuarios de prueba ───────────────
// CAMBIA ESTO antes de usar en cualquier entorno real
const DEFAULT_PASSWORD = 'clinik2024';

// ── Usuarios a insertar ────────────────────────────────────────────────────
// Formato: [nombre, apellido, correo, numero_identificacion, id_rol, id_referencia]
//   id_rol: 1=Paciente | 2=Doctor | 3=Secretaria
//   id_referencia: id en tabla doctor o paciente (NULL para secretaria)
$usuarios = [

    // ── SECRETARIA (1 usuario, sin referencia) ─────────────────────────
    [
        'nombre'               => 'Ana',
        'apellido'             => 'González',
        'correo'               => 'secretaria@clinik.com',
        'numero_identificacion' => 'SEC-001',
        'id_rol'               => 3,
        'id_referencia'        => null,
    ],

    // ── DOCTORES (referencia al id_doctor en tabla doctor) ─────────────
    ['nombre' => 'Roberto',   'apellido' => 'Salazar',    'correo' => 'rsalazar@clinik.com',   'numero_identificacion' => 'DOC-001', 'id_rol' => 2, 'id_referencia' => 1],
    ['nombre' => 'Elena',     'apellido' => 'Cruz',       'correo' => 'ecruz@clinik.com',       'numero_identificacion' => 'DOC-002', 'id_rol' => 2, 'id_referencia' => 2],
    ['nombre' => 'Mauricio',  'apellido' => 'Rivas',      'correo' => 'mrivas@clinik.com',      'numero_identificacion' => 'DOC-003', 'id_rol' => 2, 'id_referencia' => 3],
    ['nombre' => 'Génesis',   'apellido' => 'Mejía',      'correo' => 'gmejia@clinik.com',      'numero_identificacion' => 'DOC-004', 'id_rol' => 2, 'id_referencia' => 4],
    ['nombre' => 'Manuel',    'apellido' => 'Miranda',    'correo' => 'mmiranda@clinik.com',    'numero_identificacion' => 'DOC-005', 'id_rol' => 2, 'id_referencia' => 5],

    // ── PACIENTES (referencia al id_paciente en tabla paciente) ────────
    ['nombre' => 'Juan',    'apellido' => 'López',    'correo' => 'jlopez@mail.com',    'numero_identificacion' => '00000001-1', 'id_rol' => 1, 'id_referencia' => 1],
    ['nombre' => 'María',   'apellido' => 'Gómez',   'correo' => 'mgomez@mail.com',    'numero_identificacion' => '00000002-2', 'id_rol' => 1, 'id_referencia' => 2],
    ['nombre' => 'Pedro',   'apellido' => 'Díaz',    'correo' => 'pdiaz@mail.com',     'numero_identificacion' => '00000003-3', 'id_rol' => 1, 'id_referencia' => 3],
    ['nombre' => 'Lucía',   'apellido' => 'Ramos',   'correo' => 'lramos@mail.com',    'numero_identificacion' => '00000004-4', 'id_rol' => 1, 'id_referencia' => 4],
    ['nombre' => 'Jorge',   'apellido' => 'Ortiz',   'correo' => 'jortiz@mail.com',    'numero_identificacion' => '00000005-5', 'id_rol' => 1, 'id_referencia' => 5],
];

// ── Conexión PDO ───────────────────────────────────────────────────────────
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "\n✅ Conexión exitosa a la base de datos '" . DB_NAME . "'\n\n";
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage() . "\n");
}

// ── Verificar que la migración ya fue ejecutada ────────────────────────────
$check = $pdo->query("SHOW TABLES LIKE 'usuarios'")->fetchColumn();
if (!$check) {
    die("❌ La tabla 'usuarios' no existe.\n   Ejecuta primero: database/migrations/create_auth_tables.sql\n\n");
}

// ── Insertar usuarios ──────────────────────────────────────────────────────
$hash = password_hash(DEFAULT_PASSWORD, PASSWORD_BCRYPT, ['cost' => 12]);

$sql = "INSERT INTO usuarios 
            (nombre, apellido, correo, numero_identificacion, contrasena_hash, id_rol, id_referencia, id_estado)
        VALUES 
            (:nombre, :apellido, :correo, :nid, :hash, :rol, :ref, 8)
        ON DUPLICATE KEY UPDATE
            contrasena_hash = VALUES(contrasena_hash),
            id_referencia   = VALUES(id_referencia)";

$stmt = $pdo->prepare($sql);

$insertados  = 0;
$actualizados = 0;
$errores     = 0;

foreach ($usuarios as $u) {
    try {
        $antes = $pdo->query("SELECT ROW_COUNT()")->fetchColumn();
        $stmt->execute([
            ':nombre'   => $u['nombre'],
            ':apellido' => $u['apellido'],
            ':correo'   => $u['correo'],
            ':nid'      => $u['numero_identificacion'],
            ':hash'     => $hash,
            ':rol'      => $u['id_rol'],
            ':ref'      => $u['id_referencia'],
        ]);

        $rolNombre = match($u['id_rol']) { 1 => 'Paciente', 2 => 'Doctor', 3 => 'Secretaria', default => '?' };
        echo "   ✓ [{$rolNombre}] {$u['nombre']} {$u['apellido']} ({$u['numero_identificacion']})\n";
        $insertados++;

    } catch (PDOException $e) {
        echo "   ⚠  Error en {$u['correo']}: " . $e->getMessage() . "\n";
        $errores++;
    }
}

// ── Resumen ────────────────────────────────────────────────────────────────
echo "\n════════════════════════════════════════\n";
echo "  Insertados/actualizados: {$insertados}\n";
echo "  Errores:                 {$errores}\n";
echo "════════════════════════════════════════\n";
echo "\n📋 CREDENCIALES DE ACCESO AL SISTEMA:\n";
echo "   Secretaria  → Credencial: SEC-001       | Pass: " . DEFAULT_PASSWORD . "\n";
echo "   Doctor 1    → Credencial: DOC-001       | Pass: " . DEFAULT_PASSWORD . "\n";
echo "   Paciente 1  → Credencial: 00000001-1    | Pass: " . DEFAULT_PASSWORD . "\n";
echo "\n⚠  IMPORTANTE: Cambia las contraseñas antes de entregar el sistema.\n\n";
