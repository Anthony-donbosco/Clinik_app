<?php 

namespace App\Controllers\Web; 

use App\Repositories\DashboardRepository;

class DoctorController extends BaseController 
{
    private DashboardRepository $repo;

    public function __construct() 
    { 
        $this->repo = new DashboardRepository(); 
    }

    /** Vista para el doctor individual */
    public function dashboard(): void 
    {
        $this->requireRole(2); // Solo Doctor
        $idDoctor = (int) $_SESSION['id_referencia'];
        $stats  = $this->repo->getStatsDoctor($idDoctor);
        $agenda = $this->repo->getAgendaDoctor($idDoctor);
        $this->render('doctor/dashboard', compact('stats', 'agenda'));
    }

    /** MÉTODO FALTANTE: Lista para Secretaria/Admin */
    public function lista(): void 
    {
        $this->requireRole([3, 4]); // Accesible para Secretaria (3) y Admin (4)
        
        $doctores = $this->repo->getAllDoctoresActivos(); 
        $bp = defined('BASE_PATH') ? BASE_PATH : '';
        
        $this->render('secretaria/doctores', compact('doctores', 'bp'));
    }
}