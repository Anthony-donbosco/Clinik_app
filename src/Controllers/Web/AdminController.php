<?php

namespace App\Controllers\Web;

use App\Repositories\UsuarioRepository;

/**
 * AdminController — Panel de administración del sistema.
 * Solo accesible para id_rol = 4 (Admin).
 *
 * GET  /admin                    → index()         — Dashboard con lista de usuarios
 * GET  /admin/crear-usuario      → showCrear()     — Formulario crear usuario (doctor/secretaria/admin)
 * POST /admin/crear-usuario      → procesarCrear() — Guarda el nuevo usuario
 * GET  /admin/editar-doctor      → showEditar()    — Formulario editar doctor existente
 * POST /admin/editar-doctor      → procesarEditar()— Guarda cambios del doctor
 * POST /admin/desactivar         → desactivar()    — Desactiva un usuario (soft delete)
 * POST /admin/reactivar          → reactivar()     — Reactiva un usuario
 */
class AdminController extends BaseController
{
    private UsuarioRepository $repo;

    public function __construct()
    {
        $this->repo = new UsuarioRepository();
    }

    private function bp(): string
    {
        return defined('BASE_PATH') ? BASE_PATH : '';
    }

    // ── GET /admin ─────────────────────────────────────────────────────────────
    public function index(): void
    {
        $this->requireRole(4);

        $usuarios          = $this->repo->getAllUsuarios();
        $doctoresFantasma  = $this->repo->getDoctoresFantasma();
        $flashOk           = $_SESSION['flash_ok']    ?? null;
        $flashError        = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_ok'], $_SESSION['flash_error']);

        $bp = $this->bp();
        $this->render('admin/dashboard', compact('usuarios', 'doctoresFantasma', 'flashOk', 'flashError', 'bp'));
    }

    // ── GET /admin/crear-usuario ───────────────────────────────────────────────
    public function showCrear(): void
    {
        $this->requireRole(4);

        $flashError  = $_SESSION['flash_error'] ?? null;
        $old         = $_SESSION['admin_old']   ?? [];
        unset($_SESSION['flash_error'], $_SESSION['admin_old']);

        // Si viene ?ghost=ID → pre-llenar con datos del doctor fantasma existente
        $ghostDoctor = null;
        $ghostId     = (int) ($_GET['ghost'] ?? 0);
        if ($ghostId > 0) {
            $ghostDoctor = $this->repo->getDoctorById($ghostId);
        }

        $especialidades = $this->getEspecialidades();
        $bp = $this->bp();
        $this->render('admin/crear_usuario', compact('flashError', 'old', 'bp', 'especialidades', 'ghostDoctor'));
    }


