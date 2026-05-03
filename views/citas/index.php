<?php
/**
 * views/citas/index.php
 * Vista unificada del módulo de Citas (comportamiento diferente por rol).
 * Variables: $citas, $doctores, $pacientes, $flashOk, $flashError
 */
$idRol    = (int) ($_SESSION['id_rol'] ?? 0);
$pageTitle = 'Gestión de Citas — Clini-K';
include __DIR__ . '/../layout/head.php';

// Helper: clase de badge por nombre de estado
function estadoCitaClass(string $estado): string {
    return match(strtolower(trim($estado))) {
        'pendiente'  => 'badge estado-pendiente',
        'aprobada'   => 'badge estado-aprobada',
        'atendida'   => 'badge estado-atendida',
        'cancelada'  => 'badge estado-cancelada',
        default      => 'badge badge-muted',
    };
}
?>
<style>
    /* ── Formulario de nueva cita ──────────────────────────── */
    .form-cita { display: grid; grid-template-columns: 1fr; gap: 1.25rem; }

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
        margin-bottom: .45rem;
    }
    .form-control {
        width: 100%;
        background: rgba(255,255,255,0.06);
        border: 1px solid var(--border-medium);
        border-radius: var(--radius-md);
        padding: .8rem 1rem;
        color: var(--text-primary);
        font-size: .95rem;
        font-family: var(--font-base);
        transition: border-color .2s, box-shadow .2s;
        outline: none;
        appearance: none;
    }
    .form-control:focus {
        border-color: var(--border-focus);
        box-shadow: 0 0 0 3px rgba(6,182,212,.15);
        background: rgba(6,182,212,.04);
    }
    .form-control:disabled { opacity: .5; cursor: not-allowed; }
    .form-control option   { background: #1e293b; color: #f1f5f9; }

    /* Horarios grid */
    #horarios-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
        gap: .6rem;
        margin-top: .5rem;
    }
    .horario-btn {
        padding: .6rem .4rem;
        border-radius: var(--radius-md);
        border: 1.5px solid var(--border-medium);
        background: var(--bg-glass);
        color: var(--text-primary);
        font-size: .82rem; font-weight: 600;
        cursor: pointer;
        transition: all .2s;
        text-align: center;
    }
    .horario-btn:hover:not(.ocupado) {
        border-color: var(--color-primary);
        background: rgba(6,182,212,.12);
        color: var(--color-primary);
    }
    .horario-btn.ocupado {
        opacity: .35; cursor: not-allowed;
        text-decoration: line-through;
    }
    .horario-btn.seleccionado {
        border-color: var(--color-primary);
        background: rgba(6,182,212,.2);
        color: var(--color-primary);
        box-shadow: 0 0 0 2px rgba(6,182,212,.3);
    }
    #horarios-loading { color: var(--text-muted); font-size: .875rem; padding: .75rem 0; }
    #form-submit-area { grid-column: 1 / -1; }

    /* Cancelar botón en tabla */
    .btn-cancelar { font-size: .78rem; padding: .3rem .75rem; }
    .btn-accion   { font-size: .78rem; padding: .3rem .75rem; }
    /* Botones de estado para Secretaria */
    .btn-success {
        background: rgba(16,185,129,.15);
        color: #34d399;
        border: 1px solid rgba(16,185,129,.3);
    }
    .btn-success:hover { background: rgba(16,185,129,.3); }
    .btn-warning {
        background: rgba(245,158,11,.15);
        color: #fbbf24;
        border: 1px solid rgba(245,158,11,.3);
    }
    .btn-warning:hover { background: rgba(245,158,11,.3); }
</style>

<!-- ── Topbar ──────────────────────────────────────────── -->
<header class="topbar">
    <h1 class="topbar-title">
        <?= match($idRol) { 2 => 'Mi Agenda', 1 => 'Mis Citas', default => 'Gestión de Citas' } ?>
    </h1>
    <div class="topbar-actions">
        <button class="notification-btn" id="notif-bell-btn" aria-label="Notificaciones">
            🔔 <span class="notification-dot" id="notif-dot"></span>
        </button>
        <div class="user-avatar"><?= strtoupper(substr($_SESSION['nombre'] ?? 'U', 0, 1)) ?></div>
    </div>
