-- ============================================================
--  CLINI-K — Migración: Tablas de Autenticación
--  Archivo: database/migrations/create_auth_tables.sql
--  Ejecutar en: clinik_bd (después del dump principal)
-- ============================================================

USE `clinik_bd`;

-- ── 1. Tabla de Roles ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `roles` (
  `id_rol`     INT          NOT NULL,
  `nombre_rol` VARCHAR(50)  NOT NULL,
  PRIMARY KEY (`id_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `roles` (`id_rol`, `nombre_rol`) VALUES
(1, 'Paciente'),
(2, 'Doctor'),
(3, 'Secretaria');

-- ── 2. Tabla de Usuarios ─────────────────────────────────────────────────
-- id_referencia apunta al id_doctor o id_paciente según el rol.
-- Para la Secretaria, id_referencia = NULL.
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id_usuario`           INT          NOT NULL AUTO_INCREMENT,
  `nombre`               VARCHAR(100) NOT NULL,
  `apellido`             VARCHAR(100) NOT NULL,
  `correo`               VARCHAR(100)          DEFAULT NULL,
  `numero_identificacion` VARCHAR(15)          DEFAULT NULL,
  `contrasena_hash`      VARCHAR(255) NOT NULL,
  `id_rol`               INT          NOT NULL,
  `id_referencia`        INT                   DEFAULT NULL COMMENT 'FK a doctor.id_doctor o paciente.id_paciente según rol',
  `id_estado`            INT                   DEFAULT 8,
  `fecha_creacion`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `uk_correo`  (`correo`),
  UNIQUE KEY `uk_nid`     (`numero_identificacion`),
  KEY `fk_usuarios_rol`   (`id_rol`),
  KEY `fk_usuarios_estado`(`id_estado`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_rol`)    REFERENCES `roles`  (`id_rol`),
  CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`id_estado`) REFERENCES `estado` (`id_estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── NOTA ─────────────────────────────────────────────────────────────────
-- Los passwords se insertan con el seeder PHP (database/seeders/seed_usuarios.php)
-- para garantizar hashes bcrypt reales generados por PHP.
-- NO insertes contraseñas en texto plano en este archivo SQL.
-- ─────────────────────────────────────────────────────────────────────────