    // ── POST /admin/crear-usuario ──────────────────────────────────────────────
    public function procesarCrear(): void
    {
        $this->requireRole(4);
        $bp = $this->bp();

        $tipoRol = (int) ($_POST['tipo_rol'] ?? 0);

        if ($tipoRol === 2) {
            // ── CREAR DOCTOR (completo O solo credenciales si es un fantasma) ──
            $ghostIdDoctor = (int) ($_POST['ghost_id_doctor'] ?? 0);

            $pNombre   = trim($_POST['primer_nombre']    ?? '');
            $sNombre   = trim($_POST['segundo_nombre']   ?? '');
            $pApellido = trim($_POST['primer_apellido']  ?? '');
            $sApellido = trim($_POST['segundo_apellido'] ?? '');
            $esp       = trim($_POST['especialidad']     ?? '');
            $correo    = trim(strtolower($_POST['correo'] ?? ''));
            $nid       = trim($_POST['numero_identificacion'] ?? '');
            $pass      = $_POST['contrasena'] ?? '';

            // Guardar en sesión para repoblar en caso de error
            $_SESSION['admin_old'] = [
                'tipoRol'         => $tipoRol,
                'pNombre'         => $pNombre,   'sNombre'    => $sNombre,
                'pApellido'       => $pApellido, 'sApellido'  => $sApellido,
                'esp'             => $esp,       'correo'     => $correo,
                'nid'             => $nid,
                'ghost_id_doctor' => $ghostIdDoctor,
            ];

            // Validaciones comunes
            if ($ghostIdDoctor > 0) {
                // Para fantasma: nombre/apellido/esp vienen del doctor existente → solo validar credenciales
                if (empty($correo) || empty($nid) || empty($pass)) {
                    $_SESSION['flash_error'] = 'El correo, la cédula y la contraseña son obligatorios.';
                    header('Location: ' . $bp . '/admin/crear-usuario?ghost=' . $ghostIdDoctor);
                    exit;
                }
            } else {
                // Doctor nuevo completo
                if (empty($pNombre) || empty($pApellido) || empty($esp) || empty($correo) || empty($nid) || empty($pass)) {
                    $_SESSION['flash_error'] = 'Todos los campos marcados (*) son obligatorios.';
                    header('Location: ' . $bp . '/admin/crear-usuario');
                    exit;
                }
            }

            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $redir = $ghostIdDoctor > 0 ? "/admin/crear-usuario?ghost={$ghostIdDoctor}" : '/admin/crear-usuario';
                $_SESSION['flash_error'] = 'El correo no tiene un formato válido.';
                header('Location: ' . $bp . $redir);
                exit;
            }
            if (mb_strlen($pass) < 8) {
                $redir = $ghostIdDoctor > 0 ? "/admin/crear-usuario?ghost={$ghostIdDoctor}" : '/admin/crear-usuario';
                $_SESSION['flash_error'] = 'La contraseña debe tener al menos 8 caracteres.';
                header('Location: ' . $bp . $redir);
                exit;
            }
            if ($this->repo->existeCorreo($correo)) {
                $redir = $ghostIdDoctor > 0 ? "/admin/crear-usuario?ghost={$ghostIdDoctor}" : '/admin/crear-usuario';
                $_SESSION['flash_error'] = 'El correo ya está registrado en el sistema.';
                header('Location: ' . $bp . $redir);
                exit;
            }
            if ($this->repo->existeIdentificacion($nid)) {
                $redir = $ghostIdDoctor > 0 ? "/admin/crear-usuario?ghost={$ghostIdDoctor}" : '/admin/crear-usuario';
                $_SESSION['flash_error'] = 'La cédula/ID ya está registrada en el sistema.';
                header('Location: ' . $bp . $redir);
                exit;
            }

            try {
                if ($ghostIdDoctor > 0) {
                    // Caso fantasma: doctor ya existe en DB, solo crear credenciales
                    $ghost       = $this->repo->getDoctorById($ghostIdDoctor);
                    $nombreDoc   = trim(($ghost['primer_nombre'] ?? '') . ' ' . ($ghost['segundo_nombre'] ?? ''));
                    $apellidoDoc = trim(($ghost['primer_apellido'] ?? '') . ' ' . ($ghost['segundo_apellido'] ?? ''));

                    $this->repo->crearCredencialesParaDoctor($ghostIdDoctor, [
                        'nombre'               => $nombreDoc,
                        'apellido'             => $apellidoDoc,
                        'correo'               => $correo,
                        'numero_identificacion' => $nid,
                        'contrasena'           => $pass,
                    ]);

                    unset($_SESSION['admin_old']);
                    $_SESSION['flash_ok'] = "Acceso creado para el Dr. '{$nombreDoc} {$apellidoDoc}'. Ya puede iniciar sesión.";
                } else {
                    // Caso nuevo: crear perfil médico + credenciales en transacción atómica
                    $this->repo->crearDoctorCompleto([
                        'primer_nombre'        => $pNombre,
                        'segundo_nombre'       => $sNombre ?: null,
                        'primer_apellido'      => $pApellido,
                        'segundo_apellido'     => $sApellido ?: null,
                        'especialidad'         => $esp,
                        'correo'               => $correo,
                        'numero_identificacion' => $nid,
                        'contrasena'           => $pass,
                    ]);

                    unset($_SESSION['admin_old']);
                    $_SESSION['flash_ok'] = "Doctor '{$pNombre} {$pApellido}' creado correctamente con acceso al sistema.";
                }

                header('Location: ' . $bp . '/admin');
                exit;

            } catch (\RuntimeException $e) {
                $redir = $ghostIdDoctor > 0 ? "/admin/crear-usuario?ghost={$ghostIdDoctor}" : '/admin/crear-usuario';
                $_SESSION['flash_error'] = $e->getMessage();
                header('Location: ' . $bp . $redir);
                exit;
            }

        } else {
            // ── CREAR SECRETARIA (3) o ADMIN (4) ──
            $nombre   = trim($_POST['nombre']   ?? '');
            $apellido = trim($_POST['apellido']  ?? '');
            $correo   = trim(strtolower($_POST['correo'] ?? ''));
            $nid      = trim($_POST['numero_identificacion'] ?? '');
            $pass     = $_POST['contrasena'] ?? '';

            $_SESSION['admin_old'] = compact('nombre', 'apellido', 'correo', 'nid', 'tipoRol');

            if (empty($nombre) || empty($apellido) || empty($correo) || empty($nid) || empty($pass)) {
                $_SESSION['flash_error'] = 'Todos los campos son obligatorios.';
                header('Location: ' . $bp . '/admin/crear-usuario');
                exit;
            }
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['flash_error'] = 'El correo no tiene un formato válido.';
                header('Location: ' . $bp . '/admin/crear-usuario');
                exit;
            }
            if (mb_strlen($pass) < 8) {
                $_SESSION['flash_error'] = 'La contraseña debe tener al menos 8 caracteres.';
                header('Location: ' . $bp . '/admin/crear-usuario');
                exit;
            }
            if ($this->repo->existeCorreo($correo)) {
                $_SESSION['flash_error'] = 'El correo ya está registrado en el sistema.';
                header('Location: ' . $bp . '/admin/crear-usuario');
                exit;
            }
            if ($this->repo->existeIdentificacion($nid)) {
                $_SESSION['flash_error'] = 'La cédula/ID ya está registrada.';
                header('Location: ' . $bp . '/admin/crear-usuario');
                exit;
            }

