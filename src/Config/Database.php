<?php

namespace App\Config;

use PDO;
use PDOException;

/**
 * Clase Database - Gestión de la conexión PDO a MySQL.
 * Implementa el patrón Singleton para garantizar una única
 * instancia de conexión durante todo el ciclo de vida de la petición.
 */
class Database
{
    // ──── Configuración de conexión ────────────────────────────────────────
    private const DB_HOST   = 'localhost';
    private const DB_PORT   = '3306';
    private const DB_NAME   = 'clinik_bd';
    private const DB_USER   = 'root';
    private const DB_PASS   = '';
    private const DB_CHARSET = 'utf8mb4';

    /** @var Database|null Instancia única (Singleton) */
    private static ?Database $instance = null;

    /** @var PDO Objeto de conexión PDO */
    private PDO $pdo;

    /**
     * Constructor privado: establece y configura la conexión PDO.
     * @throws PDOException Si la conexión a MySQL falla.
     */
    private function __construct()
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            self::DB_HOST,
            self::DB_PORT,
            self::DB_NAME,
            self::DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // Lanza excepciones en errores SQL
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,          // Resultados como arrays asociativos
            PDO::ATTR_EMULATE_PREPARES   => false,                      // Prepared statements reales
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ];

        $this->pdo = new PDO($dsn, self::DB_USER, self::DB_PASS, $options);
    }

    /**
     * Obtiene la instancia única del Singleton.
     * @return Database
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Retorna el objeto PDO listo para ejecutar consultas.
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /** Bloquea la clonación (patrón Singleton) */
    private function __clone() {}

    /** Bloquea la deserialización (patrón Singleton) */
    public function __wakeup(): void
    {
        throw new \Exception("No se puede deserializar un Singleton.");
    }
}
