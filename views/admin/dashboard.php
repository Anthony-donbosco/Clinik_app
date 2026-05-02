<?php
/**
 * views/admin/dashboard.php
 * Panel principal del Administrador — lista de usuarios del sistema.
 *
 * La tabla `usuarios` es la ÚNICA fuente de verdad para el admin.
 * Para los doctores (id_rol=2), se enriquece con datos de la tabla `doctor`
 * via LEFT JOIN (especialidad) gracias al id_referencia.
 */
$bp = $bp ?? '';
$nombreAdmin = htmlspecialchars($_SESSION['nombre'] ?? 'Admin', ENT_QUOTES, 'UTF-8');

// Agrupar usuarios por rol para presentación
$grupos = [];
foreach ($usuarios as $u) {
    $grupos[$u['nombre_rol']][] = $u;
}
?>
<?php include dirname(__DIR__) . '/layout/head.php'; ?>

<div class="page-header">
    <h1>⚙️ Panel de Administración</h1>
    <p style="color:var(--text-secondary);margin-top:.25rem;">
        Gestión de usuarios del sistema. Bienvenido, <?= $nombreAdmin ?>.
    </p>
</div>

<?php if (!empty($flashOk)): ?>
    <div class="alert alert-success" role="alert" style="margin-bottom:1.5rem;">
        ✅ <?= htmlspecialchars($flashOk, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>
<?php if (!empty($flashError)): ?>
    <div class="alert alert-error" role="alert" style="margin-bottom:1.5rem;">
        ⚠️ <?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<!-- Acciones rápidas -->
<div style="display:flex;gap:.75rem;margin-bottom:1.5rem;flex-wrap:wrap;">
    <a href="<?= $bp ?>/admin/crear-usuario" class="btn btn-primary" id="btn-crear-usuario">
        ➕ Crear Usuario
    </a>
    <a href="<?= $bp ?>/admin/citas" class="btn btn-secondary" id="btn-ver-citas">
        📅 Supervisar Citas
    </a>
</div>

<!-- ── ALERTA: Doctores Fantasma ─────────────────────────────────────── -->
<?php if (!empty($doctoresFantasma)): ?>
<div style="background:rgba(251,191,36,.08);border:1.5px solid rgba(251,191,36,.4);
            border-radius:var(--radius-md);padding:1rem 1.25rem;margin-bottom:1.5rem;">
    <p style="font-weight:700;color:#fbbf24;margin-bottom:.5rem;">
        ⚠️ <?= count($doctoresFantasma) ?> Doctor(es) sin acceso al sistema ("Fantasmas")
    </p>
    <p style="font-size:.82rem;color:var(--text-secondary);margin-bottom:.75rem;">
        Estos doctores <strong>tienen perfil médico</strong> (visible para pacientes) pero
        <strong>no tienen credenciales de acceso</strong> (no pueden iniciar sesión).
        Crea su usuario para completar el vínculo.
    </p>
    <div style="display:flex;flex-wrap:wrap;gap:.5rem;">
        <?php foreach ($doctoresFantasma as $f): ?>
        <span style="display:inline-flex;align-items:center;gap:.6rem;background:rgba(251,191,36,.12);
                     border:1px solid rgba(251,191,36,.3);border-radius:var(--radius-md);
                     padding:.35rem .8rem;font-size:.8rem;">
            🩺 <?= htmlspecialchars(trim($f['nombre_completo']), ENT_QUOTES, 'UTF-8') ?>
            <span style="color:var(--text-muted);">·</span>
            <?= htmlspecialchars($f['especialidad'], ENT_QUOTES, 'UTF-8') ?>
            <a href="<?= $bp ?>/admin/crear-usuario?ghost=<?= (int)$f['id_doctor'] ?>"
               class="btn btn-sm btn-primary"
               style="padding:.2rem .6rem;font-size:.72rem;">+ Crear acceso</a>
        </span>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>


<!-- Nota arquitectónica visible solo en dev (puedes eliminarla en producción) -->
<div style="background:rgba(6,182,212,.07);border:1px solid rgba(6,182,212,.2);border-radius:var(--radius-md);
            padding:.75rem 1rem;margin-bottom:1.5rem;font-size:.8rem;color:var(--text-secondary);">
    ℹ️ <strong>Fuente única de verdad:</strong> Todos los usuarios se gestionan aquí.
    Los doctores creados aparecen automáticamente disponibles para que los pacientes agenden citas.
</div>

<!-- Tabla de usuarios por grupo -->
<?php foreach ($grupos as $rolNombre => $lista): ?>
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <h3 class="card-title"><?= htmlspecialchars($rolNombre, ENT_QUOTES, 'UTF-8') ?>s
            <span class="badge badge-primary" style="margin-left:.5rem;"><?= count($lista) ?></span>
        </h3>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Credencial / Cédula</th>
                    <?php if ($rolNombre === 'Doctor'): ?>
                    <th>Especialidad</th>
                    <?php endif; ?>
                    <th>Correo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lista as $u): ?>
                <tr id="usuario-row-<?= (int)$u['id_usuario'] ?>">
                    <td>
                        <div style="display:flex;align-items:center;gap:.6rem;">
                            <div class="user-avatar" style="width:30px;height:30px;font-size:.7rem;flex-shrink:0;">
                                <?= mb_strtoupper(mb_substr($u['nombre'], 0, 1) . mb_substr($u['apellido'], 0, 1)) ?>
                            </div>
                            <?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido'], ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    </td>
                    <td><code style="font-size:.78rem;color:var(--color-primary);">
                        <?= htmlspecialchars($u['numero_identificacion'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                    </code></td>
                    <?php if ($rolNombre === 'Doctor'): ?>
                    <td style="font-size:.84rem;color:var(--text-secondary);">
                        <?= htmlspecialchars($u['especialidad'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                    </td>
                    <?php endif; ?>
                    <td style="color:var(--text-secondary);font-size:.85rem;">
                        <?= htmlspecialchars($u['correo'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                    </td>
                    <td>
                        <?php if ((int)$u['id_estado'] === 8): ?>
                            <span class="badge badge-success">Activo</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ((int)$u['id_usuario'] !== (int)$_SESSION['id_usuario']): ?>
                            <div style="display:flex;gap:.4rem;flex-wrap:wrap;align-items:center;">

                                <!-- Botón Editar Doctor -->
                                <?php if ((int)$u['id_rol'] === 2 && !empty($u['id_doctor'])): ?>
                                <a href="<?= $bp ?>/admin/editar-doctor?id=<?= (int)$u['id_doctor'] ?>"
                                   class="btn btn-sm btn-secondary"
                                   id="btn-editar-<?= (int)$u['id_doctor'] ?>">
                                    ✏️ Editar
                                </a>
                                <?php endif; ?>

                                <!-- Botón Editar Staff (Secretaria=3, Admin=4) -->
                                <?php if (in_array((int)$u['id_rol'], [3, 4], true)): ?>
                                <a href="<?= $bp ?>/admin/editar-usuario?id=<?= (int)$u['id_usuario'] ?>"
                                   class="btn btn-sm btn-secondary"
                                   id="btn-editar-staff-<?= (int)$u['id_usuario'] ?>">
                                    ✏️ Editar
                                </a>
                                <?php endif; ?>

                                <!-- Desactivar / Reactivar -->
                                <?php if ((int)$u['id_estado'] === 8): ?>
                                    <form method="POST" action="<?= $bp ?>/admin/desactivar"
                                          id="form-desact-<?= (int)$u['id_usuario'] ?>"
                                          onsubmit="return confirm('¿Desactivar a <?= htmlspecialchars($u['nombre'], ENT_QUOTES) ?>?')"
                                          style="display:inline;">
                                        <input type="hidden" name="id_usuario" value="<?= (int)$u['id_usuario'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger"
                                                id="btn-desact-<?= (int)$u['id_usuario'] ?>">
                                            🚫 Desactivar
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="<?= $bp ?>/admin/reactivar"
                                          id="form-react-<?= (int)$u['id_usuario'] ?>"
                                          style="display:inline;">
                                        <input type="hidden" name="id_usuario" value="<?= (int)$u['id_usuario'] ?>">
                                        <button type="submit" class="btn btn-sm btn-success"
                                                id="btn-react-<?= (int)$u['id_usuario'] ?>">
                                            ✅ Reactivar
                                        </button>
                                    </form>
                                <?php endif; ?>

                            </div>
                        <?php else: ?>
                            <span style="font-size:.75rem;color:var(--text-muted);">Tu cuenta</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endforeach; ?>

<?php if (empty($usuarios)): ?>
<div class="card" style="text-align:center;padding:3rem;">
    <p style="color:var(--text-muted);">No hay usuarios registrados aún.</p>
    <a href="<?= $bp ?>/admin/crear-usuario" class="btn btn-primary" style="margin-top:1rem;">
        ➕ Crear primer usuario
    </a>
</div>
<?php endif; ?>

<?php include dirname(__DIR__) . '/layout/foot.php'; ?>
