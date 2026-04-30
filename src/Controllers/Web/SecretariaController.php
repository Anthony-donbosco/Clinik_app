<?php

namespace App\Controllers\Web;

use App\Repositories\DashboardRepository;

/**
 * SecretariaController — Panel de control de la Secretaria.
 * Rol: Gestión global del sistema (pacientes, doctores, agenda del día).
 */
class SecretariaController extends BaseController
{
    private DashboardRepository $repo;

    public function __construct()
    {
        $this->repo = new DashboardRepository();
    }

    public function dashboard(): void
    {
        $this->requireRole(3); // Solo Secretaria

        $stats = [
            'pacientes_activos' => $this->repo->countPacientesActivos(),
            'doctores_activos'  => $this->repo->countDoctoresActivos(),
            'citas_hoy'         => $this->repo->countCitasHoy(),
            'citas_pendientes'  => $this->repo->countCitasPendientes(),
        ];

        $citas_hoy = $this->repo->getCitasDeHoy();
        $pacientes  = $this->repo->getAllPacientesActivos();
        $doctores   = $this->repo->getAllDoctoresActivos();

        $this->render('secretaria/dashboard', compact('stats', 'citas_hoy', 'pacientes', 'doctores'));
    }
}
