<?php
/**
 * views/doctor/dashboard.php
 * Dashboard del Doctor — muestra su agenda y estadísticas personales.
 * Variables: $stats (array), $agenda (array[])
 */
$pageTitle = 'Mi Panel — Dr. ' . htmlspecialchars($_SESSION['nombre'] ?? '') . ' · Clini-K';
include __DIR__ . '/../layout/head.php';
?>

<!-- ── Topbar ────────────────────────────────────────────────── -->
<header class="topbar">
    <h1 class="topbar-title">Mi Panel Médico</h1>
    <div class="topbar-actions">
        <button class="notification-btn" id="notif-bell-btn" aria-label="Ver notificaciones">
            🔔 <span class="notification-dot" id="notif-dot"></span>
        </button>
        <div class="user-avatar" title="<?= htmlspecialchars($_SESSION['nombre'] ?? '') ?>">
            <?= strtoupper(substr($_SESSION['nombre'] ?? 'D', 0, 1)) ?>
        </div>
    </div>
</header>

<!-- ── Contenido principal ──────────────────────────────────── -->
<main class="page-content" id="doctor-dashboard">

    <div class="page-header">
        <h2>Dr. <?= htmlspecialchars($_SESSION['nombre'] ?? '') ?></h2>
        <p class="text-secondary">Tu resumen de agenda — <?= date('l, d \d\e F \d\e Y') ?></p>
    </div>

    <!-- ── Tarjetas de estadísticas del doctor ──────────────── -->
    <section class="stats-grid" aria-label="Estadísticas de agenda del doctor">

        <div class="stat-card" id="stat-doctor-hoy">
            <div class="stat-icon cyan">📅</div>
            <div>
                <div class="stat-value"><?= $stats['hoy'] ?></div>
                <div class="stat-label">Citas de Hoy</div>
            </div>
        </div>

        <div class="stat-card" id="stat-doctor-pendientes">
            <div class="stat-icon violet">⏳</div>
            <div>
                <div class="stat-value"><?= $stats['pendientes'] ?></div>
                <div class="stat-label">Citas Pendientes</div>
            </div>
        </div>

        <div class="stat-card" id="stat-doctor-proximas">
            <div class="stat-icon blue">🗓</div>
            <div>
                <div class="stat-value"><?= $stats['proximas'] ?></div>
                <div class="stat-label">Próximos 7 Días</div>
            </div>
        </div>

        <div class="stat-card" id="stat-doctor-atendidas">
            <div class="stat-icon green">✅</div>
            <div>
                <div class="stat-value"><?= $stats['atendidas'] ?></div>
                <div class="stat-label">Total Atendidas</div>
            </div>
        </div>

    </section>

    <!-- ── Agenda próxima ────────────────────────────────────── -->
    <section class="content-grid">
        <div class="card" id="card-agenda-doctor" style="grid-column: 1 / -1;">
            <div class="card-header">
                <h3 class="card-title">🗓 Mi Agenda — Próximos 7 Días</h3>
                <a href="<?= defined('BASE_PATH') ? BASE_PATH : '' ?>/citas" class="btn btn-primary btn-sm" id="btn-ver-agenda-completa">Ver agenda completa</a>
            </div>

            <?php if (empty($agenda)): ?>
                <div class="table-empty">
                    <span>🎉</span>
                    No tienes citas programadas para los próximos 7 días.
                </div>
            <?php else: ?>
            <div class="table-wrapper">
                <table id="tabla-agenda-doctor">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Paciente</th>
                            <th>Identificación</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agenda as $cita): ?>
                        <?php
                            $estadoClass = match((int)$cita['id_estado']) {
                                1 => 'badge estado-pendiente',
                                4 => 'badge estado-atendida',
                                5 => 'badge estado-cancelada',
                                default => 'badge badge-muted',
                            };
                            $esHoy = ($cita['fecha'] === date('Y-m-d'));
                        ?>
                        <tr <?= $esHoy ? 'style="background:rgba(6,182,212,0.06);"' : '' ?>>
                            <td>
                                <?= date('d/m/Y', strtotime($cita['fecha'])) ?>
                                <?php if ($esHoy): ?><span class="badge badge-primary" style="margin-left:.4rem">Hoy</span><?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars(substr($cita['hora'], 0, 5)) ?></strong></td>
                            <td><?= htmlspecialchars($cita['nombre_paciente']) ?></td>
                            <td><code><?= htmlspecialchars($cita['numeroIdentificacion']) ?></code></td>
                            <td><span class="<?= $estadoClass ?>"><?= htmlspecialchars($cita['estado']) ?></span></td>
                            <td>
                                <?php if (in_array((int)$cita['id_estado'], [1, 2], true)): ?>
                                    <a href="<?= defined('BASE_PATH') ? BASE_PATH : '' ?>/historial/atender?cita=<?= (int)$cita['id_cita'] ?>"
                                       class="btn btn-primary btn-sm"
                                       id="btn-atender-cita-<?= (int)$cita['id_cita'] ?>">
                                        ✅ Atender
                                    </a>
                                <?php elseif ((int)$cita['id_estado'] === 4): ?>
                                    <a href="<?= defined('BASE_PATH') ? BASE_PATH : '' ?>/historial"
                                       class="btn btn-secondary btn-sm"
                                       id="btn-historial-cita-<?= (int)$cita['id_cita'] ?>">
                                        📋 Ver historial
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size:.8rem">—</span>
                                <?php endif; ?>
                            </td>
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
