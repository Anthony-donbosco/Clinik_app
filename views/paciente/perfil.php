<?php
/**
 * views/paciente/perfil.php
 * Perfil del paciente: ver datos + editar teléfono/correo.
 * Variables: $perfil (array), $bp (string), $flashOk, $flashError
 */
$pageTitle = 'Mi Perfil — Clini-K';
include __DIR__ . '/../layout/head.php';
?>
<style>
.perfil-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}
@media (max-width: 680px) { .perfil-grid { grid-template-columns: 1fr; } }

.perfil-avatar {
    width: 72px; height: 72px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--color-primary), #7c3aed);
    display: flex; align-items: center; justify-content: center;
    font-size: 2rem; font-weight: 800; color: #fff;
    margin-bottom: 1rem;
    box-shadow: 0 4px 16px rgba(6,182,212,.3);
}
.perfil-nombre {
    font-size: 1.35rem; font-weight: 700;
    color: var(--text-primary); margin-bottom: .2rem;
}
.perfil-id {
    font-size: .82rem; color: var(--text-muted);
    font-family: 'JetBrains Mono', monospace;
    background: rgba(255,255,255,.05);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-sm);
    padding: .2rem .6rem; display: inline-block;
}

.dato-readonly {
    background: rgba(255,255,255,.03);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-md);
    padding: .65rem .9rem;
    color: var(--text-secondary);
    font-size: .9rem;
    opacity: .75;
}
.dato-label {
    font-size: .75rem; font-weight: 600;
    text-transform: uppercase; letter-spacing: .06em;
    color: var(--text-muted); margin-bottom: .35rem;
    display: block;
}
.form-input {
    width: 100%;
    background: rgba(255,255,255,.06);
    border: 1px solid var(--border-medium);
    border-radius: var(--radius-md);
    padding: .7rem .9rem;
    color: var(--text-primary);
    font-size: .92rem;
    font-family: var(--font-base);
    transition: border-color .2s, box-shadow .2s;
    outline: none;
}
.form-input:focus {
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(6,182,212,.15);
    background: rgba(6,182,212,.04);
}
.campo-bloqueo {
    display: flex; align-items: center; gap: .5rem;
    font-size: .78rem; color: var(--text-muted);
    margin-top: .3rem;
}
.campo-bloqueo span { font-size: 1rem; }
</style>

<!-- ── Topbar ──────────────────────────────────────────── -->
<header class="topbar">
    <h1 class="topbar-title">👤 Mi Perfil</h1>
    <div class="topbar-actions">
        <button class="notification-btn" id="notif-bell-btn" aria-label="Notificaciones">
            🔔 <span class="notification-dot" id="notif-dot"></span>
        </button>
        <div class="user-avatar"><?= strtoupper(substr($_SESSION['nombre'] ?? 'P', 0, 1)) ?></div>
    </div>
</header>