</header>

<main class="page-content" id="citas-page">

    <!-- ── Mensajes flash ──────────────────────────────── -->
    <?php if ($flashOk): ?>
        <div class="alert alert-success" id="flash-ok-msg" role="status">
            <span>✅</span> <?= htmlspecialchars($flashOk) ?>
        </div>
    <?php endif; ?>
    <?php if ($flashError): ?>
        <div class="alert alert-error" id="flash-error-msg" role="alert">
            <span>⚠</span> <?= htmlspecialchars($flashError) ?>
        </div>
    <?php endif; ?>

    <div class="content-grid custom-layout" style="align-items: start;">

        <!-- ════════════════════════════════════════════════
             FORMULARIO NUEVA CITA (Secretaria y Paciente)
        ═══════════════════════════════════════════════════ -->
        <?php if ($idRol !== 2): // Doctor no puede crear citas ?>
        <div class="card" id="card-nueva-cita">
            <div class="card-header">
                <h2 class="card-title">
                    <?= $idRol === 3 ? '📅 Registrar Nueva Cita' : '📅 Solicitar Cita' ?>
                </h2>
            </div>

            <!-- Cambia la línea del formulario por esta -->
            <form id="form-nueva-cita" method="POST" action="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/citas/crear" novalidate>
                <div class="form-cita">

                    <?php if ($idRol === 3): // Secretaria elige al paciente ?>
                    <div class="form-group">
                        <label for="select-paciente">Paciente</label>
                        <select id="select-paciente" name="id_paciente" class="form-control" required>
                            <option value="">— Selecciona un paciente —</option>
                            <?php foreach ($pacientes as $p): ?>
                            <option value="<?= $p['id_paciente'] ?>">
                                <?= htmlspecialchars($p['nombre_completo']) ?>
                                (<?= htmlspecialchars($p['numeroIdentificacion']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="select-doctor">Doctor / Especialidad</label>
                        <select id="select-doctor" name="id_doctor" class="form-control" required>
                            <option value="">— Selecciona un doctor —</option>
                            <?php foreach ($doctores as $d): ?>
                            <option value="<?= $d['id_doctor'] ?>">
                                Dr. <?= htmlspecialchars($d['nombre_completo']) ?>
                                — <?= htmlspecialchars($d['especialidad']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="input-fecha">Fecha de la Cita</label>
                        <input type="date"
                               id="input-fecha"
                               name="fecha"
                               class="form-control"
                               min="<?= date('Y-m-d') ?>"
                               required
                               disabled>
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Horario Disponible</label>
                        <input type="hidden" id="hora-seleccionada" name="hora" value="">
                        <div id="horarios-container">
                            <p id="horarios-loading" style="display:none">⏳ Consultando disponibilidad...</p>
                            <div id="horarios-grid"></div>
                            <p id="horarios-hint" class="text-muted" style="font-size:.85rem; margin-top:.5rem">
                                Selecciona un doctor y una fecha para ver los horarios disponibles.
                            </p>
                        </div>
                    </div>

                    <div id="form-submit-area" style="display:none">
                        <button type="submit" id="btn-confirmar-cita" class="btn btn-primary w-full" disabled>
                            Confirmar Cita
                        </button>
                    </div>

                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- ════════════════════════════════════════════════
             TABLA DE CITAS (todos los roles)
        ═══════════════════════════════════════════════════ -->
        <div class="card" id="card-listado-citas" <?= $idRol === 2 ? 'style="grid-column:1/-1"' : '' ?>>
            <div class="card-header" style="flex-wrap:wrap; gap:1rem;">
                <h2 class="card-title">
                    <?= match($idRol) { 2 => '🗓 Mi Agenda', 1 => 'Mis Citas', default => 'Todas las Citas' } ?>
                </h2>
                <div style="display:flex; gap:.75rem; align-items:center; flex-wrap:wrap;">
                    <form method="GET" action="<?= defined('BASE_PATH') ? BASE_PATH : '' ?>/citas" style="display:flex; gap:.5rem; align-items:center;">
                        <input type="date" name="fecha" value="<?= htmlspecialchars($fechaFiltro) ?>" class="form-control" style="padding:.4rem; width:auto; font-size:.85rem;">
                        <button type="submit" class="btn btn-secondary btn-sm">Filtrar</button>
                        <a href="<?= defined('BASE_PATH') ? BASE_PATH : '' ?>/citas?fecha=" class="btn btn-secondary btn-sm" title="Ver todas">Todas</a>
                    </form>
                    <span class="badge badge-primary"><?= count($citas) ?> registro(s)</span>
                </div>
            </div>

            <?php if (empty($citas)): ?>
                <div class="table-empty">
                    <span>📭</span>
                    <?= $idRol === 2 ? 'No tienes citas en tu agenda.' : 'No hay citas registradas.' ?>
                </div>
            <?php else: ?>
            <div class="table-wrapper">
                <table id="tabla-citas">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <?php if ($idRol !== 1): ?><th>Paciente</th><?php endif; ?>
                            <?php if ($idRol !== 2): ?><th>Doctor</th><th>Especialidad</th><?php endif; ?>
                            <th>Estado</th>
                            <?php if ($idRol !== 2): ?><th>Acción</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($citas as $c): ?>
                        <?php
                            $idEstado     = (int)$c['id_estado'];
                            $puedeCancelar = in_array($idEstado, [1, 2]);
                            $puedeAprobar  = ($idRol === 3 && $idEstado === 1); // Solo Secretaria sobre Pendientes
                            $puedeRechazar = ($idRol === 3 && in_array($idEstado, [1, 2])); // Solo Secretaria
                        ?>
                        <tr id="fila-cita-<?= $c['id_cita'] ?>">
                            <td><?= (int)$c['id_cita'] ?></td>
                            <td><?= date('d/m/Y', strtotime($c['fecha'])) ?></td>
                            <td><strong><?= htmlspecialchars(substr($c['hora'], 0, 5)) ?></strong></td>
                            <?php if ($idRol !== 1): ?>
                            <td><?= htmlspecialchars($c['nombre_paciente'] ?? '—') ?></td>
                            <?php endif; ?>
                            <?php if ($idRol !== 2): ?>
                            <td><?= htmlspecialchars($c['nombre_doctor'] ?? '—') ?></td>
                            <td><span class="badge badge-primary"><?= htmlspecialchars($c['especialidad'] ?? '—') ?></span></td>
                            <?php endif; ?>
                            <td><span class="<?= estadoCitaClass($c['estado']) ?>"><?= htmlspecialchars($c['estado']) ?></span></td>
                            <?php if ($idRol !== 2): ?>
                            <td>
                                <div style="display:flex; gap:.35rem; flex-wrap:wrap;">
                                <?php if ($puedeAprobar): ?>
                                <button class="btn btn-success btn-sm btn-accion"
                                        id="btn-aprobar-<?= $c['id_cita'] ?>"
                                        onclick="cambiarEstadoCita(<?= $c['id_cita'] ?>, 'aprobar')"
                                        title="Aprobar esta cita">✔ Aprobar</button>
                                <?php endif; ?>
                                <?php if ($puedeRechazar): ?>
                                <button class="btn btn-warning btn-sm btn-accion"
                                        id="btn-rechazar-<?= $c['id_cita'] ?>"
                                        onclick="cambiarEstadoCita(<?= $c['id_cita'] ?>, 'rechazar')"
                                        title="Rechazar esta cita">✖ Rechazar</button>
                                <?php endif; ?>
                                <?php if ($puedeCancelar): ?>
                                <button class="btn btn-danger btn-sm btn-cancelar"
                                        id="btn-cancelar-<?= $c['id_cita'] ?>"
                                        data-id="<?= $c['id_cita'] ?>"
                                        onclick="confirmarCancelacion(<?= $c['id_cita'] ?>)"
                                        title="Cancelar esta cita">
                                    Cancelar
                                </button>
                                <?php endif; ?>
                                <?php if (!$puedeCancelar && !$puedeAprobar && !$puedeRechazar): ?>
                                <span class="text-muted" style="font-size:.8rem">—</span>
                                <?php endif; ?>
                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

    </div><!-- /.content-grid -->
</main>

<script>
/**
 * Módulo de Citas — JavaScript Vanilla
 */
    (function () {
        'use strict';

        const BASE_URL = window.CLINIK_BASE_URL || '/clinik_app';

        const selectDoctor = document.getElementById('select-doctor');
        const inputFecha   = document.getElementById('input-fecha');
        const gridHorarios = document.getElementById('horarios-grid');
        const loading      = document.getElementById('horarios-loading');
        const hint         = document.getElementById('horarios-hint');
        const inputHora    = document.getElementById('hora-seleccionada');
        const submitArea   = document.getElementById('form-submit-area');
        const btnConfirmar = document.getElementById('btn-confirmar-cita');

        if (selectDoctor) {
            selectDoctor.addEventListener('change', function () {
                if (inputFecha) {
                    inputFecha.disabled = !this.value;
                    inputFecha.value    = '';
                    resetHorarios();
                }
            });
        }

        if (inputFecha) {
            inputFecha.addEventListener('change', async function () {
                const idDoctor = selectDoctor?.value;
                const fecha    = this.value;
                if (!idDoctor || !fecha) return;
                await cargarHorarios(idDoctor, fecha);
            });
        }

        async function cargarHorarios(idDoctor, fecha) {
            resetHorarios();
            if (loading) loading.style.display = 'block';
            if (hint)    hint.style.display    = 'none';

            try {
                const endpoint = `${BASE_URL}/api/citas/disponibilidad?id_doctor=${idDoctor}&fecha=${fecha}`;
                console.log("Petición AJAX enviada a:", endpoint);
                // CORRECCIÓN: Se añade BASE_URL a la petición
                const url = `${BASE_URL}/api/citas/disponibilidad?id_doctor=${idDoctor}&fecha=${fecha}`;
                console.log("Llamando a:", url); // Esto te ayudará a ver la URL real en la consola

                const res = await fetch(endpoint, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                const data = await res.json();

                if (!res.ok || data.error) {
                    mostrarErrorHorario(data.error || 'Error al consultar disponibilidad.');
                    return;
                }

                renderHorarios(data.horarios || []);

            } catch (err) {
                mostrarErrorHorario('No se pudo conectar con el servidor.');
            } finally {
                if (loading) loading.style.display = 'none';
            }
        }

        function renderHorarios(horarios) {
            if (!gridHorarios) return;
            gridHorarios.innerHTML = '';
            const disponibles = horarios.filter(h => h.disponible);
            if (disponibles.length === 0) {
                gridHorarios.innerHTML = '<p style="color:var(--text-muted);font-size:.875rem">No hay horarios disponibles.</p>';
                return;
            }
            horarios.forEach(h => {
                const btn = document.createElement('button');
                btn.type      = 'button';
                btn.className = 'horario-btn' + (h.disponible ? '' : ' ocupado');
                btn.textContent = h.hora;
                btn.disabled  = !h.disponible;
                if (h.disponible) {
                    btn.addEventListener('click', function () {
                        document.querySelectorAll('.horario-btn').forEach(b => b.classList.remove('seleccionado'));
                        btn.classList.add('seleccionado');
                        if (inputHora)    inputHora.value = h.hora;
                        if (submitArea)   submitArea.style.display = 'block';
                        if (btnConfirmar) btnConfirmar.disabled    = false;
                    });
                }
                gridHorarios.appendChild(btn);
            });
        }

        function mostrarErrorHorario(msg) {
            if (gridHorarios) gridHorarios.innerHTML = `<p style="color:var(--color-danger);font-size:.875rem">⚠ ${msg}</p>`;
        }

        function resetHorarios() {
            if (gridHorarios)  gridHorarios.innerHTML  = '';
            if (inputHora)     inputHora.value          = '';
            if (submitArea)    submitArea.style.display  = 'none';
            if (btnConfirmar)  btnConfirmar.disabled     = true;
            if (hint)          hint.style.display        = 'block';
        }

        window.confirmarCancelacion = function (idCita) {
            if (!confirm('¿Seguro que deseas cancelar?')) return;
            const btn = document.getElementById('btn-cancelar-' + idCita);
            if (btn) { btn.textContent = '...'; btn.disabled = true; }

            // CORRECCIÓN: Se añade BASE_URL aquí también
            fetch(`${BASE_URL}/api/citas/cancelar`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-Requested-With': 'XMLHttpRequest' 
                },
                credentials: 'same-origin',
                body: JSON.stringify({ id_cita: idCita }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.ok) {
                    const fila = document.getElementById('fila-cita-' + idCita);
                    if (fila) {
                        fila.style.opacity = '0.4';
                        setTimeout(() => fila.remove(), 500);
                    }
                } else {
                    alert('⚠ ' + (data.mensaje || 'Error al cancelar.'));
                    if (btn) { btn.textContent = 'Cancelar'; btn.disabled = false; }
                }
            });
        };

        /**
         * Aprobar o Rechazar cita (solo Secretaria)
         * accion: 'aprobar' | 'rechazar'
         */
        window.cambiarEstadoCita = function (idCita, accion) {
            const label = accion === 'aprobar' ? 'aprobar' : 'rechazar';
            if (!confirm(`¿Seguro que deseas ${label} esta cita?`)) return;

            const btnAp  = document.getElementById('btn-aprobar-'  + idCita);
            const btnRec = document.getElementById('btn-rechazar-' + idCita);
            const btnCan = document.getElementById('btn-cancelar-' + idCita);
            [btnAp, btnRec, btnCan].forEach(b => { if (b) { b.disabled = true; b.style.opacity = '.5'; } });

            fetch(`${BASE_URL}/api/citas/${accion}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ id_cita: idCita }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.ok) {
                    // Actualizar el badge de estado en la fila sin recargar página
                    const fila = document.getElementById('fila-cita-' + idCita);
                    if (fila) {
                        const badge = fila.querySelector('[class*="badge estado-"]');
                        if (badge) {
                            const nuevoEstado = accion === 'aprobar' ? 'Aprobada' : 'Rechazada';
                            const nuevaClase  = accion === 'aprobar' ? 'badge estado-aprobada' : 'badge badge-muted';
                            badge.textContent  = nuevoEstado;
                            badge.className    = nuevaClase;
                        }
                        // Quitar todos los botones de acción de esta fila
                        fila.querySelectorAll('.btn-accion, .btn-cancelar').forEach(b => b.remove());
                    }
                } else {
                    alert('⚠ ' + (data.mensaje || `Error al ${label}.`));
                    [btnAp, btnRec, btnCan].forEach(b => { if (b) { b.disabled = false; b.style.opacity = '1'; } });
                }
            })
            .catch(() => {
                alert('⚠ Error de conexión.');
                [btnAp, btnRec, btnCan].forEach(b => { if (b) { b.disabled = false; b.style.opacity = '1'; } });
            });
        };

        // Paginación de citas
        const tbody = document.querySelector('#tabla-citas tbody');
        if (tbody) {
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const rowsPerPage = 15;
            const totalPages = Math.ceil(rows.length / rowsPerPage);

            if (totalPages > 1) {
                const paginationControls = document.createElement('div');
                paginationControls.className = 'pagination-controls';
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

                document.querySelector('#card-listado-citas .table-wrapper').after(paginationControls);
                renderPage(1);
            }
        }
    })();
</script>

<?php include __DIR__ . '/../layout/foot.php'; ?>
