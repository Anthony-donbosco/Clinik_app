-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 02, 2026 at 09:35 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `clinik_bd`
--

-- --------------------------------------------------------

--
-- Table structure for table `cita`
--

CREATE TABLE `cita` (
  `id_cita` int(11) NOT NULL,
  `id_doctor` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `id_estado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cita`
--

INSERT INTO `cita` (`id_cita`, `id_doctor`, `id_paciente`, `fecha`, `hora`, `id_estado`) VALUES
(1, 1, 1, '2026-05-10', '08:00:00', 4),
(2, 1, 2, '2026-05-10', '08:30:00', 4),
(3, 2, 3, '2026-05-10', '09:00:00', 4),
(4, 2, 4, '2026-05-10', '09:30:00', 4),
(5, 3, 5, '2026-05-10', '10:00:00', 4),
(6, 3, 6, '2026-05-10', '10:30:00', 4),
(7, 4, 7, '2026-05-11', '08:00:00', 4),
(8, 4, 8, '2026-05-11', '08:30:00', 4),
(9, 5, 9, '2026-05-11', '09:00:00', 4),
(10, 5, 10, '2026-05-11', '09:30:00', 4),
(11, 6, 11, '2026-05-11', '10:00:00', 4),
(12, 6, 12, '2026-05-11', '10:30:00', 4),
(13, 7, 13, '2026-05-12', '08:00:00', 4),
(14, 8, 14, '2026-05-12', '08:30:00', 4),
(15, 9, 15, '2026-05-12', '09:00:00', 4),
(16, 10, 16, '2026-05-12', '09:30:00', 4),
(17, 11, 17, '2026-05-13', '10:00:00', 4),
(18, 12, 18, '2026-05-13', '10:30:00', 4),
(19, 13, 19, '2026-05-13', '11:00:00', 4),
(20, 14, 20, '2026-05-13', '11:30:00', 4),
(21, 10, 1, '2026-05-06', '10:00:00', 4),
(22, 14, 2, '2026-04-30', '09:30:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `diagnostico`
--

CREATE TABLE `diagnostico` (
  `id_diagnostico` int(11) NOT NULL,
  `id_historial` int(11) NOT NULL,
  `descripcion` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diagnostico`
--

INSERT INTO `diagnostico` (`id_diagnostico`, `id_historial`, `descripcion`) VALUES
(1, 1, 'Faringitis aguda con inflamación visible en amígdalas.'),
(2, 2, 'Cuadro de estrés crónico y leve hipertensión arterial.'),
(3, 3, 'Otitis media en oído derecho, fiebre leve de 38°C.'),
(4, 4, 'Dermatitis atópica severa en extremidades superiores.'),
(5, 5, 'Infección gastrointestinal por posible bacteria.'),
(6, 6, 'Migraña tensional crónica con fotofobia.'),
(7, 7, 'Candidiasis leve sin complicaciones graves.'),
(8, 8, 'Embarazo de 8 semanas en desarrollo normal.'),
(9, 9, 'Arritmia cardíaca leve, requiere monitoreo.'),
(10, 10, 'Soplo cardíaco grado 2 detectado en revisión de rutina.'),
(11, 11, 'Deficiencia de vitamina D y principios de anemia.'),
(12, 12, 'Obesidad tipo 1, colesterol alto en exámenes previos.'),
(13, 13, 'Caries profunda en molar inferior derecho.'),
(14, 14, 'Gingivitis leve por acumulación de placa bacteriana.'),
(15, 15, 'Cuadro de ansiedad generalizada moderada.'),
(16, 16, 'Conjuntivitis viral en ojo izquierdo.'),
(17, 17, 'Astigmatismo leve detectado en examen de agudeza visual.'),
(18, 18, 'Resfriado común, congestión nasal severa.'),
(19, 19, 'Reacción alérgica cutánea por contacto.'),
(20, 20, 'Esguince de tobillo grado 1 por contusión física.'),
(21, 21, 'tienes cancer andy');

-- --------------------------------------------------------

--
-- Table structure for table `doctor`
--

CREATE TABLE `doctor` (
  `id_doctor` int(11) NOT NULL,
  `primer_nombre` varchar(50) NOT NULL,
  `segundo_nombre` varchar(50) DEFAULT NULL,
  `primer_apellido` varchar(50) NOT NULL,
  `segundo_apellido` varchar(50) DEFAULT NULL,
  `especialidad` varchar(50) NOT NULL,
  `id_estado` int(11) DEFAULT 8,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctor`
--

INSERT INTO `doctor` (`id_doctor`, `primer_nombre`, `segundo_nombre`, `primer_apellido`, `segundo_apellido`, `especialidad`, `id_estado`, `fecha_creacion`) VALUES
(1, 'Roberto', 'Antonio', 'Salazar', 'Méndez', 'Medicina General', 8, '2026-04-29 06:01:07'),
(2, 'Elena', 'Beatriz', 'Cruz', 'Valle', 'Pediatría', 8, '2026-04-29 06:01:07'),
(3, 'Mauricio', 'Alejandro', 'Rivas', 'Torres', 'Dermatología', 8, '2026-04-29 06:01:07'),
(4, 'Génesis', 'Victoria', 'Mejía', 'Cruz', 'Ginecología', 8, '2026-04-29 06:01:07'),
(5, 'Manuel', 'Ernesto', 'Miranda', 'Rivas', 'Cardiología', 8, '2026-04-29 06:01:07'),
(6, 'Lorena', 'Giselle', 'Serrano', 'Majano', 'Nutrición', 8, '2026-04-29 06:01:07'),
(7, 'Elmer', 'Eduardo', 'Avilés', 'Pérez', 'Odontología', 8, '2026-04-29 06:01:07'),
(8, 'Carlos', 'Daniel', 'Monterrosa', 'Luna', 'Psicología', 8, '2026-04-29 06:01:07'),
(9, 'Diego', 'Andrés', 'Rivera', 'Gómez', 'Oftalmología', 8, '2026-04-29 06:01:07'),
(10, 'Johana', 'Margarita', 'López', 'Díaz', 'Pediatría', 8, '2026-04-29 06:01:07'),
(11, 'Valerie', 'Nicole', 'Pérez', 'Sosa', 'Dermatología', 8, '2026-04-29 06:01:07'),
(12, 'Antonio', 'José', 'Sosa', 'Hernández', 'Medicina General', 8, '2026-04-29 06:01:07'),
(13, 'Alexander', 'David', 'Hernández', 'Ortiz', 'Cardiología', 8, '2026-04-29 06:01:07'),
(14, 'Victoria', 'Isabel', 'García', 'Mares', 'Nutrición', 8, '2026-04-29 06:01:07'),
(15, 'Eduardo', 'Luis', 'Ríos', 'Sol', 'Odontología', 8, '2026-04-29 06:01:07'),
(16, 'Giselle', 'María', 'Torres', 'Paz', 'Psicología', 8, '2026-04-29 06:01:07'),
(17, 'Miranda', 'Sofía', 'Serrano', 'Luz', 'Ginecología', 8, '2026-04-29 06:01:07'),
(18, 'Juan', 'Carlos', 'Rivas', 'Cruz', 'Medicina General', 8, '2026-04-29 06:01:07'),
(19, 'Mario', 'Alberto', 'Mejía', 'Bueno', 'Oftalmología', 8, '2026-04-29 06:01:07'),
(20, 'Luis', 'Fernando', 'Pérez', 'Díaz', 'Pediatría', 8, '2026-04-29 06:01:07');

-- --------------------------------------------------------

--
-- Table structure for table `estado`
--

CREATE TABLE `estado` (
  `id_estado` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `estado`
--

INSERT INTO `estado` (`id_estado`, `nombre`) VALUES
(1, 'Pendiente'),
(2, 'Aprobada'),
(3, 'Rechazada'),
(4, 'Atendida'),
(5, 'Cancelada'),
(6, 'Disponible'),
(7, 'Ocupado'),
(8, 'Activo'),
(9, 'Inactivo');

-- --------------------------------------------------------

--
-- Table structure for table `factura`
--

CREATE TABLE `factura` (
  `id_factura` int(11) NOT NULL,
  `id_cita` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `detalle` varchar(255) NOT NULL,
  `monto` decimal(10,2) DEFAULT 25.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `factura`
--

INSERT INTO `factura` (`id_factura`, `id_cita`, `fecha`, `detalle`, `monto`) VALUES
(1, 1, '2026-05-10', 'Consulta Medicina General', 25.00),
(2, 2, '2026-05-10', 'Consulta Medicina General', 25.00),
(3, 3, '2026-05-10', 'Consulta Especialidad Pediatría', 35.00),
(4, 4, '2026-05-10', 'Consulta Especialidad Pediatría', 35.00),
(5, 5, '2026-05-10', 'Consulta Especialidad Dermatología', 40.00),
(6, 6, '2026-05-10', 'Consulta Especialidad Dermatología', 40.00),
(7, 7, '2026-05-11', 'Consulta Especialidad Ginecología', 45.00),
(8, 8, '2026-05-11', 'Consulta Especialidad Ginecología', 45.00),
(9, 9, '2026-05-11', 'Consulta Especialidad Cardiología', 50.00),
(10, 10, '2026-05-11', 'Consulta Especialidad Cardiología', 50.00),
(11, 11, '2026-05-11', 'Consulta Especialidad Nutrición', 30.00),
(12, 12, '2026-05-11', 'Consulta Especialidad Nutrición', 30.00),
(13, 13, '2026-05-12', 'Consulta Especialidad Odontología (con resina)', 65.00),
(14, 14, '2026-05-12', 'Consulta Especialidad Psicología', 40.00),
(15, 15, '2026-05-12', 'Consulta Especialidad Oftalmología', 35.00),
(16, 16, '2026-05-12', 'Consulta Especialidad Pediatría', 35.00),
(17, 17, '2026-05-13', 'Consulta Especialidad Dermatología', 40.00),
(18, 18, '2026-05-13', 'Consulta Medicina General', 25.00),
(19, 19, '2026-05-13', 'Consulta Especialidad Cardiología', 50.00),
(20, 20, '2026-05-13', 'Consulta Especialidad Nutrición', 30.00);

-- --------------------------------------------------------

--
-- Table structure for table `historial`
--

CREATE TABLE `historial` (
  `id_historial` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `id_cita` int(11) NOT NULL,
  `fecha` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `historial`
--

INSERT INTO `historial` (`id_historial`, `id_paciente`, `id_cita`, `fecha`) VALUES
(1, 1, 1, '2026-05-10'),
(2, 2, 2, '2026-05-10'),
(3, 3, 3, '2026-05-10'),
(4, 4, 4, '2026-05-10'),
(5, 5, 5, '2026-05-10'),
(6, 6, 6, '2026-05-10'),
(7, 7, 7, '2026-05-11'),
(8, 8, 8, '2026-05-11'),
(9, 9, 9, '2026-05-11'),
(10, 10, 10, '2026-05-11'),
(11, 11, 11, '2026-05-11'),
(12, 12, 12, '2026-05-11'),
(13, 13, 13, '2026-05-12'),
(14, 14, 14, '2026-05-12'),
(15, 15, 15, '2026-05-12'),
(16, 16, 16, '2026-05-12'),
(17, 17, 17, '2026-05-13'),
(18, 18, 18, '2026-05-13'),
(19, 19, 19, '2026-05-13'),
(20, 20, 20, '2026-05-13'),
(21, 1, 21, '2026-04-30');

-- --------------------------------------------------------

--
-- Table structure for table `paciente`
--

CREATE TABLE `paciente` (
  `id_paciente` int(11) NOT NULL,
  `primer_nombre` varchar(50) NOT NULL,
  `segundo_nombre` varchar(50) DEFAULT NULL,
  `primer_apellido` varchar(50) NOT NULL,
  `segundo_apellido` varchar(50) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `fecha_nacimiento` date NOT NULL,
  `numeroIdentificacion` varchar(15) NOT NULL,
  `id_estado` int(11) DEFAULT 8,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `paciente`
--

INSERT INTO `paciente` (`id_paciente`, `primer_nombre`, `segundo_nombre`, `primer_apellido`, `segundo_apellido`, `telefono`, `fecha_nacimiento`, `numeroIdentificacion`, `id_estado`, `fecha_creacion`) VALUES
(1, 'Juan', 'Pablo', 'López', 'García', '7000-0001', '1990-01-01', '00000001-1', 8, '2026-04-29 06:01:07'),
(2, 'María', 'José', 'Gómez', 'Martínez', '7000-0002', '1985-05-12', '00000002-2', 8, '2026-04-29 06:01:07'),
(3, 'Pedro', 'Antonio', 'Díaz', 'Hernández', '7000-0003', '2010-03-20', '00000003-3', 8, '2026-04-29 06:01:07'),
(4, 'Lucía', 'Fernanda', 'Ramos', 'Flores', '7000-0004', '1995-07-15', '00000004-4', 8, '2026-04-29 06:01:07'),
(5, 'Jorge', 'Alberto', 'Ortiz', 'Pérez', '7000-0005', '1982-11-30', '00000005-5', 8, '2026-04-29 06:01:07'),
(6, 'Ana', 'Cristina', 'Vega', 'Gómez', '7000-0006', '2000-02-14', '00000006-6', 8, '2026-04-29 06:01:07'),
(7, 'Luis', 'Enrique', 'Soto', 'Díaz', '7000-0007', '1975-09-05', '00000007-7', 8, '2026-04-29 06:01:07'),
(8, 'Elena', 'Margarita', 'Mares', 'Ríos', '7000-0008', '1998-12-25', '00000008-8', 8, '2026-04-29 06:01:07'),
(9, 'Pablo', 'Andrés', 'Luna', 'Cruz', '7000-0009', '1992-06-18', '00000009-9', 8, '2026-04-29 06:01:07'),
(10, 'Carmen', 'Alicia', 'Sol', 'Méndez', '7000-0010', '1988-04-22', '00000010-0', 8, '2026-04-29 06:01:07'),
(11, 'Rosa', 'María', 'Melo', 'Valle', '7000-0011', '2015-08-10', '00000011-1', 8, '2026-04-29 06:01:07'),
(12, 'Saúl', 'Ernesto', 'Bueno', 'Torres', '7000-0012', '1980-03-03', '00000012-2', 8, '2026-04-29 06:01:07'),
(13, 'Tania', 'Beatriz', 'Paz', 'Serrano', '7000-0013', '1994-10-10', '00000013-3', 8, '2026-04-29 06:01:07'),
(14, 'Víctor', 'Manuel', 'Hugo', 'Avilés', '7000-0014', '1970-01-20', '00000014-4', 8, '2026-04-29 06:01:07'),
(15, 'Xenia', 'Carolina', 'Luz', 'Monterrosa', '7000-0015', '2002-05-05', '00000015-5', 8, '2026-04-29 06:01:07'),
(16, 'Yuri', 'Alejandro', 'Ríos', 'Rivera', '7000-0016', '1987-09-09', '00000016-6', 8, '2026-04-29 06:01:07'),
(17, 'Zaira', 'Valentina', 'Díaz', 'López', '7000-0017', '1999-11-11', '00000017-7', 8, '2026-04-29 06:01:07'),
(18, 'Ángel', 'Gabriel', 'Cruz', 'Pérez', '7000-0018', '1991-02-02', '00000018-8', 8, '2026-04-29 06:01:07'),
(19, 'Berta', 'Luz', 'Sosa', 'Sosa', '7000-0019', '1984-06-06', '00000019-9', 8, '2026-04-29 06:01:07'),
(20, 'César', 'Augusto', 'Polo', 'Hernández', '7000-0020', '1978-08-08', '00000020-0', 8, '2026-04-29 06:01:07');

-- --------------------------------------------------------

--
-- Table structure for table `receta`
--

CREATE TABLE `receta` (
  `id_receta` int(11) NOT NULL,
  `id_historial` int(11) NOT NULL,
  `indicaciones` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `receta`
--

INSERT INTO `receta` (`id_receta`, `id_historial`, `indicaciones`) VALUES
(1, 1, 'Amoxicilina 500mg cada 8 horas por 7 días. Ibuprofeno 400mg.'),
(2, 2, 'Enalapril 10mg diario por las mañanas.'),
(3, 3, 'Gotas óticas (Ciprofloxacino) 3 gotas cada 12 horas.'),
(4, 4, 'Crema con hidrocortisona al 1% aplicar capa fina 2 veces al día.'),
(5, 5, 'Ciprofloxacina 500mg cada 12 horas por 5 días.'),
(6, 6, 'Ibuprofeno 600mg al inicio del dolor. No exceder 3 al día.'),
(7, 7, 'Fluconazol 150mg dosis única oral.'),
(8, 8, 'Ácido fólico 5mg diario y vitaminas prenatales.'),
(9, 9, 'Amiodarona 200mg diaria según evaluación de electro.'),
(10, 10, 'Aspirina 81mg diaria preventiva.'),
(11, 11, 'Suplemento de Vitamina D3 4000 UI diarias por 30 días.'),
(12, 12, 'Atorvastatina 20mg diaria por las noches.'),
(13, 13, 'Acetaminofén 500mg cada 6 horas en caso de dolor post-resina.'),
(14, 14, 'Enjuague bucal con clorhexidina 0.12% 2 veces al día por 1 semana.'),
(15, 15, 'Sertralina 50mg diaria por la mañana. Inicio gradual.'),
(16, 16, 'Lágrimas artificiales cada 4 horas y antibiótico tópico ocular.'),
(17, 17, 'Ninguna receta farmacológica. Solo orden de lentes.'),
(18, 18, 'Loratadina 10mg diaria por 5 días.'),
(19, 19, 'Desloratadina 5mg diaria por 7 días.'),
(20, 20, 'Diclofenaco gel al 1% aplicar en zona afectada 3 veces al día.'),
(21, 21, 'yiyis');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre_rol`) VALUES
(1, 'Paciente'),
(2, 'Doctor'),
(3, 'Secretaria'),
(4, 'Admin');

-- --------------------------------------------------------

--
-- Table structure for table `tratamiento`
--

CREATE TABLE `tratamiento` (
  `id_tratamiento` int(11) NOT NULL,
  `id_historial` int(11) NOT NULL,
  `descripcion` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tratamiento`
--

INSERT INTO `tratamiento` (`id_tratamiento`, `id_historial`, `descripcion`) VALUES
(1, 1, 'Reposo absoluto por 3 días. Hidratación constante.'),
(2, 2, 'Derivación a psicología clínica. Monitoreo de presión.'),
(3, 3, 'Limpieza de oído en clínica. Compresas tibias en casa.'),
(4, 4, 'Evitar exposición al sol. Uso de ropa de algodón 100%.'),
(5, 5, 'Dieta blanda por 5 días. Consumo de suero oral.'),
(6, 6, 'Descanso en habitación oscura. Reducción de tiempo en pantallas.'),
(7, 7, 'Higiene estricta en la zona afectada. Evitar humedad.'),
(8, 8, 'Controles prenatales mensuales. Suplementación vitamínica.'),
(9, 9, 'Evitar consumo de cafeína y bebidas energéticas.'),
(10, 10, 'Realización de electrocardiograma programado para próxima semana.'),
(11, 11, 'Exposición solar diaria de 15 minutos. Mejora en dieta.'),
(12, 12, 'Plan nutricional estricto con déficit calórico.'),
(13, 13, 'Extracción de tejido cariado y colocación de resina.'),
(14, 14, 'Limpieza dental profunda y técnica de cepillado corregida.'),
(15, 15, 'Terapias cognitivo-conductuales semanales por 2 meses.'),
(16, 16, 'Aislamiento preventivo por 4 días. Limpieza ocular.'),
(17, 17, 'Uso de lentes con prescripción permanente para lectura.'),
(18, 18, 'Inhalaciones de vapor y reposo por 48 horas.'),
(19, 19, 'Evitar contacto con alérgenos identificados.'),
(20, 20, 'Aplicación de hielo local y uso de vendaje compresivo.'),
(21, 21, 'no mas');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `numero_identificacion` varchar(15) DEFAULT NULL,
  `contrasena_hash` varchar(255) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `id_referencia` int(11) DEFAULT NULL COMMENT 'FK a doctor.id_doctor o paciente.id_paciente según rol',
  `id_estado` int(11) DEFAULT 8,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido`, `correo`, `numero_identificacion`, `contrasena_hash`, `id_rol`, `id_referencia`, `id_estado`, `fecha_creacion`) VALUES
(1, 'Ana', 'González', 'secretaria@clinik.com', 'SEC-001', '$2y$12$g5IuRpEX6ajS3.6f4t6FieVbblDfvm8XvvF/Zz7MUl9qsG358rpri', 3, NULL, 8, '2026-04-29 07:22:31'),
(2, 'Roberto', 'Salazar', 'rsalazar@clinik.com', 'DOC-001', '$2y$12$g5IuRpEX6ajS3.6f4t6FieVbblDfvm8XvvF/Zz7MUl9qsG358rpri', 2, 1, 8, '2026-04-29 07:22:31'),
(3, 'Elena', 'Cruz', 'ecruz@clinik.com', 'DOC-002', '$2y$12$g5IuRpEX6ajS3.6f4t6FieVbblDfvm8XvvF/Zz7MUl9qsG358rpri', 2, 2, 8, '2026-04-29 07:22:31'),
(4, 'Mauricio', 'Rivas', 'mrivas@clinik.com', 'DOC-003', '$2y$12$g5IuRpEX6ajS3.6f4t6FieVbblDfvm8XvvF/Zz7MUl9qsG358rpri', 2, 3, 8, '2026-04-29 07:22:31'),
(5, 'Génesis', 'Mejía', 'gmejia@clinik.com', 'DOC-004', '$2y$12$g5IuRpEX6ajS3.6f4t6FieVbblDfvm8XvvF/Zz7MUl9qsG358rpri', 2, 4, 8, '2026-04-29 07:22:31'),
(6, 'Manuel', 'Miranda', 'mmiranda@clinik.com', 'DOC-005', '$2y$12$g5IuRpEX6ajS3.6f4t6FieVbblDfvm8XvvF/Zz7MUl9qsG358rpri', 2, 5, 8, '2026-04-29 07:22:31'),
(7, 'Juan', 'López', 'jlopez@mail.com', '00000001-1', '$2y$12$g5IuRpEX6ajS3.6f4t6FieVbblDfvm8XvvF/Zz7MUl9qsG358rpri', 1, 1, 8, '2026-04-29 07:22:31'),
(8, 'María', 'Gómez', 'mgomez@mail.com', '00000002-2', '$2y$12$g5IuRpEX6ajS3.6f4t6FieVbblDfvm8XvvF/Zz7MUl9qsG358rpri', 1, 2, 8, '2026-04-29 07:22:31'),
(9, 'Pedro', 'Díaz', 'pdiaz@mail.com', '00000003-3', '$2y$12$g5IuRpEX6ajS3.6f4t6FieVbblDfvm8XvvF/Zz7MUl9qsG358rpri', 1, 3, 8, '2026-04-29 07:22:31'),
(10, 'Lucía', 'Ramos', 'lramos@mail.com', '00000004-4', '$2y$12$g5IuRpEX6ajS3.6f4t6FieVbblDfvm8XvvF/Zz7MUl9qsG358rpri', 1, 4, 8, '2026-04-29 07:22:31'),
(11, 'Jorge', 'Ortiz', 'jortiz@mail.com', '00000005-5', '$2y$12$g5IuRpEX6ajS3.6f4t6FieVbblDfvm8XvvF/Zz7MUl9qsG358rpri', 1, 5, 8, '2026-04-29 07:22:31'),
(12, 'Super', 'Admin', 'admin@clinik.com', 'ADMIN-001', '$2y$12$U7Ac/LZVnOgH8uBVaswGXePsG/iMAmkxOciuYRCFlKlhSxPKr402i', 4, NULL, 8, '2026-04-30 01:00:11'),
(13, 'Johana', 'López', 'jlopez.doc@clinik.com', 'DOC-010', '$2y$12$g5IuRpEX6ajS3.6f4t6FieVbblDfvm8XvvF/Zz7MUl9qsG358rpri', 2, 10, 8, '2026-04-30 06:20:48');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cita`
--
ALTER TABLE `cita`
  ADD PRIMARY KEY (`id_cita`),
  ADD UNIQUE KEY `id_doctor` (`id_doctor`,`fecha`,`hora`),
  ADD KEY `id_paciente` (`id_paciente`),
  ADD KEY `id_estado` (`id_estado`);

--
-- Indexes for table `diagnostico`
--
ALTER TABLE `diagnostico`
  ADD PRIMARY KEY (`id_diagnostico`),
  ADD KEY `id_historial` (`id_historial`);

--
-- Indexes for table `doctor`
--
ALTER TABLE `doctor`
  ADD PRIMARY KEY (`id_doctor`),
  ADD KEY `id_estado` (`id_estado`);

--
-- Indexes for table `estado`
--
ALTER TABLE `estado`
  ADD PRIMARY KEY (`id_estado`);

--
-- Indexes for table `factura`
--
ALTER TABLE `factura`
  ADD PRIMARY KEY (`id_factura`),
  ADD KEY `id_cita` (`id_cita`);

--
-- Indexes for table `historial`
--
ALTER TABLE `historial`
  ADD PRIMARY KEY (`id_historial`),
  ADD UNIQUE KEY `id_cita` (`id_cita`),
  ADD KEY `id_paciente` (`id_paciente`);

--
-- Indexes for table `paciente`
--
ALTER TABLE `paciente`
  ADD PRIMARY KEY (`id_paciente`),
  ADD UNIQUE KEY `numeroIdentificacion` (`numeroIdentificacion`),
  ADD KEY `id_estado` (`id_estado`);

--
-- Indexes for table `receta`
--
ALTER TABLE `receta`
  ADD PRIMARY KEY (`id_receta`),
  ADD KEY `id_historial` (`id_historial`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indexes for table `tratamiento`
--
ALTER TABLE `tratamiento`
  ADD PRIMARY KEY (`id_tratamiento`),
  ADD KEY `id_historial` (`id_historial`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `uk_correo` (`correo`),
  ADD UNIQUE KEY `uk_nid` (`numero_identificacion`),
  ADD KEY `fk_usuarios_rol` (`id_rol`),
  ADD KEY `fk_usuarios_estado` (`id_estado`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cita`
--
ALTER TABLE `cita`
  MODIFY `id_cita` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `diagnostico`
--
ALTER TABLE `diagnostico`
  MODIFY `id_diagnostico` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `doctor`
--
ALTER TABLE `doctor`
  MODIFY `id_doctor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `factura`
--
ALTER TABLE `factura`
  MODIFY `id_factura` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `historial`
--
ALTER TABLE `historial`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `paciente`
--
ALTER TABLE `paciente`
  MODIFY `id_paciente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `receta`
--
ALTER TABLE `receta`
  MODIFY `id_receta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `tratamiento`
--
ALTER TABLE `tratamiento`
  MODIFY `id_tratamiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cita`
--
ALTER TABLE `cita`
  ADD CONSTRAINT `cita_ibfk_1` FOREIGN KEY (`id_doctor`) REFERENCES `doctor` (`id_doctor`),
  ADD CONSTRAINT `cita_ibfk_2` FOREIGN KEY (`id_paciente`) REFERENCES `paciente` (`id_paciente`),
  ADD CONSTRAINT `cita_ibfk_3` FOREIGN KEY (`id_estado`) REFERENCES `estado` (`id_estado`);

--
-- Constraints for table `diagnostico`
--
ALTER TABLE `diagnostico`
  ADD CONSTRAINT `diagnostico_ibfk_1` FOREIGN KEY (`id_historial`) REFERENCES `historial` (`id_historial`);

--
-- Constraints for table `doctor`
--
ALTER TABLE `doctor`
  ADD CONSTRAINT `doctor_ibfk_1` FOREIGN KEY (`id_estado`) REFERENCES `estado` (`id_estado`);

--
-- Constraints for table `factura`
--
ALTER TABLE `factura`
  ADD CONSTRAINT `factura_ibfk_1` FOREIGN KEY (`id_cita`) REFERENCES `cita` (`id_cita`);

--
-- Constraints for table `historial`
--
ALTER TABLE `historial`
  ADD CONSTRAINT `historial_ibfk_1` FOREIGN KEY (`id_paciente`) REFERENCES `paciente` (`id_paciente`),
  ADD CONSTRAINT `historial_ibfk_2` FOREIGN KEY (`id_cita`) REFERENCES `cita` (`id_cita`);

--
-- Constraints for table `paciente`
--
ALTER TABLE `paciente`
  ADD CONSTRAINT `paciente_ibfk_1` FOREIGN KEY (`id_estado`) REFERENCES `estado` (`id_estado`);

--
-- Constraints for table `receta`
--
ALTER TABLE `receta`
  ADD CONSTRAINT `receta_ibfk_1` FOREIGN KEY (`id_historial`) REFERENCES `historial` (`id_historial`);

--
-- Constraints for table `tratamiento`
--
ALTER TABLE `tratamiento`
  ADD CONSTRAINT `tratamiento_ibfk_1` FOREIGN KEY (`id_historial`) REFERENCES `historial` (`id_historial`);

--
-- Constraints for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`),
  ADD CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`id_estado`) REFERENCES `estado` (`id_estado`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
