<?php
/**
 * views/admin/crear_usuario.php
 * Formulario para que el Admin cree un nuevo usuario desde cero.
 * - Doctor nuevo: llena datos médicos completos + credenciales (doble INSERT atómico)
 * - Doctor fantasma (?ghost=ID): solo llena credenciales (el perfil ya existe en DB)
 * - Secretaria / Admin: solo credenciales de acceso
 */
$bp          = $bp          ?? '';
$old         = $old         ?? [];
$especialidades = $especialidades ?? [];
$ghostDoctor = $ghostDoctor ?? null; // Datos del doctor fantasma si viene ?ghost=ID

// Determinar si es modo fantasma
$isGhost     = !empty($ghostDoctor) && !empty($ghostDoctor['id_doctor']);
$ghostId     = $isGhost ? (int)$ghostDoctor['id_doctor'] : ($old['ghost_id_doctor'] ?? 0);

// En modo fantasma pre-llenamos del doctor existente; en modo normal del $old
$preNombre    = $old['pNombre']   ?? $ghostDoctor['primer_nombre']   ?? '';
$preSnombre   = $old['sNombre']   ?? $ghostDoctor['segundo_nombre']  ?? '';
$preApellido  = $old['pApellido'] ?? $ghostDoctor['primer_apellido'] ?? '';
$preSapellido = $old['sApellido'] ?? $ghostDoctor['segundo_apellido'] ?? '';
$preEsp       = $old['esp']       ?? $ghostDoctor['especialidad']    ?? '';
$preCorreo    = $old['correo']    ?? '';
$preNid       = $old['nid']       ?? '';
?>
<?php include dirname(__DIR__) . '/layout/head.php'; ?>

<div class="page-header">
    <div style="display:flex;align-items:center;gap:1rem;">
        <a href="<?= $bp ?>/admin" class="btn btn-secondary btn-sm" id="btn-volver-admin">← Volver</a>
        <div>
            <h1><?= $isGhost ? '🔐 Crear Acceso para Doctor' : '➕ Crear Usuario' ?></h1>
            <p style="color:var(--text-secondary);margin-top:.2rem;">
                <?= $isGhost
                    ? 'Asigna credenciales de acceso al sistema para este doctor existente.'
                    : 'Crea un Doctor (con perfil médico completo), una Secretaria o un Administrador.' ?>
            </p>
        </div>
    </div>
</div>

<?php if (!empty($flashError)): ?>
    <div class="alert alert-error" role="alert" style="margin-bottom:1.5rem;">
        ⚠️ <?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<?php if ($isGhost): ?>
<!-- Banner de modo fantasma -->
<div style="background:rgba(6,182,212,.1);border:1.5px solid rgba(6,182,212,.35);border-radius:var(--radius-md);
            padding:.9rem 1.2rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:.8rem;">
    <span style="font-size:1.5rem;">🩺</span>
    <div>
        <p style="font-weight:700;color:var(--color-primary);margin-bottom:.15rem;">
            <?= htmlspecialchars(trim("{$ghostDoctor['primer_nombre']} {$ghostDoctor['segundo_nombre']} {$ghostDoctor['primer_apellido']} {$ghostDoctor['segundo_apellido']}"), ENT_QUOTES, 'UTF-8') ?>
            <span style="display:inline-block;margin-left:.5rem;padding:.15rem .4rem;background:rgba(6,182,212,.15);border-radius:4px;font-size:.75rem;color:var(--color-primary);">ID Doctor: <?= (int)$ghostDoctor['id_doctor'] ?></span>
        </p>
        <p style="font-size:.8rem;color:var(--text-secondary);">
            <?= htmlspecialchars($ghostDoctor['especialidad'] ?? '', ENT_QUOTES, 'UTF-8') ?>
            · El perfil médico ya existe. Solo necesitas crear las credenciales de acceso.
        </p>
    </div>
</div>
<?php endif; ?>

