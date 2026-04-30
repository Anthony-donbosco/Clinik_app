<?php
/**
 * views/historial/atender_cita.php
 * Formulario que el Doctor llena para registrar el historial medico de una cita.
 * Variables: $cita (array), $bp (string BASE_PATH)
 */
$pageTitle = 'Atender Cita — Clini-K';
include __DIR__ . '/../layout/head.php';

$flashError   = $_SESSION['flash_error']   ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);
?>

<!-- ── Topbar ──────────────────────────────────────────────────────── -->
<header class="topbar">
    <h1 class="topbar-title">📋 Registrar Historial Médico</h1>
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
        <p class="text-secondary">Completa el diagnóstico para marcar la cita como Atendida.</p>
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
                <h3 class="card-title">🩺 Información de la Cita</h3>
                <span class="badge estado-pendiente" style="padding:.35rem .9rem">Pendiente de atender</span>
            </div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Paciente</span>
                    <span class="info-value"><?= htmlspecialchars($cita['nombre_paciente'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Identificación</span>
                    <span class="info-value"><code><?= htmlspecialchars($cita['numeroIdentificacion'], ENT_QUOTES, 'UTF-8') ?></code></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Fecha</span>
                    <span class="info-value"><?= date('d/m/Y', strtotime($cita['fecha'])) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Hora</span>
                    <span class="info-value"><?= htmlspecialchars(substr($cita['hora'], 0, 5), ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Especialidad</span>
                    <span class="info-value"><?= htmlspecialchars($cita['especialidad'] ?? 'General', ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>
        </div>
    </section>

    <!-- ── Formulario historial ───────────────────────────────────── -->
    <section class="content-grid" style="grid-template-columns:1fr;">
        <div class="card" id="card-form-historial">
            <div class="card-header">
                <h3 class="card-title">📝 Registro de Consulta</h3>
            </div>

            <form method="POST"
                  action="<?= $bp ?>/historial/guardar"
                  id="form-historial"
                  class="historial-form"
                  novalidate>

                <input type="hidden" name="id_cita" value="<?= (int) $cita['id_cita'] ?>">

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
                    ></textarea>
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
                    ></textarea>
                    <small class="form-hint">Mínimo 5 caracteres. Máximo 2000.</small>
                </div>

                <!-- Notas adicionales -->
                <div class="form-group">
                    <label class="form-label" for="notas">Notas Adicionales</label>
                    <textarea
                        id="notas"
                        name="notas"
                        class="form-control"
                        rows="3"
                        maxlength="2000"
                        placeholder="Observaciones adicionales, alergias conocidas, seguimiento recomendado..."
                    ></textarea>
                    <small class="form-hint">Opcional. Máximo 2000 caracteres.</small>
                </div>

                <!-- Aviso bloqueo -->
                <div class="alert alert-info" id="alert-bloqueo-aviso">
                    🔒 <strong>Importante:</strong> Una vez guardado, el historial no podrá modificarse transcurridas 24 horas.
                </div>

                <!-- Acciones -->
                <div class="form-actions">
                    <a href="<?= $bp ?>/historial" class="btn btn-secondary" id="btn-cancelar-historial">Cancelar</a>
                    <button type="submit" class="btn btn-primary" id="btn-guardar-historial">
                        ✅ Guardar y Marcar como Atendida
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
.alert-info {
    background: rgba(6,182,212,.08);
    border: 1px solid rgba(6,182,212,.25);
    border-radius: var(--radius-md);
    color: var(--color-primary);
    padding: .75rem 1rem;
    font-size: .85rem;
}
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
document.getElementById('form-historial').addEventListener('submit', function(e) {
    const diag = document.getElementById('diagnostico').value.trim();
    const trat = document.getElementById('tratamiento').value.trim();
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
    }
});
</script>

<?php include __DIR__ . '/../layout/foot.php'; ?>
