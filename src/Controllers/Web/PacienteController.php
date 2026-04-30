<?php 

namespace App\Controllers\Web; 

use App\Repositories\DashboardRepository;

class PacienteController extends BaseController 
{
    private DashboardRepository $repo;

    public function __construct() 
    { 
        $this->repo = new DashboardRepository(); 
    }

    /** Vista para el paciente individual */
    public function dashboard(): void 
    {
        $this->requireRole(1); // Solo Paciente
        $idPaciente = (int) $_SESSION['id_referencia'];
        $stats = $this->repo->getStatsPaciente($idPaciente);
        $citas = $this->repo->getCitasPaciente($idPaciente);
        $historial = $this->repo->getHistorialPaciente($idPaciente);
        $this->render('paciente/dashboard', compact('stats', 'citas', 'historial'));
    }

    /** MÉTODO FALTANTE: Lista para Secretaria/Admin */
    public function lista(): void 
    {
        $this->requireRole([3, 4]); // Accesible para Secretaria (3) y Admin (4)
        
        $pacientes = $this->repo->getAllPacientesActivos(); 
        $bp = defined('BASE_PATH') ? BASE_PATH : '';
        
        $this->render('secretaria/pacientes', compact('pacientes', 'bp'));
    }
}