<div class="card" style="max-width:720px;">

    <form method="POST" action="<?= $bp ?>/admin/crear-usuario" id="form-crear-usuario" novalidate>

        <!-- Campo oculto para el id del doctor fantasma (0 = doctor nuevo) -->
        <input type="hidden" name="ghost_id_doctor" value="<?= (int)$ghostId ?>">

        <?php if ($isGhost): ?>
        <!-- En modo fantasma: tipo forzado a Doctor, no se puede cambiar -->
        <input type="hidden" name="tipo_rol" value="2">
        <div style="margin-bottom:1.5rem;padding:.65rem 1.1rem;display:inline-flex;align-items:center;gap:.5rem;
                    background:rgba(6,182,212,.08);border:1px solid rgba(6,182,212,.25);border-radius:var(--radius-md);
                    font-size:.88rem;color:var(--color-primary);">
            🩺 Doctor (tipo fijo)
        </div>
        <?php else: ?>
        <!-- ── Tipo de rol ── -->
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
        <?php endif; ?>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- Panel DOCTOR — datos médicos completos                          -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div id="panel-doctor" style="display:<?= ($isGhost || (int)($old['tipoRol'] ?? 0) === 2) ? 'block' : 'none' ?>;margin-bottom:1.25rem;
             background:rgba(6,182,212,.06);border:1px solid rgba(6,182,212,.25);
             border-radius:var(--radius-md);padding:1.25rem;">

            <?php if (!$isGhost): // En modo fantasma, los datos médicos ya existen; no se editan ?>
            <p style="font-size:.78rem;font-weight:600;color:var(--color-primary);text-transform:uppercase;
                      letter-spacing:.06em;margin-bottom:1rem;">
                📋 Perfil Médico
            </p>

            <!-- Nombres -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                <div>
                    <label class="form-label" for="primer_nombre" style="display:block;margin-bottom:.4rem;font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                        Primer Nombre <span style="color:#f87171;">*</span>
                    </label>
                    <input type="text" id="primer_nombre" name="primer_nombre"
                           class="form-input" placeholder="Roberto"
                           value="<?= htmlspecialchars($preNombre, ENT_QUOTES, 'UTF-8') ?>"
                           style="width:100%;background:var(--bg-glass);border:1px solid var(--border-medium);border-radius:var(--radius-md);padding:.75rem 1rem;color:var(--text-primary);font-family:var(--font-base);font-size:.9rem;outline:none;">
                </div>
                <div>
                    <label class="form-label" for="segundo_nombre" style="display:block;margin-bottom:.4rem;font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                        Segundo Nombre
                    </label>
                    <input type="text" id="segundo_nombre" name="segundo_nombre"
                           class="form-input" placeholder="Antonio (opcional)"
                           value="<?= htmlspecialchars($preSnombre, ENT_QUOTES, 'UTF-8') ?>"
                           style="width:100%;background:var(--bg-glass);border:1px solid var(--border-medium);border-radius:var(--radius-md);padding:.75rem 1rem;color:var(--text-primary);font-family:var(--font-base);font-size:.9rem;outline:none;">
                </div>
            </div>

            <!-- Apellidos -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                <div>
                    <label class="form-label" for="primer_apellido" style="display:block;margin-bottom:.4rem;font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                        Primer Apellido <span style="color:#f87171;">*</span>
                    </label>
                    <input type="text" id="primer_apellido" name="primer_apellido"
                           class="form-input" placeholder="Salazar"
                           value="<?= htmlspecialchars($preApellido, ENT_QUOTES, 'UTF-8') ?>"
                           style="width:100%;background:var(--bg-glass);border:1px solid var(--border-medium);border-radius:var(--radius-md);padding:.75rem 1rem;color:var(--text-primary);font-family:var(--font-base);font-size:.9rem;outline:none;">
                </div>
                <div>
                    <label class="form-label" for="segundo_apellido" style="display:block;margin-bottom:.4rem;font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                        Segundo Apellido
                    </label>
                    <input type="text" id="segundo_apellido" name="segundo_apellido"
                           class="form-input" placeholder="Méndez (opcional)"
                           value="<?= htmlspecialchars($preSapellido, ENT_QUOTES, 'UTF-8') ?>"
                           style="width:100%;background:var(--bg-glass);border:1px solid var(--border-medium);border-radius:var(--radius-md);padding:.75rem 1rem;color:var(--text-primary);font-family:var(--font-base);font-size:.9rem;outline:none;">
                </div>
            </div>

            <!-- Especialidad -->
            <div style="margin-bottom:1rem;">
                <label class="form-label" for="especialidad" style="display:block;margin-bottom:.4rem;font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                    Especialidad <span style="color:#f87171;">*</span>
                </label>
                <style>
                    #especialidad option {
                        background-color: #1e293b; /* Fondo oscuro sólido */
                        color: #f8fafc; /* Texto claro */
                    }
                </style>
                <select name="especialidad" id="especialidad"
                        style="width:100%;background:var(--bg-glass);border:1px solid var(--border-medium);
                               border-radius:var(--radius-md);padding:.75rem 1rem;
                               color:var(--text-primary);font-family:var(--font-base);font-size:.9rem;outline:none;">
                    <option value="">— Selecciona una especialidad —</option>
                    <?php foreach ($especialidades as $esp): ?>
                    <option value="<?= htmlspecialchars($esp, ENT_QUOTES, 'UTF-8') ?>"
                        <?= $preEsp === $esp ? 'selected' : '' ?>>
                        <?= htmlspecialchars($esp, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php else: ?>
            <!-- Modo fantasma: perfil médico bloqueado (solo lectura) -->
            <p style="font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;
                      letter-spacing:.06em;margin-bottom:.75rem;">
                📋 Perfil Médico (datos de DB — no editables aquí)
            </p>
            <!-- Campos hidden para que el servidor reciba los datos del perfil -->
            <input type="hidden" name="primer_nombre"   value="<?= htmlspecialchars($preNombre,    ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="segundo_nombre"  value="<?= htmlspecialchars($preSnombre,   ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="primer_apellido" value="<?= htmlspecialchars($preApellido,  ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="segundo_apellido" value="<?= htmlspecialchars($preSapellido, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="especialidad"    value="<?= htmlspecialchars($preEsp,       ENT_QUOTES, 'UTF-8') ?>">
            <?php endif; ?>

            <hr style="border:none;border-top:1px solid var(--border-medium);margin:1.25rem 0;">
            <p style="font-size:.78rem;font-weight:600;color:var(--color-primary);text-transform:uppercase;
                      letter-spacing:.06em;margin-bottom:1rem;">
                🔐 Credenciales de Acceso
            </p>

            <!-- Correo doctor -->
            <div style="margin-bottom:1rem;">
                <label class="form-label" for="doc-correo" style="display:block;margin-bottom:.4rem;font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                    Correo electrónico <span style="color:#f87171;">*</span>
                </label>
                <input type="email" id="doc-correo" name="correo"
                       class="form-input" placeholder="doctor@clinik.com"
                       value="<?= htmlspecialchars($preCorreo, ENT_QUOTES, 'UTF-8') ?>"
                       style="width:100%;background:var(--bg-glass);border:1px solid var(--border-medium);border-radius:var(--radius-md);padding:.75rem 1rem;color:var(--text-primary);font-family:var(--font-base);font-size:.9rem;outline:none;">
            </div>

            <!-- Cédula doctor -->
            <div style="margin-bottom:1rem;">
                <label class="form-label" for="doc-nid" style="display:block;margin-bottom:.4rem;font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                    Cédula / ID de acceso <span style="color:#f87171;">*</span>
                </label>
                <input type="text" id="doc-nid" name="numero_identificacion"
                       class="form-input" placeholder="DOC-XXX"
                       value="<?= htmlspecialchars($old['nid'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       style="width:100%;background:var(--bg-glass);border:1px solid var(--border-medium);border-radius:var(--radius-md);padding:.75rem 1rem;color:var(--text-primary);font-family:var(--font-base);font-size:.9rem;outline:none;">
            </div>

            <!-- Contraseña doctor -->
            <div>
                <label class="form-label" for="doc-pass" style="display:block;margin-bottom:.4rem;font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                    Contraseña inicial <span style="color:#f87171;">*</span>
                </label>
                <input type="password" id="doc-pass" name="contrasena" minlength="8"
                       class="form-input" placeholder="Mínimo 8 caracteres"
                       style="width:100%;background:var(--bg-glass);border:1px solid var(--border-medium);border-radius:var(--radius-md);padding:.75rem 1rem;color:var(--text-primary);font-family:var(--font-base);font-size:.9rem;outline:none;">
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- Panel STAFF — Secretaria / Admin                                -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div id="panel-staff" style="display:none;margin-bottom:1.25rem;">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div>
                    <label class="form-label" for="adm-nombre" style="display:block;margin-bottom:.4rem;font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                        Nombre <span style="color:#f87171;">*</span>
                    </label>
                    <input type="text" id="adm-nombre" name="nombre"
                           class="form-input" placeholder="Ana"
                           value="<?= htmlspecialchars($old['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           style="width:100%;background:var(--bg-glass);border:1px solid var(--border-medium);border-radius:var(--radius-md);padding:.75rem 1rem;color:var(--text-primary);font-family:var(--font-base);font-size:.9rem;outline:none;">
                </div>
                <div>
                    <label class="form-label" for="adm-apellido" style="display:block;margin-bottom:.4rem;font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                        Apellido <span style="color:#f87171;">*</span>
                    </label>
                    <input type="text" id="adm-apellido" name="apellido"
                           class="form-input" placeholder="González"
                           value="<?= htmlspecialchars($old['apellido'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           style="width:100%;background:var(--bg-glass);border:1px solid var(--border-medium);border-radius:var(--radius-md);padding:.75rem 1rem;color:var(--text-primary);font-family:var(--font-base);font-size:.9rem;outline:none;">
                </div>
            </div>

            <div style="margin-top:1rem;">
                <label class="form-label" for="adm-nid" style="display:block;margin-bottom:.4rem;font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                    Cédula / ID de acceso <span style="color:#f87171;">*</span>
                </label>
                <input type="text" id="adm-nid" name="numero_identificacion"
                       class="form-input" placeholder="SEC-002"
                       value="<?= htmlspecialchars($old['nid'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       style="width:100%;background:var(--bg-glass);border:1px solid var(--border-medium);border-radius:var(--radius-md);padding:.75rem 1rem;color:var(--text-primary);font-family:var(--font-base);font-size:.9rem;outline:none;">
            </div>

            <div style="margin-top:1rem;">
                <label class="form-label" for="adm-correo" style="display:block;margin-bottom:.4rem;font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                    Correo electrónico <span style="color:#f87171;">*</span>
                </label>
                <input type="email" id="adm-correo" name="correo"
                       class="form-input" placeholder="usuario@clinik.com"
                       value="<?= htmlspecialchars($old['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       style="width:100%;background:var(--bg-glass);border:1px solid var(--border-medium);border-radius:var(--radius-md);padding:.75rem 1rem;color:var(--text-primary);font-family:var(--font-base);font-size:.9rem;outline:none;">
            </div>

            <div style="margin-top:1rem;">
                <label class="form-label" for="adm-pass" style="display:block;margin-bottom:.4rem;font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                    Contraseña inicial <span style="color:#f87171;">*</span>
                </label>
                <input type="password" id="adm-pass" name="contrasena" minlength="8"
                       class="form-input" placeholder="Mínimo 8 caracteres"
                       style="width:100%;background:var(--bg-glass);border:1px solid var(--border-medium);border-radius:var(--radius-md);padding:.75rem 1rem;color:var(--text-primary);font-family:var(--font-base);font-size:.9rem;outline:none;">
                <p style="font-size:.72rem;color:var(--text-muted);margin-top:.3rem;">
                    El usuario deberá cambiarla en su primer acceso.
                </p>
            </div>
        </div>

        <!-- Aviso cuando no se ha elegido rol -->
        <div id="panel-hint" style="padding:1.5rem;text-align:center;color:var(--text-muted);font-size:.85rem;">
            👆 Selecciona el tipo de usuario para ver el formulario.
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
    const radios    = document.querySelectorAll('input[name="tipo_rol"]');
    const panelDoc  = document.getElementById('panel-doctor');
    const panelSta  = document.getElementById('panel-staff');
    const panelHint = document.getElementById('panel-hint');
    const labels    = document.querySelectorAll('.tipo-label');

    function updatePanel() {
        const val = document.querySelector('input[name="tipo_rol"]:checked')?.value 
                 || document.querySelector('input[name="tipo_rol"][type="hidden"]')?.value;
                 
        if(panelDoc) panelDoc.style.display  = (val === '2') ? 'block' : 'none';
        if(panelSta) panelSta.style.display  = (val === '3' || val === '4') ? 'block' : 'none';
        if(panelHint) panelHint.style.display = (!val) ? 'block' : 'none';

        // Habilitar/deshabilitar inputs según panel visible
        // Esto evita que inputs ocultos con el mismo name= sobreescriban los visibles
        panelDoc.querySelectorAll('input, select').forEach(el => {
            el.disabled = (val !== '2');
        });
        panelSta.querySelectorAll('input, select').forEach(el => {
            el.disabled = (val !== '3' && val !== '4');
        });

        labels.forEach(l => {
            const r = l.querySelector('input[type=radio]');
            l.style.borderColor = r.checked ? 'var(--color-primary)' : 'var(--border-medium)';
            l.style.background  = r.checked ? 'rgba(6,182,212,.08)' : 'var(--bg-glass)';
        });
    }

    radios.forEach(r => r.addEventListener('change', updatePanel));
    updatePanel(); // Estado inicial — también deshabilita el panel oculto desde el principio

    // Prevenir doble submit
    document.getElementById('form-crear-usuario').addEventListener('submit', function(e) {
        const val = document.querySelector('input[name="tipo_rol"]:checked')?.value
                 || document.querySelector('input[name="tipo_rol"][type="hidden"]')?.value;
        if (!val) {
            e.preventDefault();
            alert('Selecciona el tipo de usuario antes de continuar.');
            return;
        }
        const btn = document.getElementById('btn-guardar-usuario');
        if(btn) {
            btn.textContent = 'Creando...';
            btn.disabled = true;
        }
    });
})();
</script>


<?php include dirname(__DIR__) . '/layout/foot.php'; ?>
