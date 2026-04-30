<?php

namespace App\Config;

use \RuntimeException;

/**
 * Excepción personalizada para fallos de conexión a la base de datos.
 * El Front Controller la captura para mostrar la página de error 500
 * sin exponer trazas internas de PHP al usuario final.
 */
class DatabaseConnectionException extends RuntimeException {}