            if (!in_array($tipoRol, [3, 4], true)) {
                $_SESSION['flash_error'] = 'Tipo de rol no válido.';
                header('Location: ' . $bp . '/admin/crear-usuario');
                exit;
            }

            try {
                $this->repo->crearUsuarioStaff(
                    compact('nombre', 'apellido', 'correo', 'nid') + ['contrasena' => $pass],
                    $tipoRol
                );
                $rolNombre = $tipoRol === 3 ? 'Secretaria' : 'Admin';
                unset($_SESSION['admin_old']);
                $_SESSION['flash_ok'] = "Usuario {$rolNombre} '{$nombre} {$apellido}' creado correctamente.";
                header('Location: ' . $bp . '/admin');
                exit;

            } catch (\RuntimeException $e) {
                $_SESSION['flash_error'] = $e->getMessage();
                header('Location: ' . $bp . '/admin/crear-usuario');
                exit;
            }
        }
    }

    // ── GET /admin/editar-doctor ───────────────────────────────────────────────
    public function showEditar(): void
    {
        $this->requireRole(4);
        $bp = $this->bp();

        $idDoctor = (int) ($_GET['id'] ?? 0);
        if ($idDoctor <= 0) {
            header('Location: ' . $bp . '/admin');
            exit;
        }

        $doctor     = $this->repo->getDoctorById($idDoctor);
        $flashError = $_SESSION['flash_error'] ?? null;
        $old        = $_SESSION['admin_old']   ?? [];
        unset($_SESSION['flash_error'], $_SESSION['admin_old']);

        if (!$doctor) {
            $_SESSION['flash_error'] = 'Doctor no encontrado.';
            header('Location: ' . $bp . '/admin');
            exit;
        }

        $especialidades = $this->getEspecialidades();
        $this->render('admin/editar_doctor', compact('doctor', 'flashError', 'old', 'bp', 'especialidades'));
    }

    // ── POST /admin/editar-doctor ──────────────────────────────────────────────
    public function procesarEditar(): void
    {
        $this->requireRole(4);
        $bp = $this->bp();

        $idDoctor  = (int) ($_POST['id_doctor']  ?? 0);
        $idUsuario = (int) ($_POST['id_usuario'] ?? 0);

        $pNombre   = trim($_POST['primer_nombre']    ?? '');
        $sNombre   = trim($_POST['segundo_nombre']   ?? '');
        $pApellido = trim($_POST['primer_apellido']  ?? '');
        $sApellido = trim($_POST['segundo_apellido'] ?? '');
        $esp       = trim($_POST['especialidad']     ?? '');
        $correo    = trim(strtolower($_POST['correo'] ?? ''));
        $nid       = trim($_POST['numero_identificacion'] ?? '');

        $_SESSION['admin_old'] = compact('pNombre', 'sNombre', 'pApellido', 'sApellido', 'esp', 'correo', 'nid');

        if ($idDoctor <= 0 || $idUsuario <= 0) {
            $_SESSION['flash_error'] = 'Datos de referencia inválidos.';
            header('Location: ' . $bp . '/admin');
            exit;
        }

        if (empty($pNombre) || empty($pApellido) || empty($esp) || empty($correo) || empty($nid)) {
            $_SESSION['flash_error'] = 'Todos los campos marcados (*) son obligatorios.';
            header('Location: ' . $bp . '/admin/editar-doctor?id=' . $idDoctor);
            exit;
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'El correo no tiene un formato válido.';
            header('Location: ' . $bp . '/admin/editar-doctor?id=' . $idDoctor);
            exit;
        }

        try {
            $this->repo->editarDoctor($idDoctor, $idUsuario, [
                'primer_nombre'        => $pNombre,
                'segundo_nombre'       => $sNombre ?: null,
                'primer_apellido'      => $pApellido,
                'segundo_apellido'     => $sApellido ?: null,
                'especialidad'         => $esp,
                'correo'               => $correo,
                'numero_identificacion' => $nid,
            ]);

            unset($_SESSION['admin_old']);
            $_SESSION['flash_ok'] = "Doctor '{$pNombre} {$pApellido}' actualizado correctamente.";
            header('Location: ' . $bp . '/admin');
            exit;

        } catch (\RuntimeException $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            header('Location: ' . $bp . '/admin/editar-doctor?id=' . $idDoctor);
            exit;
        }
    }

    // ── POST /admin/desactivar ─────────────────────────────────────────────────
    public function desactivar(): void
    {
        $this->requireRole(4);
        $bp = $this->bp();

        $idUsuario = (int) ($_POST['id_usuario'] ?? 0);

        if ($idUsuario === (int) $_SESSION['id_usuario']) {
            $_SESSION['flash_error'] = 'No puedes desactivar tu propia cuenta.';
            header('Location: ' . $bp . '/admin');
            exit;
        }

        $ok = $this->repo->desactivarUsuario($idUsuario);
        $_SESSION[$ok ? 'flash_ok' : 'flash_error'] = $ok
            ? 'Usuario desactivado correctamente.'
            : 'No se pudo desactivar el usuario.';

        header('Location: ' . $bp . '/admin');
        exit;
    }

    // ── POST /admin/reactivar ──────────────────────────────────────────────────
    public function reactivar(): void
    {
        $this->requireRole(4);
        $bp = $this->bp();

        $idUsuario = (int) ($_POST['id_usuario'] ?? 0);
        $ok = $this->repo->reactivarUsuario($idUsuario);
        $_SESSION[$ok ? 'flash_ok' : 'flash_error'] = $ok
            ? 'Usuario reactivado correctamente.'
            : 'No se pudo reactivar el usuario.';

        header('Location: ' . $bp . '/admin');
        exit;
    }

    // ── GET /admin/editar-usuario ──────────────────────────────────────────────
    /**
     * Formulario de edición para cualquier usuario Staff (Secretaria=3, Admin=4).
     * Los Doctores (rol=2) tienen su propio flujo en showEditar()/procesarEditar().
     */
    public function showEditarStaff(): void
    {
        $this->requireRole(4);
        $bp = $this->bp();

        $idUsuario = (int) ($_GET['id'] ?? 0);
        if ($idUsuario <= 0) {
            header('Location: ' . $bp . '/admin');
            exit;
        }

        $usuario    = $this->repo->getUsuarioById($idUsuario);
        $flashError = $_SESSION['flash_error'] ?? null;
        // Solo restaura $old si pertenece a este usuario específico (evita datos rancios de otra operación)
        $sessionOld = $_SESSION['admin_old'] ?? [];
        $old = (isset($sessionOld['_for_usuario']) && (int)$sessionOld['_for_usuario'] === $idUsuario)
            ? $sessionOld
            : [];
        unset($_SESSION['flash_error'], $_SESSION['admin_old']);

        if (!$usuario) {
            $_SESSION['flash_error'] = 'Usuario no encontrado.';
            header('Location: ' . $bp . '/admin');
            exit;
        }

        // Solo permite editar Secretaria (3) y Admin (4) — doctores usan su propio flujo
        if (!in_array((int)$usuario['id_rol'], [3, 4], true)) {
            $_SESSION['flash_error'] = 'Usa el formulario de edición de Doctor para este usuario.';
            header('Location: ' . $bp . '/admin');
            exit;
        }

        $this->render('admin/editar_usuario', compact('usuario', 'flashError', 'old', 'bp'));
    }

    // ── POST /admin/editar-usuario ─────────────────────────────────────────────
    public function procesarEditarStaff(): void
    {
        $this->requireRole(4);
        $bp = $this->bp();

        $idUsuario = (int) ($_POST['id_usuario'] ?? 0);
        $nombre    = trim($_POST['nombre']   ?? '');
        $apellido  = trim($_POST['apellido']  ?? '');
        $correo    = trim(strtolower($_POST['correo'] ?? ''));
        $nid       = trim($_POST['numero_identificacion'] ?? '');

        // Taggear con el id del usuario para evitar contaminar otras ediciones
        $_SESSION['admin_old'] = compact('nombre', 'apellido', 'correo', 'nid') + ['_for_usuario' => $idUsuario];


        if ($idUsuario <= 0) {
            header('Location: ' . $bp . '/admin');
            exit;
        }

        if (empty($nombre) || empty($apellido) || empty($correo) || empty($nid)) {
            $_SESSION['flash_error'] = 'Todos los campos son obligatorios.';
            header('Location: ' . $bp . '/admin/editar-usuario?id=' . $idUsuario);
            exit;
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'El correo no tiene un formato válido.';
            header('Location: ' . $bp . '/admin/editar-usuario?id=' . $idUsuario);
            exit;
        }

        try {
            $this->repo->editarUsuarioStaff($idUsuario, compact('nombre', 'apellido', 'correo') + ['numero_identificacion' => $nid]);
            unset($_SESSION['admin_old']);
            $_SESSION['flash_ok'] = "Usuario '{$nombre} {$apellido}' actualizado correctamente.";
            header('Location: ' . $bp . '/admin');
            exit;
        } catch (\RuntimeException $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            header('Location: ' . $bp . '/admin/editar-usuario?id=' . $idUsuario);
            exit;
        }
    }

    // ── GET /admin/citas ───────────────────────────────────────────────────────
    /**
     * Vista de supervisión global de citas para el Admin.
     * El Admin NO puede crear citas; solo las visualiza y monitorea.
     */
    public function citas(): void
    {
        $this->requireRole(4);
        $bp = $this->bp();

        // Reutilizamos CitaRepository para obtener todas las citas
        $citaRepo  = new \App\Repositories\CitaRepository();
        $citas     = $citaRepo->getAllCitas(100); // últimas 100
        $flashOk   = $_SESSION['flash_ok']    ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_ok'], $_SESSION['flash_error']);

        $this->render('admin/citas', compact('citas', 'flashOk', 'flashError', 'bp'));
    }

    // ── GET /admin/editar-cita ────────────────────────────────────────────────
    /**
     * Vista para que el Admin edite, reagende, apruebe o cancele una cita específica.
     */
    public function editarCita(): void
    {
        $this->requireRole(4);
        $bp = $this->bp();

        $idCita = (int) ($_GET['id'] ?? 0);
        if ($idCita <= 0) {
            header('Location: ' . $bp . '/admin/citas');
            exit;
        }

        $citaRepo = new \App\Repositories\CitaRepository();
        $cita = $citaRepo->getCitaById($idCita);

        if (!$cita) {
            $_SESSION['flash_error'] = 'Cita no encontrada.';
            header('Location: ' . $bp . '/admin/citas');
            exit;
        }

        $doctores = $citaRepo->getDoctoresParaSelect();

        $this->render('admin/editar_cita', compact('cita', 'doctores', 'bp'));
    }

    // ── Helper: lista de especialidades médicas ────────────────────────────────
    private function getEspecialidades(): array
    {
        return [
            'Medicina General', 'Pediatría', 'Dermatología', 'Ginecología',
            'Cardiología', 'Nutrición', 'Odontología', 'Psicología',
            'Oftalmología', 'Traumatología', 'Neurología', 'Endocrinología',
            'Reumatología', 'Oncología', 'Urología', 'Nefrología',
            'Gastroenterología', 'Pulmonología', 'Infectología', 'Otra',
        ];
    }
}
