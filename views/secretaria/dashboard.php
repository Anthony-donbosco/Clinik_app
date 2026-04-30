<?php
/**
 * views/secretaria/dashboard.php
 * Dashboard principal de la Secretaria.
 * Variables recibidas: $stats, $citas_hoy, $pacientes, $doctores
 */
$pageTitle = 'Panel de Secretaria — Clini-K';
include __DIR__ . '/../layout/head.php';

// Helper: clase CSS por nombre de estado
function estadoClass(string $estado): string {
    return match(strtolower($estado)) {
        'pendiente'  => 'badge estado-pendiente',
        'aprobada'   => 'badge estado-aprobada',
        'atendida'   => 'badge estado-atendida',
        'cancelada'  => 'badge estado-cancelada',
        default      => 'badge badge-muted',
    };
}
?>

<!-- ── Topbar ────────────────────────────────────────────────── -->
<header class="topbar">
    <h1 class="topbar-title">Panel de Control — Secretaria</h1>
    <div class="topbar-actions">
        <button class="notification-btn" id="notif-bell-btn" aria-label="Ver notificaciones" title="Notificaciones">
            🔔 <span class="notification-dot" id="notif-dot"></span>
        </button>
        <div class="user-avatar" title="<?= htmlspecialchars($_SESSION['nombre'] ?? '') ?>">
            <?= strtoupper(substr($_SESSION['nombre'] ?? 'S', 0, 1)) ?>
        </div>
    </div>
</header>

<!-- ── Contenido principal ──────────────────────────────────── -->
<main class="page-content" id="secretaria-dashboard">

    <div class="page-header">
        <h2>Bienvenida, <?= htmlspecialchars(explode(' ', $_SESSION['nombre'])[0] ?? 'Secretaria') ?></h2>
        <p class="text-secondary">Resumen del sistema al día de hoy, <?= date('d/m/Y') ?></p>
    </div>

    <!-- ── Tarjetas de estadísticas ─────────────────────────── -->
    <section class="stats-grid" aria-label="Estadísticas generales del sistema">

        <div class="stat-card" id="stat-pacientes">
            <div class="stat-icon cyan">👤</div>
            <div>
                <div class="stat-value"><?= $stats['pacientes_activos'] ?></div>
                <div class="stat-label">Pacientes Activos</div>
            </div>
        </div>

        <div class="stat-card" id="stat-doctores">
            <div class="stat-icon blue">🩺</div>
            <div>
                <div class="stat-value"><?= $stats['doctores_activos'] ?></div>
                <div class="stat-label">Doctores Activos</div>
            </div>
        </div>

        <div class="stat-card" id="stat-citas-hoy">
            <div class="stat-icon violet">📅</div>
            <div>
                <div class="stat-value"><?= $stats['citas_hoy'] ?></div>
                <div class="stat-label">Citas de Hoy</div>
            </div>
        </div>

        <div class="stat-card" id="stat-pendientes">
            <div class="stat-icon green">⏳</div>
            <div>
                <div class="stat-value"><?= $stats['citas_pendientes'] ?></div>
                <div class="stat-label">Citas Pendientes</div>
            </div>
        </div>

    </section>

    <!-- ── Agenda del día ────────────────────────────────────── -->
    <section class="content-grid">

        <div class="card" id="card-agenda-hoy" style="grid-column: 1 / -1;">
            <div class="card-header">
                <h3 class="card-title">📅 Agenda de Hoy — <?= date('d \d\e F \d\e Y') ?></h3>
                <a href="/citas" class="btn btn-primary btn-sm" id="btn-ver-todas-citas">Ver todas las citas</a>
            </div>

            <?php if (empty($citas_hoy)): ?>
                <div class="table-empty">
                    <span>📭</span>
                    No hay citas programadas para hoy.
                </div>
            <?php else: ?>
            <div class="table-wrapper">
                <table id="tabla-agenda-hoy">
                    <thead>
                        <tr>
                            <th>Hora</th>
                            <th>Paciente</th>
                            <th>Doctor</th>
                            <th>Especialidad</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($citas_hoy as $cita): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars(substr($cita['hora'], 0, 5)) ?></strong></td>
                            <td><?= htmlspecialchars($cita['nombre_paciente']) ?></td>
                            <td><?= htmlspecialchars($cita['nombre_doctor']) ?></td>
                            <td><span class="badge badge-primary"><?= htmlspecialchars($cita['especialidad']) ?></span></td>
                            <td><span class="<?= estadoClass($cita['estado']) ?>"><?= htmlspecialchars($cita['estado']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- ── Lista de Pacientes ──────────────────────────── -->
        <div class="card" id="card-pacientes">
            <div class="card-header">
                <h3 class="card-title">👤 Pacientes Registrados</h3>
                <span class="badge badge-primary"><?= count($pacientes) ?></span>
            </div>
            <?php if (empty($pacientes)): ?>
                <div class="table-empty"><span>👥</span>No hay pacientes registrados.</div>
            <?php else: ?>
            <div class="table-wrapper">
                <table id="tabla-pacientes">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Identificación</th>
                            <th>Teléfono</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pacientes as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars(trim($p['nombre_completo'])) ?></td>
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

        <!-- ── Lista de Doctores ───────────────────────────── -->
        <div class="card" id="card-doctores">
            <div class="card-header">
                <h3 class="card-title">🩺 Doctores del Personal</h3>
                <span class="badge badge-primary"><?= count($doctores) ?></span>
            </div>
            <?php if (empty($doctores)): ?>
                <div class="table-empty"><span>🏥</span>No hay doctores registrados.</div>
            <?php else: ?>
            <div class="table-wrapper">
                <table id="tabla-doctores">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Especialidad</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($doctores as $d): ?>
                        <tr>
                            <td><?= htmlspecialchars($d['nombre_completo']) ?></td>
                            <td><span class="badge badge-primary"><?= htmlspecialchars($d['especialidad']) ?></span></td>
                            <td><span class="badge estado-activo"><?= htmlspecialchars($d['estado']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

    </section>

</main>

<?php include __DIR__ . '/../layout/foot.php'; ?>
