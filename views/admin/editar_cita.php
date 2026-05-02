<?php
/**
 * views/admin/editar_cita.php
 * Vista de edición detallada para citas desde el Admin.
 */
$bp = $bp ?? '';

$idCita     = (int)$cita['id_cita'];
$idDoctor   = (int)$cita['id_doctor'];
$idPaciente = (int)$cita['id_paciente'];
$idEstado   = (int)$cita['id_estado'];
$estado     = htmlspecialchars($cita['estado'] ?? '—');
$fecha      = date('Y-m-d', strtotime($cita['fecha']));
$hora       = substr($cita['hora'], 0, 5);

$puedeAprobar  = ($idEstado === 1);
$puedeRechazar = in_array($idEstado, [1, 2]);
$puedeCancelar = in_array($idEstado, [1, 2]);
$puedeReagendar = !in_array($idEstado, [4, 5]);

function estadoCitaClassAdminDetail(string $estado): string {
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
    .detail-card {
        background: var(--bg-glass);
        border: 1px solid var(--border-medium);
        border-radius: var(--radius-md);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    .detail-label {
        font-size: .75rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: .2rem;
    }
    .detail-value {
        font-size: 1rem;
        color: var(--text-primary);
        font-weight: 500;
    }
    
    /* Reagendar Form Styles */
    .form-control {
        width: 100%;
        background: rgba(255,255,255,0.06);
        border: 1px solid var(--border-medium);
        border-radius: var(--radius-md);
        padding: .8rem 1rem;
        color: var(--text-primary);
        font-size: .95rem;
        font-family: var(--font-base);
        outline: none;
    }
    .form-control:disabled { opacity: .5; cursor: not-allowed; }
    
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
        text-align: center;
        transition: all .2s;
    }
    .horario-btn:hover:not(.ocupado) {
        border-color: var(--color-primary);
        background: rgba(6,182,212,.12);
        color: var(--color-primary);
    }
    .horario-btn.ocupado {
        opacity: .35; cursor: not-allowed; text-decoration: line-through;
    }
    .horario-btn.seleccionado {
        border-color: var(--color-primary);
        background: rgba(6,182,212,.2);
        color: var(--color-primary);
        box-shadow: 0 0 0 2px rgba(6,182,212,.3);
    }
    .action-bar {
        display: flex; gap: .75rem; flex-wrap: wrap; margin-top: 1.5rem;
        padding-top: 1.5rem; border-top: 1px solid var(--border-medium);
    }
</style>

<div class="page-header">
    <div style="display:flex;align-items:center;gap:1rem;">
        <a href="<?= $bp ?>/admin/citas" class="btn btn-secondary btn-sm">← Volver</a>
        <div>
            <h1>✏️ Editar Cita #<?= $idCita ?></h1>
            <p style="color:var(--text-secondary);margin-top:.2rem;">
                Información detallada, estado y reagendamiento.
            </p>
        </div>
    </div>
</div>

<div class="detail-card">
    <h2 style="font-size:1.1rem; margin-bottom:1rem; color:var(--color-primary);">📄 Detalles Actuales</h2>
    <div class="detail-grid">
        <div>
            <div class="detail-label">Paciente</div>
            <div class="detail-value"><?= htmlspecialchars($cita['nombre_paciente'] ?? '—') ?></div>
        </div>
        <div>
            <div class="detail-label">Doctor</div>
            <div class="detail-value">Dr. <?= htmlspecialchars($cita['nombre_doctor'] ?? '—') ?></div>
        </div>
        <div>
            <div class="detail-label">Fecha</div>
            <div class="detail-value"><?= date('d/m/Y', strtotime($fecha)) ?></div>
        </div>
        <div>
            <div class="detail-label">Hora</div>
            <div class="detail-value"><strong><?= $hora ?></strong></div>
        </div>
        <div>
            <div class="detail-label">Estado Actual</div>
            <div class="detail-value" id="badge-estado">
                <span class="<?= estadoCitaClassAdminDetail($estado) ?>"><?= $estado ?></span>
            </div>
        </div>
        <div>
            <div class="detail-label">Especialidad</div>
            <div class="detail-value"><?= htmlspecialchars($cita['especialidad'] ?? '—') ?></div>
        </div>
    </div>

    <!-- Barra de acciones rápidas de estado -->
    <div class="action-bar" id="status-actions">
        <?php if ($puedeAprobar): ?>
            <button class="btn btn-sm" id="btn-aprobar"
                    style="background:rgba(16,185,129,.15);color:#34d399;border:1px solid rgba(16,185,129,.3);">
                ✔ Aprobar Cita
            </button>
        <?php endif; ?>
        <?php if ($puedeRechazar): ?>
            <button class="btn btn-sm" id="btn-rechazar"
                    style="background:rgba(245,158,11,.15);color:#fbbf24;border:1px solid rgba(245,158,11,.3);">
                ✖ Rechazar Cita
            </button>
        <?php endif; ?>
        <?php if ($puedeCancelar): ?>
            <button class="btn btn-sm btn-danger" id="btn-cancelar">
                Cancelar Cita
            </button>
        <?php endif; ?>
        
        <?php if (!$puedeAprobar && !$puedeRechazar && !$puedeCancelar): ?>
            <p style="color:var(--text-muted);font-size:.85rem;">No hay acciones de estado disponibles (la cita ya fue atendida o cancelada).</p>
        <?php endif; ?>
    </div>
</div>

<?php if ($puedeReagendar): ?>
<div class="card" id="card-reagendar">
    <div class="card-header">
        <h2 class="card-title">🔄 Reagendar Cita</h2>
    </div>
    <div style="padding: 1.25rem;">
        <p style="font-size:.85rem; color:var(--text-secondary); margin-bottom:1rem;">
            Al reagendar, la cita volverá al estado <strong>Pendiente</strong> automáticamente.
        </p>

        <input type="hidden" id="id_doctor" value="<?= $idDoctor ?>">
        
        <div style="margin-bottom:1rem; max-width:300px;">
            <label for="input-fecha" class="detail-label">Nueva Fecha</label>
            <input type="date" id="input-fecha" class="form-control" min="<?= date('Y-m-d') ?>">
        </div>

        <div style="margin-bottom:1.5rem;">
            <label class="detail-label">Nuevo Horario</label>
            <input type="hidden" id="hora-seleccionada" value="">
            <div id="horarios-container">
                <p id="horarios-hint" style="color:var(--text-muted);font-size:.85rem;">
                    Selecciona una fecha para ver la disponibilidad del doctor.
                </p>
                <p id="horarios-loading" style="display:none; color:var(--text-muted);font-size:.85rem;">⏳ Consultando...</p>
                <div id="horarios-grid"></div>
            </div>
        </div>

        <button id="btn-reagendar" class="btn btn-primary" disabled>
            Guardar Nuevo Horario
        </button>
    </div>
</div>
<?php endif; ?>

<script>
(function() {
    const BASE_URL = window.CLINIK_BASE_URL || '/clinik_app';
    const ID_CITA = <?= $idCita ?>;
    
    // --- LÓGICA DE ESTADO (Aprobar/Rechazar/Cancelar) ---
    function cambiarEstado(accion) {
        const url = accion === 'cancelar' ? `${BASE_URL}/api/citas/cancelar` : `${BASE_URL}/api/citas/${accion}`;
        
        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
            body: JSON.stringify({ id_cita: ID_CITA })
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                alert('✅ ' + data.mensaje);
                location.reload(); // Recargamos para actualizar vista
            } else {
                alert('⚠ ' + (data.mensaje || 'Error al actualizar.'));
            }
        })
        .catch(() => alert('⚠ Error de conexión.'));
    }

    const btnAprobar = document.getElementById('btn-aprobar');
    const btnRechazar = document.getElementById('btn-rechazar');
    const btnCancelar = document.getElementById('btn-cancelar');

    if(btnAprobar) btnAprobar.addEventListener('click', () => {
        if(confirm('¿Aprobar esta cita?')) cambiarEstado('aprobar');
    });
    if(btnRechazar) btnRechazar.addEventListener('click', () => {
        if(confirm('¿Rechazar esta cita?')) cambiarEstado('rechazar');
    });
    if(btnCancelar) btnCancelar.addEventListener('click', () => {
        if(confirm('¿Cancelar esta cita de forma definitiva?')) cambiarEstado('cancelar');
    });

    // --- LÓGICA DE REAGENDAR ---
    const inputFecha = document.getElementById('input-fecha');
    const inputHora = document.getElementById('hora-seleccionada');
    const idDoctor = document.getElementById('id_doctor')?.value;
    const gridHorarios = document.getElementById('horarios-grid');
    const hint = document.getElementById('horarios-hint');
    const loading = document.getElementById('horarios-loading');
    const btnReagendar = document.getElementById('btn-reagendar');

    if (inputFecha) {
        inputFecha.addEventListener('change', async function() {
            const fecha = this.value;
            if (!fecha || !idDoctor) return;
            
            gridHorarios.innerHTML = '';
            inputHora.value = '';
            btnReagendar.disabled = true;
            hint.style.display = 'none';
            loading.style.display = 'block';

            try {
                const url = `${BASE_URL}/api/citas/disponibilidad?id_doctor=${idDoctor}&fecha=${fecha}`;
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await res.json();
                
                loading.style.display = 'none';

                if (!res.ok || data.error) {
                    gridHorarios.innerHTML = `<p style="color:#f87171;font-size:.85rem;">⚠ ${data.error}</p>`;
                    return;
                }

                const horarios = data.horarios || [];
                const disponibles = horarios.filter(h => h.disponible);
                
                if (disponibles.length === 0) {
                    gridHorarios.innerHTML = '<p style="color:var(--text-muted);font-size:.85rem;">No hay horarios disponibles en esta fecha.</p>';
                    return;
                }

                horarios.forEach(h => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'horario-btn' + (h.disponible ? '' : ' ocupado');
                    btn.textContent = h.hora;
                    btn.disabled = !h.disponible;
                    
                    if (h.disponible) {
                        btn.addEventListener('click', () => {
                            document.querySelectorAll('.horario-btn').forEach(b => b.classList.remove('seleccionado'));
                            btn.classList.add('seleccionado');
                            inputHora.value = h.hora;
                            btnReagendar.disabled = false;
                        });
                    }
                    gridHorarios.appendChild(btn);
                });
            } catch (err) {
                loading.style.display = 'none';
                gridHorarios.innerHTML = '<p style="color:#f87171;font-size:.85rem;">⚠ Error de conexión.</p>';
            }
        });
    }

    if (btnReagendar) {
        btnReagendar.addEventListener('click', function() {
            const f = inputFecha.value;
            const h = inputHora.value;
            if(!f || !h) return;

            if(!confirm(`¿Reagendar cita para el ${f} a las ${h}?`)) return;

            btnReagendar.disabled = true;
            btnReagendar.textContent = 'Reagendando...';

            fetch(`${BASE_URL}/api/citas/reagendar`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                body: JSON.stringify({ id_cita: ID_CITA, fecha: f, hora: h })
            })
            .then(r => r.json())
            .then(data => {
                if(data.ok) {
                    alert('✅ ' + data.mensaje);
                    location.href = `${BASE_URL}/admin/citas`;
                } else {
                    alert('⚠ ' + (data.mensaje || 'Error al reagendar.'));
                    btnReagendar.disabled = false;
                    btnReagendar.textContent = 'Guardar Nuevo Horario';
                }
            })
            .catch(() => {
                alert('⚠ Error de conexión.');
                btnReagendar.disabled = false;
                btnReagendar.textContent = 'Guardar Nuevo Horario';
            });
        });
    }
})();
</script>

<?php include dirname(__DIR__) . '/layout/foot.php'; ?>
