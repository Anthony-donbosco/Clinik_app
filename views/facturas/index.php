<?php
/**
 * views/facturas/index.php
 * Vista unificada del módulo de Facturación.
 * - Secretaria (rol=3): ve todas las facturas + formulario para crear nuevas
 * - Paciente   (rol=1): ve solo sus propias facturas
 * Variables: $facturas, $citasSinFactura, $bp, $flashOk, $flashError
 */
$idRol     = (int) ($_SESSION['id_rol'] ?? 0);
$pageTitle = $idRol === 1 ? 'Mis Facturas — Clini-K' : 'Gestión de Facturas — Clini-K';
include __DIR__ . '/../layout/head.php';
?>
<style>
/* ── Módulo Facturas ──────────────────────────────── */
.form-factura {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
}
@media (max-width: 640px) { .form-factura { grid-template-columns: 1fr; } }

.form-group label {
    display: block;
    font-size: .78rem; font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase; letter-spacing: .05em;
    margin-bottom: .4rem;
}
.form-control {
    width: 100%;
    background: rgba(255,255,255,0.06);
    border: 1px solid var(--border-medium);
    border-radius: var(--radius-md);
    padding: .75rem 1rem;
    color: var(--text-primary);
    font-size: .92rem;
    font-family: var(--font-base);
    transition: border-color .2s, box-shadow .2s;
    outline: none;
}
.form-control:focus {
    border-color: var(--border-focus);
    box-shadow: 0 0 0 3px rgba(6,182,212,.15);
    background: rgba(6,182,212,.04);
}
.form-control option { background: #1e293b; color: #f1f5f9; }

/* Monto con símbolo $ */
.input-with-symbol { position: relative; }
.input-symbol {
    position: absolute; left: .85rem; top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted); font-weight: 600; pointer-events: none;
}
.input-with-symbol .form-control { padding-left: 1.8rem; }

/* Badge monto */
.badge-monto {
    background: rgba(16,185,129,.15);
    color: #34d399;
    border: 1px solid rgba(16,185,129,.25);
    border-radius: var(--radius-sm);
    padding: .25rem .65rem;
    font-size: .8rem; font-weight: 700;
    font-variant-numeric: tabular-nums;
}

/* Fila tabla seleccionada */
tr.selected-row td { background: rgba(6,182,212,.07); }

/* Botón imprimir */
@media print {
    .sidebar, .topbar, .no-print { display: none !important; }
    .page-content { margin: 0 !important; padding: 0 !important; }
}
</style>

<!-- ── Topbar ──────────────────────────────────────────── -->
<header class="topbar">
    <h1 class="topbar-title">
        <?= $idRol === 1 ? '🧾 Mis Facturas' : '🧾 Gestión de Facturas' ?>
    </h1>
    <div class="topbar-actions">
        <button class="notification-btn" id="notif-bell-btn" aria-label="Notificaciones">
            🔔 <span class="notification-dot" id="notif-dot"></span>
        </button>
        <div class="user-avatar"><?= strtoupper(substr($_SESSION['nombre'] ?? 'U', 0, 1)) ?></div>
    </div>
</header>

