<?php
/**
 * views/historial/editar_historial.php
 * Formulario para el Doctor para editar un historial medico reciente.
 * Variables: $historial (array), $bp (string)
 */
$pageTitle = 'Editar Historial Médico — Clini-K';
include __DIR__ . '/../layout/head.php';

$flashError   = $_SESSION['flash_error']   ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);
?>

<!-- ── Topbar ──────────────────────────────────────────────────────── -->
<header class="topbar">
    <h1 class="topbar-title">✏️ Editar Historial Médico</h1>
    <div class="topbar-actions">
        <a href="<?= $bp ?>/historial" class="btn btn-secondary btn-sm" id="btn-volver-historial">← Volver</a>
        <div class="user-avatar" title="<?= htmlspecialchars($_SESSION['nombre'] ?? '') ?>">
            <?= strtoupper(substr($_SESSION['nombre'] ?? 'D', 0, 1)) ?>
        </div>
    </div>
</header>

<!-- ── Contenido principal ─────────────────────────────────────────── -->
<main class="page-content" id="atender-cita-page">

    <div class="page-header">
        <h2>Consulta Médica</h2>
        <p class="text-secondary">Modifica los detalles del historial médico del paciente.</p>
    </div>

    <?php if ($flashError): ?>
        <div class="alert alert-error" role="alert" id="alert-error-historial">
            ⚠️ <?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- ── Info de la cita ────────────────────────────────────────── -->
    <section class="content-grid" style="grid-template-columns:1fr;">
        <div class="card" id="card-info-cita">
            <div class="card-header">
                <h3 class="card-title">📝 Datos del Historial</h3>
            </div>
            
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Paciente</span>
                    <span class="info-value"><strong><?= htmlspecialchars($historial['nombre_paciente'] ?? 'Desconocido', ENT_QUOTES, 'UTF-8') ?></strong></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Fecha original de Cita</span>
                    <span class="info-value"><?= date('d/m/Y', strtotime($historial['fecha_cita'] ?? 'now')) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Hora original</span>
                    <span class="info-value"><?= htmlspecialchars(substr($historial['hora_cita'] ?? '', 0, 5)) ?></span>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Formulario de edición -->
        <div class="card" id="card-formulario-atencion">
            <form method="POST" action="<?= $bp ?>/historial/actualizar" id="form-atender-cita">
                <input type="hidden" name="id_historial" value="<?= (int) ($historial['id_historial'] ?? 0) ?>">

                <!-- Diagnóstico -->
                <div class="form-group">
                    <label class="form-label" for="diagnostico">
                        Diagnóstico <span class="required">*</span>
                    </label>
                    <textarea
                        id="diagnostico"
                        name="diagnostico"
                        class="form-control"
                        rows="4"
                        minlength="10"
                        maxlength="2000"
                        placeholder="Describa el diagnóstico clínico del paciente..."
                        required
                    ><?= htmlspecialchars($historial['diagnostico'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    <small class="form-hint">Mínimo 10 caracteres. Máximo 2000.</small>
                </div>

                <!-- Tratamiento -->
                <div class="form-group">
                    <label class="form-label" for="tratamiento">
                        Tratamiento <span class="required">*</span>
                    </label>
                    <textarea
                        id="tratamiento"
                        name="tratamiento"
                        class="form-control"
                        rows="3"
                        minlength="5"
                        maxlength="2000"
                        placeholder="Indique el tratamiento recetado, medicamentos, dosis..."
                        required
                    ><?= htmlspecialchars($historial['tratamiento'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    <small class="form-hint">Mínimo 5 caracteres. Máximo 2000.</small>
                </div>

                <!-- Receta / Indicaciones -->
                <div class="form-group">
                    <label class="form-label" for="notas">
                        Receta / Indicaciones <span class="required">*</span>
                    </label>
                    <textarea
                        id="notas"
                        name="notas"
                        class="form-control"
                        rows="3"
                        minlength="5"
                        maxlength="2000"
                        placeholder="Ej: Amoxicilina 500mg cada 8h por 7 días. Reposo absoluto 3 días..."
                        required
                    ><?= htmlspecialchars($historial['notas'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    <small class="form-hint">Obligatorio. Incluye medicamentos, dosis y cuidados en casa. Máx. 2000 caracteres.</small>
                </div>

                <!-- Acciones -->
                <div class="form-actions">
                    <a href="<?= $bp ?>/historial" class="btn btn-secondary" id="btn-cancelar-historial">Cancelar</a>
                    <button type="submit" class="btn btn-primary" id="btn-guardar-historial">
                        💾 Guardar Cambios
                    </button>
                </div>

            </form>
        </div>
    </section>

</main>

<style>
/* ── Estilos extra para la vista de atender cita ── */
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem 1.5rem;
    padding: .25rem 0 .5rem;
}
.info-item { display: flex; flex-direction: column; gap: .25rem; }
.info-label { font-size: .72rem; text-transform: uppercase; letter-spacing: .06em; color: var(--text-muted); font-weight: 600; }
.info-value { font-size: .95rem; color: var(--text-primary); font-weight: 500; }

.historial-form { display: flex; flex-direction: column; gap: 1.25rem; }
.form-group { display: flex; flex-direction: column; gap: .4rem; }
.form-label { font-size: .85rem; font-weight: 600; color: var(--text-secondary); }
.form-label .required { color: #f87171; }
.form-control {
    background: var(--bg-elevated);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-md);
    color: var(--text-primary);
    font-family: inherit;
    font-size: .9rem;
    padding: .65rem .9rem;
    resize: vertical;
    transition: border-color .2s, box-shadow .2s;
}
.form-control:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(6,182,212,.15);
}
.form-hint { font-size: .72rem; color: var(--text-muted); }
.alert-error {
    background: rgba(239,68,68,.08);
    border: 1px solid rgba(239,68,68,.25);
    border-radius: var(--radius-md);
    color: #f87171;
    padding: .75rem 1rem;
    font-size: .875rem;
    margin-bottom: 1rem;
}
.form-actions {
    display: flex;
    gap: .75rem;
    justify-content: flex-end;
    padding-top: .5rem;
    border-top: 1px solid var(--border-subtle);
}
</style>

<script>
/* Validacion cliente antes de enviar */
document.getElementById('form-atender-cita').addEventListener('submit', function(e) {
    const diag  = document.getElementById('diagnostico').value.trim();
    const trat  = document.getElementById('tratamiento').value.trim();
    const notas = document.getElementById('notas').value.trim();
    if (diag.length < 10) {
        e.preventDefault();
        alert('El diagnóstico debe tener al menos 10 caracteres.');
        document.getElementById('diagnostico').focus();
        return;
    }
    if (trat.length < 5) {
        e.preventDefault();
        alert('El tratamiento debe tener al menos 5 caracteres.');
        document.getElementById('tratamiento').focus();
        return;
    }
    if (notas.length < 5) {
        e.preventDefault();
        alert('La receta / indicaciones son obligatorias (mínimo 5 caracteres).');
        document.getElementById('notas').focus();
        return;
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const errorMsg = "<?= addslashes($_SESSION['flash_error'] ?? '') ?>";
    if (errorMsg) {
        alert(errorMsg);
    }
});
</script>

<?php include __DIR__ . '/../layout/foot.php'; ?>
