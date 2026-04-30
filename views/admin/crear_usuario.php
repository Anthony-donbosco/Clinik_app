<?php
/**
 * views/admin/crear_usuario.php
 * Formulario para que el Admin cree un nuevo usuario (Doctor / Secretaria / Admin).
 */
$bp  = $bp  ?? '';
$old = $old ?? [];
?>
<?php include dirname(__DIR__) . '/layout/head.php'; ?>

<div class="page-header">
    <div style="display:flex;align-items:center;gap:1rem;">
        <a href="<?= $bp ?>/admin" class="btn btn-secondary btn-sm" id="btn-volver-admin">← Volver</a>
        <div>
            <h1>➕ Crear Usuario</h1>
            <p style="color:var(--text-secondary);margin-top:.2rem;">
                Crea un Doctor, Secretaria u otro Administrador.
            </p>
        </div>
    </div>
</div>

<?php if (!empty($flashError)): ?>
    <div class="alert alert-error" role="alert" style="margin-bottom:1.5rem;">
        ⚠️ <?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<div class="card" style="max-width:620px;">

    <form method="POST" action="<?= $bp ?>/admin/crear-usuario" id="form-crear-usuario" novalidate>

        <!-- Tipo de rol -->
        <div class="form-group" style="margin-bottom:1.5rem;">
            <label class="form-label" style="display:block;margin-bottom:.6rem;font-size:.8rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                Tipo de usuario <span style="color:#f87171;">*</span>
            </label>
            <div style="display:flex;gap:.75rem;flex-wrap:wrap;" id="tipo-rol-group">
                <?php
                $tipos = [2 => '🩺 Doctor', 3 => '🗂️ Secretaria', 4 => '⚙️ Admin'];
                foreach ($tipos as $rid => $label):
                    $sel = ((int)($old['tipoRol'] ?? 0) === $rid) ? 'checked' : '';
                ?>
                <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;
                              background:var(--bg-glass);border:1px solid var(--border-medium);
                              border-radius:var(--radius-md);padding:.65rem 1.1rem;font-size:.88rem;
                              transition:border-color .15s,background .15s;"
                       class="tipo-label" id="label-rol-<?= $rid ?>">
                    <input type="radio" name="tipo_rol" value="<?= $rid ?>" <?= $sel ?>
                           id="radio-rol-<?= $rid ?>" style="accent-color:var(--color-primary);">
                    <?= $label ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Panel Doctor — selector de Doctor del catálogo -->
        <div id="panel-doctor" style="display:none;margin-bottom:1.25rem;
             background:rgba(6,182,212,.06);border:1px solid rgba(6,182,212,.2);
             border-radius:var(--radius-md);padding:1rem;">
            <label class="form-label" for="id_referencia" style="display:block;margin-bottom:.5rem;font-size:.8rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                Selecciona el Doctor del catálogo <span style="color:#f87171;">*</span>
            </label>
            <select name="id_referencia" id="id_referencia"
                    style="width:100%;background:var(--bg-glass);border:1px solid var(--border-medium);
                           border-radius:var(--radius-md);padding:.75rem 1rem;
                           color:var(--text-primary);font-family:var(--font-base);font-size:.9rem;outline:none;">
                <option value="">— Elige un doctor —</option>
                <?php foreach ($doctoresLibres as $d): ?>
                <option value="<?= (int)$d['id_doctor'] ?>"
                    <?= ((int)($old['id_referencia'] ?? 0) === (int)$d['id_doctor']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($d['nombre_completo'] . ' — ' . ($d['especialidad'] ?? 'Sin especialidad'), ENT_QUOTES, 'UTF-8') ?>
                </option>
                <?php endforeach; ?>
            </select>
            <?php if (empty($doctoresLibres)): ?>
            <p style="color:var(--color-warning);font-size:.78rem;margin-top:.5rem;">
                ⚠️ Todos los doctores del catálogo ya tienen usuario asignado.
            </p>
            <?php endif; ?>
        </div>

        <!-- Datos personales -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div>
                <label class="form-label" for="adm-nombre" style="display:block;margin-bottom:.4rem;font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                    Nombre <span style="color:#f87171;">*</span>
                </label>
                <input type="text" id="adm-nombre" name="nombre" required
                       class="form-input" placeholder="Roberto"
                       value="<?= htmlspecialchars($old['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       style="width:100%;background:var(--bg-glass);border:1px solid var(--border-medium);border-radius:var(--radius-md);padding:.75rem 1rem;color:var(--text-primary);font-family:var(--font-base);font-size:.9rem;outline:none;">
            </div>
            <div>
                <label class="form-label" for="adm-apellido" style="display:block;margin-bottom:.4rem;font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                    Apellido <span style="color:#f87171;">*</span>
                </label>
                <input type="text" id="adm-apellido" name="apellido" required
                       class="form-input" placeholder="Salazar"
                       value="<?= htmlspecialchars($old['apellido'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       style="width:100%;background:var(--bg-glass);border:1px solid var(--border-medium);border-radius:var(--radius-md);padding:.75rem 1rem;color:var(--text-primary);font-family:var(--font-base);font-size:.9rem;outline:none;">
            </div>
        </div>

        <div style="margin-top:1rem;">
            <label class="form-label" for="adm-nid" style="display:block;margin-bottom:.4rem;font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                Cédula / ID de acceso <span style="color:#f87171;">*</span>
            </label>
            <input type="text" id="adm-nid" name="numero_identificacion" required
                   class="form-input" placeholder="DOC-006"
                   value="<?= htmlspecialchars($old['nid'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   style="width:100%;background:var(--bg-glass);border:1px solid var(--border-medium);border-radius:var(--radius-md);padding:.75rem 1rem;color:var(--text-primary);font-family:var(--font-base);font-size:.9rem;outline:none;">
        </div>

        <div style="margin-top:1rem;">
            <label class="form-label" for="adm-correo" style="display:block;margin-bottom:.4rem;font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                Correo electrónico <span style="color:#f87171;">*</span>
            </label>
            <input type="email" id="adm-correo" name="correo" required
                   class="form-input" placeholder="usuario@clinik.com"
                   value="<?= htmlspecialchars($old['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   style="width:100%;background:var(--bg-glass);border:1px solid var(--border-medium);border-radius:var(--radius-md);padding:.75rem 1rem;color:var(--text-primary);font-family:var(--font-base);font-size:.9rem;outline:none;">
        </div>

        <div style="margin-top:1rem;">
            <label class="form-label" for="adm-pass" style="display:block;margin-bottom:.4rem;font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                Contraseña inicial <span style="color:#f87171;">*</span>
            </label>
            <input type="password" id="adm-pass" name="contrasena" required minlength="8"
                   class="form-input" placeholder="Mínimo 8 caracteres"
                   style="width:100%;background:var(--bg-glass);border:1px solid var(--border-medium);border-radius:var(--radius-md);padding:.75rem 1rem;color:var(--text-primary);font-family:var(--font-base);font-size:.9rem;outline:none;">
            <p style="font-size:.72rem;color:var(--text-muted);margin-top:.3rem;">
                El usuario deberá cambiarla en su primer acceso.
            </p>
        </div>

        <div style="margin-top:1.75rem;display:flex;gap:.75rem;">
            <button type="submit" id="btn-guardar-usuario" class="btn btn-primary">
                ✅ Crear usuario
            </button>
            <a href="<?= $bp ?>/admin" class="btn btn-secondary" id="btn-cancelar-crear">
                Cancelar
            </a>
        </div>

    </form>
</div>

<script>
(function() {
    const radios   = document.querySelectorAll('input[name="tipo_rol"]');
    const panelDoc = document.getElementById('panel-doctor');
    const labels   = document.querySelectorAll('.tipo-label');

    function updatePanel() {
        const val = document.querySelector('input[name="tipo_rol"]:checked')?.value;
        panelDoc.style.display = (val === '2') ? 'block' : 'none';
        labels.forEach(l => {
            const r = l.querySelector('input[type=radio]');
            l.style.borderColor   = r.checked ? 'var(--color-primary)' : 'var(--border-medium)';
            l.style.background    = r.checked ? 'rgba(6,182,212,.08)' : 'var(--bg-glass)';
        });
    }

    radios.forEach(r => r.addEventListener('change', updatePanel));
    updatePanel(); // Estado inicial

    // Prevenir doble submit
    document.getElementById('form-crear-usuario').addEventListener('submit', function() {
        const btn = document.getElementById('btn-guardar-usuario');
        btn.textContent = 'Creando...';
        btn.disabled = true;
    });
})();
</script>

<?php include dirname(__DIR__) . '/layout/foot.php'; ?>
