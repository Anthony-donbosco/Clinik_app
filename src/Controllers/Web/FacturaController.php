<?php

namespace App\Controllers\Web;

use App\Repositories\FacturaRepository;

/**
 * FacturaController — Módulo de Facturación.
 *
 * GET  /facturas          → index()  — Secretaria ve todas; Paciente ve las suyas
 * POST /facturas/crear    → crear()  — Solo Secretaria puede generar facturas
 */
class FacturaController extends BaseController
{
    private FacturaRepository $repo;

    public function __construct()
    {
        $this->repo = new FacturaRepository();
    }

    // ──────────────────────────────────────────────────────────────────────
    //  GET /facturas
    // ──────────────────────────────────────────────────────────────────────

    public function index(): void
    {
        $this->requireAuth();

        $idRol      = (int) ($_SESSION['id_rol'] ?? 0);
        $bp         = defined('BASE_PATH') ? BASE_PATH : '';
        $flashOk    = $_SESSION['flash_ok']    ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_ok'], $_SESSION['flash_error']);

        $fechaFiltro = $_GET['fecha'] ?? date('Y-m-d');

        if ($idRol === 3) {
            // Secretaria — ve todas las facturas y puede crear nuevas
            $facturas       = $this->repo->getAllFacturas(150, $fechaFiltro);
            $citasSinFactura = $this->repo->getCitasSinFactura();
            $this->render('facturas/index', compact('facturas', 'citasSinFactura', 'bp', 'flashOk', 'flashError', 'fechaFiltro'));

        } elseif ($idRol === 1) {
            // Paciente — ve solo sus propias facturas
            $idPaciente = (int) ($_SESSION['id_referencia'] ?? 0);
            $facturas   = $this->repo->getFacturasByPaciente($idPaciente, 150, $fechaFiltro);
            $citasSinFactura = [];
            $this->render('facturas/index', compact('facturas', 'citasSinFactura', 'bp', 'flashOk', 'flashError', 'fechaFiltro'));

        } else {
            // Doctor o Admin — no tienen acceso al módulo de facturas
            http_response_code(403);
            include dirname(__DIR__, 3) . '/views/errors/403.php';
        }
    }

    // ──────────────────────────────────────────────────────────────────────
    //  POST /facturas/crear  (solo Secretaria)
    // ──────────────────────────────────────────────────────────────────────

    public function crear(): void
    {
        $this->requireRole(3); // Solo Secretaria

        $bp      = defined('BASE_PATH') ? BASE_PATH : '';
        $idCita  = (int) ($_POST['id_cita'] ?? 0);
        $detalle = trim($_POST['detalle'] ?? '');
        $monto   = (float) ($_POST['monto'] ?? 0);

        // Validaciones básicas
        if ($idCita <= 0) {
            $_SESSION['flash_error'] = 'Debes seleccionar una cita válida.';
            header('Location: ' . $bp . '/facturas');
            exit;
        }
        if (empty($detalle)) {
            $_SESSION['flash_error'] = 'El detalle de la factura no puede estar vacío.';
            header('Location: ' . $bp . '/facturas');
            exit;
        }
        if ($monto <= 0) {
            $_SESSION['flash_error'] = 'El monto debe ser mayor que cero.';
            header('Location: ' . $bp . '/facturas');
            exit;
        }

        // Verificar que la cita no tenga ya una factura
        if ($this->repo->existeFacturaParaCita($idCita)) {
            $_SESSION['flash_error'] = 'Esta cita ya tiene una factura emitida.';
            header('Location: ' . $bp . '/facturas');
            exit;
        }

        try {
            $this->repo->crearFactura($idCita, $detalle, $monto);
            $_SESSION['flash_ok'] = '✅ Factura emitida correctamente.';
        } catch (\RuntimeException $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        header('Location: ' . $bp . '/facturas');
        exit;
    }
}
