<?php
/**
 * views/admin/editar_doctor.php
 * Formulario para que el Admin edite los datos de un doctor existente.
 * Actualiza AMBAS tablas (doctor + usuarios) en una sola operación atómica.
 *
 * Variables disponibles:
 *   $doctor         — array con datos del doctor (de UsuarioRepository::getDoctorById)
 *   $especialidades — array de strings con especialidades
 *   $old            — datos previos para repoblar en caso de error
 *   $flashError     — mensaje de error de sesión
 *   $bp             — base path
 */
$bp             = $bp             ?? '';
$old            = $old            ?? [];
$especialidades = $especialidades ?? [];

// Preferir datos de $old (repoblación tras error) sobre $doctor
$pNombre   = $old['pNombre']   ?? $doctor['primer_nombre']    ?? '';
$sNombre   = $old['sNombre']   ?? $doctor['segundo_nombre']   ?? '';
$pApellido = $old['pApellido'] ?? $doctor['primer_apellido']  ?? '';
$sApellido = $old['sApellido'] ?? $doctor['segundo_apellido'] ?? '';
$esp       = $old['esp']       ?? $doctor['especialidad']     ?? '';
$correo    = $old['correo']    ?? $doctor['correo']           ?? '';
$nid       = $old['nid']       ?? $doctor['numero_identificacion'] ?? '';

$idDoctor  = (int) ($doctor['id_doctor']  ?? 0);
$idUsuario = (int) ($doctor['id_usuario'] ?? 0);
?>
<?php include dirname(__DIR__) . '/layout/head.php'; ?>

<div class="page-header">
    <div style="display:flex;align-items:center;gap:1rem;">
        <a href="<?= $bp ?>/admin" class="btn btn-secondary btn-sm" id="btn-volver-admin-edit">← Volver</a>
        <div>
            <h1>✏️ Editar Doctor</h1>
            <p style="color:var(--text-secondary);margin-top:.2rem;">
                Los cambios se guardan en el perfil médico <em>y</em> en las credenciales de acceso de forma simultánea.
            </p>
        </div>
    </div>
</div>

<?php if (!empty($flashError)): ?>
    <div class="alert alert-error" role="alert" style="margin-bottom:1.5rem;">
        ⚠️ <?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<!-- Chip informativo -->
<div style="display:inline-flex;align-items:center;gap:.5rem;background:rgba(6,182,212,.1);
            border:1px solid rgba(6,182,212,.3);border-radius:var(--radius-md);
            padding:.45rem 1rem;margin-bottom:1.5rem;font-size:.82rem;color:var(--color-primary);">
    🩺 Doctor ID #<?= $idDoctor ?> · Usuario ID #<?= $idUsuario ?>
</div>

