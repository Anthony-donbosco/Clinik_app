<?php
/**
 * views/secretaria/doctores.php
 * Listado de doctores.
 */
$pageTitle = 'Personal Médico — Clini-K';
include __DIR__ . '/../layout/head.php';
?>

<header class="topbar">
    <h1 class="topbar-title">Directorio Médico</h1>
    <div class="topbar-actions">
        <div class="user-avatar"><?= strtoupper(substr($_SESSION['nombre'] ?? 'S', 0, 1)) ?></div>
    </div>
</header>

<main class="page-content">
    <div class="page-header">
        <h2>Doctores de la Clínica</h2>
        <p class="text-secondary">Personal médico activo en el sistema.</p>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">🩺 Especialistas</h3>
            <span class="badge badge-primary"><?= count($doctores) ?> médicos</span>
        </div>

        <?php if (empty($doctores)): ?>
            <div class="table-empty"><span>🏥</span>No hay doctores registrados.</div>
        <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Nombre del Doctor</th>
                        <th>Especialidad</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($doctores as $d): ?>
                    <tr>
                        <td><strong>Dr. <?= htmlspecialchars($d['nombre_completo']) ?></strong></td>
                        <td><span class="badge badge-primary"><?= htmlspecialchars($d['especialidad']) ?></span></td>
                        <td><span class="badge estado-activo"><?= htmlspecialchars($d['estado']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/../layout/foot.php'; ?>