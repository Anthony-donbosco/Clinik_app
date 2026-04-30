<?php
/**
 * views/secretaria/pacientes.php
 * Listado completo de pacientes.
 */
$pageTitle = 'Directorio de Pacientes — Clini-K';
include __DIR__ . '/../layout/head.php';
?>

<header class="topbar">
    <h1 class="topbar-title">Gestión de Pacientes</h1>
    <div class="topbar-actions">
        <div class="user-avatar"><?= strtoupper(substr($_SESSION['nombre'] ?? 'S', 0, 1)) ?></div>
    </div>
</header>

<main class="page-content">
    <div class="page-header">
        <h2>Pacientes Registrados</h2>
        <p class="text-secondary">Listado de personas con acceso al sistema.</p>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">👥 Directorio</h3>
            <span class="badge badge-primary"><?= count($pacientes) ?> registros</span>
        </div>

        <?php if (empty($pacientes)): ?>
            <div class="table-empty"><span>👥</span>No hay pacientes registrados.</div>
        <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Nombre Completo</th>
                        <th>Identificación</th>
                        <th>Teléfono</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pacientes as $p): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($p['nombre_completo']) ?></strong></td>
                        <td><code><?= htmlspecialchars($p['numeroIdentificacion']) ?></code></td>
                        <td><?= htmlspecialchars($p['telefono'] ?? '—') ?></td>
                        <td><span class="badge estado-activo"><?= htmlspecialchars($p['estado']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/../layout/foot.php'; ?>