<main class="page-content" id="facturas-page">

    <!-- ── Mensajes flash ──────────────────────────────── -->
    <?php if ($flashOk): ?>
        <div class="alert alert-success" id="flash-ok-factura" role="status">
            <span>✅</span> <?= htmlspecialchars($flashOk) ?>
        </div>
    <?php endif; ?>
    <?php if ($flashError): ?>
        <div class="alert alert-error" id="flash-error-factura" role="alert">
            <span>⚠</span> <?= htmlspecialchars($flashError) ?>
        </div>
    <?php endif; ?>

    <div class="content-grid" style="align-items: start;">

        <!-- ════════════════════════════════════════════════
             FORMULARIO NUEVA FACTURA (solo Secretaria)
        ═══════════════════════════════════════════════════ -->
        <?php if ($idRol === 3): ?>
        <div class="card" id="card-nueva-factura">
            <div class="card-header">
                <h2 class="card-title">📄 Emitir Nueva Factura</h2>
                <span class="badge badge-primary"><?= count($citasSinFactura) ?> cita(s) pendiente(s)</span>
            </div>

            <?php if (empty($citasSinFactura)): ?>
                <div class="table-empty">
                    <span>🎉</span>
                    Todas las citas atendidas ya tienen factura emitida.
                </div>
            <?php else: ?>
            <form id="form-nueva-factura" method="POST"
                  action="<?= $bp ?>/facturas/crear" novalidate>

                <div class="form-factura">

                    <!-- Cita -->
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="select-cita">Cita Atendida</label>
                        <select id="select-cita" name="id_cita" class="form-control" required>
                            <option value="">— Selecciona una cita —</option>
                            <?php foreach ($citasSinFactura as $c): ?>
                            <option value="<?= (int)$c['id_cita'] ?>"
                                    data-especialidad="<?= htmlspecialchars($c['especialidad']) ?>">
                                #<?= $c['id_cita'] ?> |
                                <?= date('d/m/Y', strtotime($c['fecha_cita'])) ?>
                                <?= substr($c['hora_cita'], 0, 5) ?> —
                                <?= htmlspecialchars($c['nombre_paciente']) ?>
                                (<?= htmlspecialchars($c['numeroIdentificacion']) ?>) —
                                Dr. <?= htmlspecialchars($c['nombre_doctor']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Detalle -->
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="input-detalle">Detalle / Concepto</label>
                        <input type="text"
                               id="input-detalle"
                               name="detalle"
                               class="form-control"
                               maxlength="255"
                               placeholder="Ej. Consulta Medicina General"
                               required>
                    </div>

                    <!-- Monto -->
                    <div class="form-group">
                        <label for="input-monto">Monto ($)</label>
                        <div class="input-with-symbol">
                            <span class="input-symbol">$</span>
                            <input type="number"
                                   id="input-monto"
                                   name="monto"
                                   class="form-control"
                                   min="0.01" step="0.01"
                                   placeholder="25.00"
                                   required>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="form-group" style="display:flex; align-items:flex-end;">
                        <button type="submit" id="btn-emitir-factura"
                                class="btn btn-primary w-full">
                            🧾 Emitir Factura
                        </button>
                    </div>

                </div>
            </form>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- ════════════════════════════════════════════════
             TABLA DE FACTURAS
        ═══════════════════════════════════════════════════ -->
        <div class="card" id="card-listado-facturas"
             <?= $idRol === 1 ? 'style="grid-column:1/-1"' : '' ?>>
            <div class="card-header">
                <h2 class="card-title">
                    <?= $idRol === 1 ? '📋 Mis Facturas' : '📋 Todas las Facturas' ?>
                </h2>
                <div style="display:flex; gap:.75rem; align-items:center;">
                    <span class="badge badge-primary"><?= count($facturas) ?> registro(s)</span>
                    <button class="btn btn-secondary btn-sm no-print"
                            id="btn-imprimir-facturas"
                            onclick="window.print()"
                            title="Imprimir listado">
                        🖨️ Imprimir
                    </button>
                </div>
            </div>

            <?php if (empty($facturas)): ?>
                <div class="table-empty">
                    <span>📭</span>
                    <?= $idRol === 1 ? 'No tienes facturas registradas.' : 'No hay facturas en el sistema.' ?>
                </div>
            <?php else: ?>
            <div class="table-wrapper">
                <table id="tabla-facturas">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fecha</th>
                            <?php if ($idRol === 3): ?>
                            <th>Paciente</th>
                            <th>Cédula</th>
                            <?php endif; ?>
                            <th>Doctor</th>
                            <th>Especialidad</th>
                            <th>Detalle</th>
                            <th>Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($facturas as $f): ?>
                        <tr id="fila-factura-<?= (int)$f['id_factura'] ?>">
                            <td><?= (int)$f['id_factura'] ?></td>
                            <td><?= date('d/m/Y', strtotime($f['fecha'])) ?></td>
                            <?php if ($idRol === 3): ?>
                            <td><strong><?= htmlspecialchars($f['nombre_paciente'] ?? '—') ?></strong></td>
                            <td><code><?= htmlspecialchars($f['numeroIdentificacion'] ?? '—') ?></code></td>
                            <?php endif; ?>
                            <td><?= htmlspecialchars($f['nombre_doctor'] ?? '—') ?></td>
                            <td>
                                <span class="badge badge-primary">
                                    <?= htmlspecialchars($f['especialidad'] ?? '—') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($f['detalle']) ?></td>
                            <td>
                                <span class="badge-monto">
                                    $<?= number_format((float)$f['monto'], 2) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="<?= $idRol === 3 ? 7 : 5 ?>"
                                style="text-align:right; font-weight:600; color:var(--text-secondary); font-size:.85rem;">
                                Total:
                            </td>
                            <td>
                                <span class="badge-monto">
                                    $<?= number_format(array_sum(array_column($facturas, 'monto')), 2) ?>
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <?php endif; ?>
        </div>

    </div><!-- /.content-grid -->
</main>

<script>
/* Auto-completa el detalle según la especialidad del doctor */
(function () {
    const selectCita   = document.getElementById('select-cita');
    const inputDetalle = document.getElementById('input-detalle');
    if (!selectCita || !inputDetalle) return;

    selectCita.addEventListener('change', function () {
        const opt         = this.options[this.selectedIndex];
        const especialidad = opt.dataset.especialidad || '';
        if (especialidad && !inputDetalle.value) {
            inputDetalle.value = 'Consulta Especialidad ' + especialidad;
        }
    });
})();
</script>

<?php include __DIR__ . '/../layout/foot.php'; ?>
