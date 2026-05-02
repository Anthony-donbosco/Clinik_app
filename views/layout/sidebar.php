<?php
/**
 * views/layout/sidebar.php
 * Sidebar de navegación — se incluye en todos los dashboards.
 */
$rol    = $_SESSION['id_rol']     ?? 0;
$nombre = $_SESSION['nombre']     ?? 'Usuario';
$rolNom = $_SESSION['nombre_rol'] ?? '';
$bp     = defined('BASE_PATH') ? BASE_PATH : '';

$menus = [
    3 => [ // Secretaria
        ['icon' => '🏠', 'label' => 'Dashboard',     'href' => '/dashboard/secretaria'],
        ['icon' => '📅', 'label' => 'Citas',          'href' => '/citas'],
        ['icon' => '🧾', 'label' => 'Facturas',       'href' => '/facturas'],
        ['icon' => '👤', 'label' => 'Pacientes',      'href' => '/pacientes'],
        ['icon' => '🩺', 'label' => 'Doctores',       'href' => '/doctores'],
    ],
    2 => [ // Doctor
        ['icon' => '🏠', 'label' => 'Dashboard',     'href' => '/dashboard/doctor'],
        ['icon' => '📅', 'label' => 'Mi Agenda',     'href' => '/citas'],
        ['icon' => '📋', 'label' => 'Historiales',   'href' => '/historial'],
    ],
    1 => [ // Paciente
        ['icon' => '🏠', 'label' => 'Dashboard',     'href' => '/dashboard/paciente'],
        ['icon' => '📅', 'label' => 'Mis Citas',     'href' => '/citas'],
        ['icon' => '📋', 'label' => 'Mi Historial',  'href' => '/historial'],
        ['icon' => '🧾', 'label' => 'Mis Facturas',  'href' => '/facturas'],
        ['icon' => '👤', 'label' => 'Mi Perfil',     'href' => '/paciente/perfil'],
    ],
    4 => [ // Admin
        ['icon' => '🏠', 'label' => 'Panel Admin',   'href' => '/admin'],
        ['icon' => '➕', 'label' => 'Crear Usuario',  'href' => '/admin/crear-usuario'],
        ['icon' => '📅', 'label' => 'Citas',          'href' => '/citas'],
    ],
];

$currentMenu = $menus[$rol] ?? [];

// Ruta actual sin BASE_PATH para comparar con los hrefs del menú
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$currentPath = ($bp !== '' && str_starts_with($requestPath, $bp))
    ? substr($requestPath, strlen($bp))
    : $requestPath;
$currentPath = '/' . ltrim($currentPath, '/');
?>
<nav class="sidebar" aria-label="Menú principal de Clini-K">

    <div class="sidebar-brand">
        <span>⚕</span> Clini<span>-K</span>
    </div>

    <div class="sidebar-user">
        <div class="sidebar-avatar"><?= strtoupper(substr($nombre, 0, 1)) ?></div>
        <div class="sidebar-user-info">
            <div class="sidebar-user-name"><?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?></div>
            <div class="sidebar-user-role"><?= htmlspecialchars($rolNom, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    </div>

    <div class="sidebar-nav">
        <p class="nav-section-title">Menú</p>

        <?php foreach ($currentMenu as $item): ?>
            <?php
            $isActive = ($currentPath === $item['href'])
                     || str_starts_with($currentPath, rtrim($item['href'], '/') . '/');
            $navId = 'nav-' . strtolower(str_replace(['/', ' '], '-', ltrim($item['href'], '/')));
            ?>
            <a href="<?= $bp . $item['href'] ?>"
               class="nav-link <?= $isActive ? 'active' : '' ?>"
               id="<?= htmlspecialchars($navId, ENT_QUOTES) ?>">
                <span class="nav-icon"><?= $item['icon'] ?></span>
                <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
                <?php if ($item['label'] === 'Notificaciones'): ?>
                    <span class="nav-badge" id="nav-notif-count" style="display:none">0</span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="sidebar-footer">
        <a href="<?= $bp ?>/logout" class="nav-link" id="nav-logout-btn">
            <span class="nav-icon">🚪</span> Cerrar sesión
        </a>
    </div>

</nav>
