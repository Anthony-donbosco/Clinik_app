<?php
/**
 * views/paciente/dashboard.php
 * Dashboard del Paciente — muestra sus citas e historial.
 * Variables: $stats (array), $citas (array[]), $historial (array[])
 */
$pageTitle = 'Mi Salud — ' . htmlspecialchars($_SESSION['nombre'] ?? '') . ' · Clini-K';
include __DIR__ . '/../layout/head.php';
?>

<!-- ── Topbar ────────────────────────────────────────────────── -->
<header class="topbar">
    <h1 class="topbar-title">Mi Portal de Salud</h1>
    <div class="topbar-actions">
        <button class="notification-btn" id="notif-bell-btn" aria-label="Ver notificaciones">
            🔔 <span class="notification-dot" id="notif-dot"></span>
        </button>
        <div class="user-avatar" title="<?= htmlspecialchars($_SESSION['nombre'] ?? '') ?>">
            <?= strtoupper(substr($_SESSION['nombre'] ?? 'P', 0, 1)) ?>
        </div>
    </div>
</header>

<!-- ── Contenido principal ──────────────────────────────────── -->
<main class="page-content" id="paciente-dashboard">

    <div class="page-header">
        <h2>Hola, <?= htmlspecialchars(explode(' ', $_SESSION['nombre'])[0] ?? 'Paciente') ?></h2>
        <p class="text-secondary">Aquí puedes revisar tus citas y tu historial médico</p>
    </div>

    <!-- ── Tarjetas de estadísticas del paciente ────────────── -->
    <section class="stats-grid" aria-label="Resumen de salud del paciente">

        <div class="stat-card" id="stat-paciente-total">
            <div class="stat-icon cyan">📅</div>
            <div>
                <div class="stat-value"><?= $stats['total'] ?></div>
                <div class="stat-label">Total de Citas</div>
            </div>
        </div>

        <div class="stat-card" id="stat-paciente-atendidas">
            <div class="stat-icon green">✅</div>
            <div>
                <div class="stat-value"><?= $stats['atendidas'] ?></div>
                <div class="stat-label">Consultas Atendidas</div>
            </div>
        </div>

        <div class="stat-card" id="stat-paciente-pendientes">
            <div class="stat-icon violet">⏳</div>
            <div>
                <div class="stat-value"><?= $stats['pendientes'] ?></div>
                <div class="stat-label">Citas Pendientes</div>
            </div>
        </div>

    </section>

    <div class="content-grid">

        <!-- ── Mis Citas ──────────────────────────────────── -->
        <div class="card" id="card-mis-citas">
            <div class="card-header">
                <h3 class="card-title">📅 Mis Citas</h3>
                <a href="<?= defined('BASE_PATH') ? BASE_PATH : '' ?>/citas" class="btn btn-primary btn-sm" id="btn-solicitar-cita">Solicitar cita</a>
            </div>

            <?php if (empty($citas)): ?>
                <div class="table-empty">
                    <span>📭</span>
                    No tienes citas registradas aún.
                </div>
            <?php else: ?>
            <div class="table-wrapper">
                <table id="tabla-mis-citas">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Doctor</th>
                            <th>Especialidad</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($citas as $cita): ?>
                        <?php
                            $estadoClass = match((int)$cita['id_estado']) {
                                1 => 'badge estado-pendiente',
                                2 => 'badge estado-aprobada',
                                4 => 'badge estado-atendida',
                                5 => 'badge estado-cancelada',
                                default => 'badge badge-muted',
                            };
                            $esFutura = (strtotime($cita['fecha']) >= strtotime('today'));
                        ?>
                        <tr>
                            <td>
                                <?= date('d/m/Y', strtotime($cita['fecha'])) ?>
                                <?php if ($esFutura && $cita['id_estado'] == 1): ?>
                                    <span class="badge badge-primary" style="margin-left:.4rem">Próxima</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars(substr($cita['hora'], 0, 5)) ?></td>
                            <td><?= htmlspecialchars($cita['nombre_doctor']) ?></td>
                            <td><span class="badge badge-primary"><?= htmlspecialchars($cita['especialidad']) ?></span></td>
                            <td><span class="<?= $estadoClass ?>"><?= htmlspecialchars($cita['estado']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- ── Mi Historial Médico ────────────────────────── -->
        <div class="card" id="card-historial-medico">
            <div class="card-header">
                <h3 class="card-title">Mi Historial Médico</h3>
                <a href="<?= defined('BASE_PATH') ? BASE_PATH : '' ?>/historial" class="btn btn-secondary btn-sm" id="btn-ver-historial-completo">Ver completo</a>
            </div>

            <?php if (empty($historial)): ?>
                <div class="table-empty">
                    <span>📁</span>
                    Tu historial médico está vacío por ahora.
                </div>
            <?php else: ?>
            <div class="table-wrapper">
                <table id="tabla-historial-paciente">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Doctor</th>
                            <th>Especialidad</th>
                            <th>Diagnóstico</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historial as $h): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($h['fecha'])) ?></td>
                            <td><?= htmlspecialchars($h['nombre_doctor']) ?></td>
                            <td><span class="badge badge-primary"><?= htmlspecialchars($h['especialidad']) ?></span></td>
                            <td style="max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"
                                title="<?= htmlspecialchars($h['diagnostico'] ?? '') ?>">
                                <?= htmlspecialchars(mb_strimwidth($h['diagnostico'] ?? '—', 0, 55, '...')) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

    </div>

</main>

<?php include __DIR__ . '/../layout/foot.php'; ?>