<main class="page-content" id="perfil-page">

    <!-- ── Flash messages ─────────────────────────────── -->
    <?php if ($flashOk): ?>
        <div class="alert alert-success" id="flash-ok-perfil" role="status">
            ✅ <?= htmlspecialchars($flashOk) ?>
        </div>
    <?php endif; ?>
    <?php if ($flashError): ?>
        <div class="alert alert-error" id="flash-error-perfil" role="alert">
            ⚠ <?= htmlspecialchars($flashError) ?>
        </div>
    <?php endif; ?>

    <div class="content-grid" style="grid-template-columns: 320px 1fr; align-items: start;">

        <!-- ── Tarjeta identidad (solo lectura) ────────── -->
        <div class="card" id="card-identidad">
            <div class="card-header">
                <h2 class="card-title">🪪 Identidad</h2>
                <span class="badge badge-primary">Solo lectura</span>
            </div>

            <div style="padding:.5rem 0;">
                <div class="perfil-avatar">
                    <?= strtoupper(substr($perfil['primer_nombre'], 0, 1)) ?>
                </div>
                <div class="perfil-nombre">
                    <?= htmlspecialchars(
                        trim($perfil['primer_nombre'] . ' ' . ($perfil['segundo_nombre'] ?? '') . ' ' .
                             $perfil['primer_apellido'] . ' ' . ($perfil['segundo_apellido'] ?? ''))
                    ) ?>
                </div>
                <div class="perfil-id" style="margin-top:.5rem;">
                    <?= htmlspecialchars($perfil['numeroIdentificacion']) ?>
                </div>
            </div>

            <div style="display:flex; flex-direction:column; gap:.85rem; margin-top:1rem;">
                <div>
                    <span class="dato-label">Primer Nombre</span>
                    <div class="dato-readonly"><?= htmlspecialchars($perfil['primer_nombre']) ?></div>
                </div>
                <?php if ($perfil['segundo_nombre']): ?>
                <div>
                    <span class="dato-label">Segundo Nombre</span>
                    <div class="dato-readonly"><?= htmlspecialchars($perfil['segundo_nombre']) ?></div>
                </div>
                <?php endif; ?>
                <div>
                    <span class="dato-label">Primer Apellido</span>
                    <div class="dato-readonly"><?= htmlspecialchars($perfil['primer_apellido']) ?></div>
                </div>
                <?php if ($perfil['segundo_apellido']): ?>
                <div>
                    <span class="dato-label">Segundo Apellido</span>
                    <div class="dato-readonly"><?= htmlspecialchars($perfil['segundo_apellido']) ?></div>
                </div>
                <?php endif; ?>
                <div>
                    <span class="dato-label">Número de Identificación</span>
                    <div class="dato-readonly"><?= htmlspecialchars($perfil['numeroIdentificacion']) ?></div>
                </div>
            </div>

            <p class="campo-bloqueo" style="margin-top:1rem;">
                <span>🔒</span>
                Nombre y cédula son datos inmutables. Contacta a la secretaria para cambios.
            </p>
        </div>

        <!-- ── Formulario datos de contacto (editable) ── -->
        <div class="card" id="card-contacto">
            <div class="card-header">
                <h2 class="card-title">✏️ Datos de Contacto</h2>
                <span class="badge" style="background:rgba(16,185,129,.15); color:#34d399; border:1px solid rgba(16,185,129,.25);">
                    Editable
                </span>
            </div>

            <form id="form-perfil" method="POST"
                  action="<?= $bp ?>/paciente/perfil" novalidate>

                <div class="perfil-grid">

                    <!-- Correo -->
                    <div style="grid-column: 1 / -1;">
                        <label class="dato-label" for="input-correo">
                            Correo Electrónico <span style="color:#f87171;">*</span>
                        </label>
                        <input type="email"
                               id="input-correo"
                               name="correo"
                               class="form-input"
                               value="<?= htmlspecialchars($perfil['correo']) ?>"
                               required
                               autocomplete="email"
                               placeholder="tu@correo.com">
                    </div>

                    <!-- Teléfono -->
                    <div>
                        <label class="dato-label" for="input-telefono">Teléfono</label>
                        <input type="tel"
                               id="input-telefono"
                               name="telefono"
                               class="form-input"
                               value="<?= htmlspecialchars($perfil['telefono'] ?? '') ?>"
                               placeholder="7000-0000"
                               maxlength="15">
                    </div>

                </div>

                <div style="display:flex; justify-content:flex-end; margin-top:1.25rem; gap:.75rem;">
                    <a href="<?= $bp ?>/dashboard/paciente" class="btn btn-secondary"
                       id="btn-cancelar-perfil">Cancelar</a>
                    <button type="submit" class="btn btn-primary" id="btn-guardar-perfil">
                        💾 Guardar Cambios
                    </button>
                </div>

            </form>
        </div>

    </div><!-- /.content-grid -->
</main>

<script>
document.getElementById('form-perfil').addEventListener('submit', function (e) {
    const correo = document.getElementById('input-correo').value.trim();
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!re.test(correo)) {
        e.preventDefault();
        alert('Por favor ingresa un correo válido.');
        document.getElementById('input-correo').focus();
    }
});
</script>

<?php include __DIR__ . '/../layout/foot.php'; ?>
