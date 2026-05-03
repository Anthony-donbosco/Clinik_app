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
    grid-template-columns: 1fr;
    gap: 1.25rem;
}

@media (min-width: 1024px) {
    .custom-layout {
        grid-template-columns: 320px 1fr !important;
    }
}

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
    /* 1. Limpiar los textos del navegador (Fecha, URL, etc.) */
    @page {
        margin: 1.5cm; 
    }

    /* 2. Evitar páginas extra en blanco reajustando el body */
    html, body {
        margin: 0 !important;
        padding: 0 !important;
        height: auto !important;
        background: white !important;
    }

    /* 3. Damos el margen a la página principal en lugar del body */
    #facturas-page {
        padding: 0 !important;
        width: 100% !important;
        box-sizing: border-box !important; /* Evita que el padding desborde la hoja */
    }

    /* 4. Ocultar elementos no deseados */
    .sidebar, .topbar, .no-print, #card-nueva-factura { 
        display: none !important; 
    }
    
    /* 5. ANIQUILAR EL GRID: Matamos el espacio del formulario izquierdo */
    .content-grid, .custom-layout { 
        display: block !important; 
        grid-template-columns: 1fr !important; /* Si insiste en ser grid, lo forzamos a 1 sola columna */
        width: 100% !important; 
        gap: 0 !important;
        margin: 0 !important;
    }
    
    /* 6. Limpiar la tarjeta y forzarla a tomar todo el ancho */
    #card-listado-facturas { 
        display: block !important;
        width: 100% !important; 
        max-width: 100% !important;
        grid-column: 1 / -1 !important; /* Asegura que tome todo el espacio disponible */
        border: none !important; 
        box-shadow: none !important; 
        margin: 0 !important; 
        padding: 0 !important; 
    }
    
    /* 7. LIBERAR LA TABLA */
    .table-wrapper { 
        overflow: visible !important; 
        width: 100% !important; 
    }
    
    #tabla-facturas { 
        width: 100% !important; 
        margin: 0 auto !important;
    }
    
    /* 8. Mantener la paginación visible completa */
    #tabla-facturas tbody tr { 
        display: table-row !important; 
    }
    
    /* 9. Eliminar el espacio del sidebar de la plantilla base */
    .app-layout, .main-content {
        display: block !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }
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

    <div class="content-grid custom-layout" style="align-items: start;">

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
            
            <?php if (!empty($citasSinFactura)): ?>
            <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-medium);">
                <h4 style="font-size:.9rem; color:var(--text-muted); margin-bottom:.75rem; text-transform:uppercase; letter-spacing:.05em;">Faltantes por facturar</h4>
                <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:.5rem;">
                    <?php foreach ($citasSinFactura as $c): ?>
                    <li style="background:rgba(255,255,255,0.03); border:1px solid var(--border-medium); border-radius:var(--radius-sm); padding:.5rem .75rem; font-size:.85rem;">
                        <strong style="color:var(--text-primary);">#<?= $c['id_cita'] ?> - <?= date('d/m/Y', strtotime($c['fecha_cita'])) ?></strong><br>
                        <span style="color:var(--text-secondary);"><?= htmlspecialchars($c['nombre_paciente']) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- ════════════════════════════════════════════════
             TABLA DE FACTURAS
        ═══════════════════════════════════════════════════ -->
        <div class="card" id="card-listado-facturas"
             <?= $idRol === 1 ? 'style="grid-column:1/-1"' : '' ?>>
            <div class="card-header" style="flex-wrap:wrap; gap:1rem;">
                <h2 class="card-title">
                    <?= $idRol === 1 ? 'Mis Facturas' : 'Todas las Facturas' ?>
                </h2>
                <div style="display:flex; gap:.75rem; align-items:center; flex-wrap:wrap;">
                    <form method="GET" action="<?= $bp ?>/facturas" style="display:flex; gap:.5rem; align-items:center;">
                        <input type="date" name="fecha" value="<?= htmlspecialchars($fechaFiltro) ?>" class="form-control" style="padding:.4rem; width:auto; font-size:.85rem;">
                        <button type="submit" class="btn btn-secondary btn-sm">Filtrar</button>
                        <a href="<?= $bp ?>/facturas?fecha=" class="btn btn-secondary btn-sm" title="Ver todas">Todas</a>
                    </form>
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

<script>
// Paginación de facturas
(function() {
    const tbody = document.querySelector('#tabla-facturas tbody');
    if (tbody) {
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const rowsPerPage = 15;
        const totalPages = Math.ceil(rows.length / rowsPerPage);

        if (totalPages > 1) {
            const paginationControls = document.createElement('div');
            paginationControls.className = 'pagination-controls no-print';
            paginationControls.style.display = 'flex';
            paginationControls.style.justifyContent = 'center';
            paginationControls.style.gap = '.5rem';
            paginationControls.style.marginTop = '1rem';

            function renderPage(page) {
                rows.forEach((row, index) => {
                    row.style.display = (index >= (page - 1) * rowsPerPage && index < page * rowsPerPage) ? '' : 'none';
                });
                
                paginationControls.innerHTML = '';
                
                const btnPrev = document.createElement('button');
                btnPrev.className = 'btn btn-secondary btn-sm';
                btnPrev.textContent = '« Ant';
                btnPrev.disabled = page === 1;
                btnPrev.onclick = () => renderPage(page - 1);
                paginationControls.appendChild(btnPrev);
                
                const span = document.createElement('span');
                span.style.padding = '0.3rem 0.75rem';
                span.style.fontSize = '0.85rem';
                span.style.color = 'var(--text-secondary)';
                span.textContent = `Pág ${page} de ${totalPages}`;
                paginationControls.appendChild(span);
                
                const btnNext = document.createElement('button');
                btnNext.className = 'btn btn-secondary btn-sm';
                btnNext.textContent = 'Sig »';
                btnNext.disabled = page === totalPages;
                btnNext.onclick = () => renderPage(page + 1);
                paginationControls.appendChild(btnNext);
            }

            document.querySelector('#card-listado-facturas .table-wrapper').after(paginationControls);
            renderPage(1);
        }
    }
})();
</script>

<?php include __DIR__ . '/../layout/foot.php'; ?>