<div class="card" style="max-width:720px;">

    <form method="POST" action="<?= $bp ?>/admin/editar-doctor" id="form-editar-doctor" novalidate>

        <!-- IDs ocultos para el controlador -->
        <input type="hidden" name="id_doctor"  value="<?= $idDoctor ?>">
        <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">

        <!-- ── Perfil Médico ── -->
        <p style="font-size:.78rem;font-weight:600;color:var(--color-primary);text-transform:uppercase;
                  letter-spacing:.06em;margin-bottom:1rem;">
            Perfil Médico
        </p>

        <!-- Nombres -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
            <div>
                <label class="form-label" for="edit-pnombre" style="display:block;margin-bottom:.4rem;font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                    Primer Nombre <span style="color:#f87171;">*</span>
                </label>
                <input type="text" id="edit-pnombre" name="primer_nombre" required
                       class="form-input" placeholder="Roberto"
                       value="<?= htmlspecialchars($pNombre, ENT_QUOTES, 'UTF-8') ?>"
                       style="width:100%;background:var(--bg-glass);border:1px solid var(--border-medium);border-radius:var(--radius-md);padding:.75rem 1rem;color:var(--text-primary);font-family:var(--font-base);font-size:.9rem;outline:none;">
            </div>
            <div>
                <label class="form-label" for="edit-snombre" style="display:block;margin-bottom:.4rem;font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                    Segundo Nombre
                </label>
                <input type="text" id="edit-snombre" name="segundo_nombre"
                       class="form-input" placeholder="(opcional)"
                       value="<?= htmlspecialchars($sNombre, ENT_QUOTES, 'UTF-8') ?>"
                       style="width:100%;background:var(--bg-glass);border:1px solid var(--border-medium);border-radius:var(--radius-md);padding:.75rem 1rem;color:var(--text-primary);font-family:var(--font-base);font-size:.9rem;outline:none;">
            </div>
        </div>

        <!-- Apellidos -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
            <div>
                <label class="form-label" for="edit-papellido" style="display:block;margin-bottom:.4rem;font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                    Primer Apellido <span style="color:#f87171;">*</span>
                </label>
                <input type="text" id="edit-papellido" name="primer_apellido" required
                       class="form-input" placeholder="Salazar"
                       value="<?= htmlspecialchars($pApellido, ENT_QUOTES, 'UTF-8') ?>"
                       style="width:100%;background:var(--bg-glass);border:1px solid var(--border-medium);border-radius:var(--radius-md);padding:.75rem 1rem;color:var(--text-primary);font-family:var(--font-base);font-size:.9rem;outline:none;">
            </div>
            <div>
                <label class="form-label" for="edit-sapellido" style="display:block;margin-bottom:.4rem;font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                    Segundo Apellido
                </label>
                <input type="text" id="edit-sapellido" name="segundo_apellido"
                       class="form-input" placeholder="(opcional)"
                       value="<?= htmlspecialchars($sApellido, ENT_QUOTES, 'UTF-8') ?>"
                       style="width:100%;background:var(--bg-glass);border:1px solid var(--border-medium);border-radius:var(--radius-md);padding:.75rem 1rem;color:var(--text-primary);font-family:var(--font-base);font-size:.9rem;outline:none;">
            </div>
        </div>

        <!-- Especialidad -->
        <div style="margin-bottom:1.5rem;">
            <label class="form-label" for="edit-esp" style="display:block;margin-bottom:.4rem;font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                Especialidad <span style="color:#f87171;">*</span>
            </label>
            <style>
                #edit-esp option {
                    background-color: #1e293b; /* Fondo oscuro sólido */
                    color: #f8fafc; /* Texto claro */
                }
            </style>
            <select name="especialidad" id="edit-esp" required
                    style="width:100%;background:var(--bg-glass);border:1px solid var(--border-medium);
                           border-radius:var(--radius-md);padding:.75rem 1rem;
                           color:var(--text-primary);font-family:var(--font-base);font-size:.9rem;outline:none;">
                <option value="">— Selecciona —</option>
                <?php foreach ($especialidades as $e): ?>
                <option value="<?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?>"
                    <?= $esp === $e ? 'selected' : '' ?>>
                    <?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <hr style="border:none;border-top:1px solid var(--border-medium);margin:1.25rem 0;">

        <!-- ── Credenciales de acceso ── -->
        <p style="font-size:.78rem;font-weight:600;color:var(--color-primary);text-transform:uppercase;
                  letter-spacing:.06em;margin-bottom:1rem;">
            🔐 Credenciales de Acceso
        </p>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div>
                <label class="form-label" for="edit-correo" style="display:block;margin-bottom:.4rem;font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                    Correo electrónico <span style="color:#f87171;">*</span>
                </label>
                <input type="email" id="edit-correo" name="correo" required
                       class="form-input" placeholder="doctor@clinik.com"
                       value="<?= htmlspecialchars($correo, ENT_QUOTES, 'UTF-8') ?>"
                       style="width:100%;background:var(--bg-glass);border:1px solid var(--border-medium);border-radius:var(--radius-md);padding:.75rem 1rem;color:var(--text-primary);font-family:var(--font-base);font-size:.9rem;outline:none;">
            </div>
            <div>
                <label class="form-label" for="edit-nid" style="display:block;margin-bottom:.4rem;font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                    Cédula / ID de acceso <span style="color:#f87171;">*</span>
                </label>
                <input type="text" id="edit-nid" name="numero_identificacion" required
                       class="form-input" placeholder="DOC-001"
                       value="<?= htmlspecialchars($nid, ENT_QUOTES, 'UTF-8') ?>"
                       style="width:100%;background:var(--bg-glass);border:1px solid var(--border-medium);border-radius:var(--radius-md);padding:.75rem 1rem;color:var(--text-primary);font-family:var(--font-base);font-size:.9rem;outline:none;">
            </div>
        </div>

        <p style="font-size:.72rem;color:var(--text-muted);margin-top:.5rem;">
            💡 Para cambiar la contraseña, el doctor debe hacerlo desde su propio perfil.
        </p>

        <div style="margin-top:1.75rem;display:flex;gap:.75rem;">
            <button type="submit" id="btn-guardar-editar" class="btn btn-primary">
                💾 Guardar cambios
            </button>
            <a href="<?= $bp ?>/admin" class="btn btn-secondary">
                Cancelar
            </a>
        </div>

    </form>
</div>

<script>
document.getElementById('form-editar-doctor').addEventListener('submit', function() {
    const btn = document.getElementById('btn-guardar-editar');
    btn.textContent = 'Guardando...';
    btn.disabled = true;
});
</script>

<?php include dirname(__DIR__) . '/layout/foot.php'; ?>
