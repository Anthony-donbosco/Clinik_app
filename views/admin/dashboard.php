<?php
/**
 * views/admin/dashboard.php
 * Panel principal del Administrador — lista de usuarios del sistema.
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
<div style="display:flex;gap:.75rem;margin-bottom:2rem;flex-wrap:wrap;">
    <a href="<?= $bp ?>/admin/crear-usuario" class="btn btn-primary" id="btn-crear-usuario">
        ➕ Crear Usuario
    </a>
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
                        <?= htmlspecialchars($u['numero_identificacion'], ENT_QUOTES, 'UTF-8') ?>
                    </code></td>
                    <td style="color:var(--text-secondary);font-size:.85rem;">
                        <?= htmlspecialchars($u['correo'], ENT_QUOTES, 'UTF-8') ?>
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
