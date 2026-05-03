<?php
/**
 * views/admin/citas.php
 * Vista de SUPERVISIÓN de citas para el Admin (rol=4).
 * Solo lectura — el Admin no crea ni cancela citas, eso es responsabilidad
 * de la Secretaria y los Pacientes. Su función es monitorear el estado global.
 */
$bp = $bp ?? '';

function estadoCitaClassAdmin(string $estado): string {
    return match(strtolower(trim($estado))) {
        'pendiente'  => 'badge estado-pendiente',
        'aprobada'   => 'badge estado-aprobada',
        'atendida'   => 'badge estado-atendida',
        'cancelada'  => 'badge estado-cancelada',
        default      => 'badge badge-muted',
    };
}
?>
<?php include dirname(__DIR__) . '/layout/head.php'; ?>

<style>
.stat-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
.stat-box { background: var(--bg-glass); border: 1px solid var(--border-medium); border-radius: var(--radius-md);
            padding: 1rem 1.25rem; text-align: center; }
.stat-box .stat-num { font-size: 2rem; font-weight: 700; color: var(--color-primary); }
.stat-box .stat-lbl { font-size: .72rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: .05em; margin-top: .2rem; }
</style>

<div class="page-header">
    <div style="display:flex;align-items:center;gap:1rem;">
        <a href="<?= $bp ?>/admin" class="btn btn-secondary btn-sm" id="btn-volver-admin-citas">← Panel Admin</a>
        <div>
            <h1>📅 Supervisión de Citas</h1>
            <p style="color:var(--text-secondary);margin-top:.2rem;">
                Vista global de todas las citas del sistema. Solo lectura.
            </p>
        </div>
    </div>
</div>

<?php if (!empty($flashOk)): ?>
    <div class="alert alert-success" style="margin-bottom:1.5rem;">✅ <?= htmlspecialchars($flashOk, ENT_QUOTES) ?></div>
<?php endif; ?>

<!-- Estadísticas rápidas -->
<?php
$stats = [
    'total'      => count($citas),
    'pendientes' => count(array_filter($citas, fn($c) => (int)$c['id_estado'] === 1)),
    'aprobadas'  => count(array_filter($citas, fn($c) => (int)$c['id_estado'] === 2)),
    'atendidas'  => count(array_filter($citas, fn($c) => (int)$c['id_estado'] === 4)),
    'canceladas' => count(array_filter($citas, fn($c) => (int)$c['id_estado'] === 5)),
];
?>
<div class="stat-row">
    <div class="stat-box">
        <div class="stat-num"><?= $stats['total'] ?></div>
        <div class="stat-lbl">Total (últimas 100)</div>
    </div>
    <div class="stat-box">
        <div class="stat-num" style="color:#fbbf24;"><?= $stats['pendientes'] ?></div>
        <div class="stat-lbl">Pendientes</div>
    </div>
    <div class="stat-box">
        <div class="stat-num" style="color:#34d399;"><?= $stats['aprobadas'] ?></div>
        <div class="stat-lbl">Aprobadas</div>
    </div>
    <div class="stat-box">
        <div class="stat-num" style="color:var(--color-primary);"><?= $stats['atendidas'] ?></div>
        <div class="stat-lbl">Atendidas</div>
    </div>
    <div class="stat-box">
        <div class="stat-num" style="color:#f87171;"><?= $stats['canceladas'] ?></div>
        <div class="stat-lbl">Canceladas</div>
    </div>
</div>

<!-- Tabla global de citas -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Todas las Citas del Sistema</h2>
        <span class="badge badge-primary"><?= count($citas) ?> registro(s)</span>
    </div>

    <?php if (empty($citas)): ?>
        <div style="text-align:center;padding:2.5rem;color:var(--text-muted);">
            📭 No hay citas registradas en el sistema.
        </div>
    <?php else: ?>
    <div class="table-wrapper">
        <table id="tabla-citas-admin">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Paciente</th>
                    <th>Doctor</th>
                    <th>Especialidad</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($citas as $c): ?>
                <?php
                    $idEst = (int)$c['id_estado'];
                    $puedeAprobar  = ($idEst === 1);
                    $puedeRechazar = in_array($idEst, [1, 2]);
                    $puedeCancelar = in_array($idEst, [1, 2]);
                ?>
                <tr id="admin-cita-<?= (int)$c['id_cita'] ?>">
                    <td><?= (int)$c['id_cita'] ?></td>
                    <td><?= date('d/m/Y', strtotime($c['fecha'])) ?></td>
                    <td><strong><?= htmlspecialchars(substr($c['hora'], 0, 5)) ?></strong></td>
                    <td><?= htmlspecialchars($c['nombre_paciente'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($c['nombre_doctor'] ?? '—') ?></td>
                    <td><span class="badge badge-primary"><?= htmlspecialchars($c['especialidad'] ?? '—') ?></span></td>
                    <td id="badge-estado-<?= (int)$c['id_cita'] ?>">
                        <span class="<?= estadoCitaClassAdmin($c['estado'] ?? '') ?>"><?= htmlspecialchars($c['estado'] ?? '—') ?></span>
                    </td>
                    <td> 
                        <div style="display:flex;gap:.35rem;flex-wrap:wrap;">
                            <a href="<?= $bp ?>/admin/editar-cita?id=<?= (int)$c['id_cita'] ?>"
                            class="btn btn-sm"
                            style="background:rgba(59,130,246,.15);color:#60a5fa;border:1px solid rgba(59,130,246,.3);">
                                ✏️ Editar
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<div style="margin-top:1rem;font-size:.77rem;color:var(--text-muted);padding:.5rem;">
    ℹ️ Como Administrador tienes control total sobre las citas. Los estados de <strong>Aprobación</strong> y <strong>Rechazo</strong> son responsabilidad habitual de la Secretaria.
</div>



<?php include dirname(__DIR__) . '/layout/foot.php'; ?>
