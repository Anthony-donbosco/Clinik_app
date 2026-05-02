<?php
/**
 * views/admin/editar_usuario.php
 * Formulario para editar un usuario Staff (Secretaria=3 o Admin=4).
 * Solo permite cambiar: nombre, apellido, correo, número de identificación.
 * Para cambiar la contraseña, el usuario debe gestionarlo desde su propio perfil.
 *
 * Variables: $usuario (array), $old (array), $flashError (string|null), $bp (string)
 */
$bp  = $bp  ?? '';
$old = $old ?? [];

// Prefiere $old (repoblación tras error) sobre los datos actuales del usuario
$editNombre   = $old['nombre']   ?? $usuario['nombre']                ?? '';
$editApellido = $old['apellido'] ?? $usuario['apellido']              ?? '';
$editCorreo   = $old['correo']   ?? $usuario['correo']                ?? '';
$editNid      = $old['nid']      ?? $usuario['numero_identificacion'] ?? '';

$idUsuario = (int) ($usuario['id_usuario'] ?? 0);
$editRolNombre = htmlspecialchars($usuario['nombre_rol'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<?php include dirname(__DIR__) . '/layout/head.php'; ?>

<div class="page-header">
    <div style="display:flex;align-items:center;gap:1rem;">
        <a href="<?= $bp ?>/admin" class="btn btn-secondary btn-sm" id="btn-volver-admin-edit-staff">← Volver</a>
        <div>
            <h1>✏️ Editar <?= $editRolNombre ?></h1>
            <p style="color:var(--text-secondary);margin-top:.2rem;">
                Actualiza nombre, correo o cédula de este usuario.
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
<div style="display:inline-flex;align-items:center;gap:.5rem;background:rgba(139,92,246,.1);
            border:1px solid rgba(139,92,246,.3);border-radius:var(--radius-md);
            padding:.45rem 1rem;margin-bottom:1.5rem;font-size:.82rem;color:#a78bfa;">
    👤 <?= $editRolNombre ?> · ID #<?= $idUsuario ?>
</div>

<div class="card" style="max-width:580px;">
    <form method="POST" action="<?= $bp ?>/admin/editar-usuario" id="form-editar-staff" novalidate>

        <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">

        <!-- Nombre y Apellido -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
            <div>
                <label class="form-label" for="staff-nombre"
                       style="display:block;margin-bottom:.4rem;font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                    Nombre <span style="color:#f87171;">*</span>
                </label>
                <input type="text" id="staff-nombre" name="nombre" required
                       class="form-input" placeholder="Ana"
                       value="<?= htmlspecialchars($editNombre, ENT_QUOTES, 'UTF-8') ?>"
                       style="width:100%;background:var(--bg-glass);border:1px solid var(--border-medium);border-radius:var(--radius-md);padding:.75rem 1rem;color:var(--text-primary);font-family:var(--font-base);font-size:.9rem;outline:none;">
            </div>
            <div>
                <label class="form-label" for="staff-apellido"
                       style="display:block;margin-bottom:.4rem;font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                    Apellido <span style="color:#f87171;">*</span>
                </label>
                <input type="text" id="staff-apellido" name="apellido" required
                       class="form-input" placeholder="González"
                       value="<?= htmlspecialchars($editApellido, ENT_QUOTES, 'UTF-8') ?>"
                       style="width:100%;background:var(--bg-glass);border:1px solid var(--border-medium);border-radius:var(--radius-md);padding:.75rem 1rem;color:var(--text-primary);font-family:var(--font-base);font-size:.9rem;outline:none;">
            </div>
        </div>

        <!-- Correo -->
        <div style="margin-bottom:1rem;">
            <label class="form-label" for="staff-correo"
                   style="display:block;margin-bottom:.4rem;font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                Correo electrónico <span style="color:#f87171;">*</span>
            </label>
            <input type="email" id="staff-correo" name="correo" required
                   class="form-input" placeholder="usuario@clinik.com"
                   value="<?= htmlspecialchars($editCorreo, ENT_QUOTES, 'UTF-8') ?>"
                   style="width:100%;background:var(--bg-glass);border:1px solid var(--border-medium);border-radius:var(--radius-md);padding:.75rem 1rem;color:var(--text-primary);font-family:var(--font-base);font-size:.9rem;outline:none;">
        </div>

        <!-- Cédula / ID -->
        <div style="margin-bottom:1.5rem;">
            <label class="form-label" for="staff-nid"
                   style="display:block;margin-bottom:.4rem;font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                Cédula / ID de acceso <span style="color:#f87171;">*</span>
            </label>
            <input type="text" id="staff-nid" name="numero_identificacion" required
                   class="form-input" placeholder="SEC-001"
                   value="<?= htmlspecialchars($editNid, ENT_QUOTES, 'UTF-8') ?>"
                   style="width:100%;background:var(--bg-glass);border:1px solid var(--border-medium);border-radius:var(--radius-md);padding:.75rem 1rem;color:var(--text-primary);font-family:var(--font-base);font-size:.9rem;outline:none;">
            <p style="font-size:.72rem;color:var(--text-muted);margin-top:.3rem;">
                Esta cédula es también el ID que el usuario usa para iniciar sesión.
            </p>
        </div>

        <div style="background:rgba(250,204,21,.07);border:1px solid rgba(250,204,21,.25);border-radius:var(--radius-md);
                    padding:.75rem 1rem;margin-bottom:1.5rem;font-size:.8rem;color:#fbbf24;">
            💡 Para cambiar la <strong>contraseña</strong>, el usuario debe hacerlo desde su propio inicio de sesión.
        </div>

        <div style="display:flex;gap:.75rem;">
            <button type="submit" id="btn-guardar-staff" class="btn btn-primary">
                💾 Guardar cambios
            </button>
            <a href="<?= $bp ?>/admin" class="btn btn-secondary">Cancelar</a>
        </div>

    </form>
</div>

<script>
document.getElementById('form-editar-staff').addEventListener('submit', function() {
    const btn = document.getElementById('btn-guardar-staff');
    btn.textContent = 'Guardando...';
    btn.disabled = true;
});
</script>

<?php include dirname(__DIR__) . '/layout/foot.php'; ?>
