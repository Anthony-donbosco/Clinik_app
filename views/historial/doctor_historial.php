<?php
/**
 * views/historial/doctor_historial.php
 * Lista de historiales medicos registrados por el doctor autenticado.
 * Variables: $historiales (array[]), $bp (string)
 */
$pageTitle = 'Mis Historiales — Clini-K';
include __DIR__ . '/../layout/head.php';

$flashError   = $_SESSION['flash_error']   ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);
?>

<!-- ── Topbar ──────────────────────────────────────────────────────── -->
<header class="topbar">
    <h1 class="topbar-title">Historiales Médicos</h1>
    <div class="topbar-actions">
        <a href="<?= $bp ?>/citas" class="btn btn-primary btn-sm" id="btn-ir-agenda">
            📅 Mi Agenda
        </a>
        <div class="user-avatar" title="<?= htmlspecialchars($_SESSION['nombre'] ?? '') ?>">
            <?= strtoupper(substr($_SESSION['nombre'] ?? 'D', 0, 1)) ?>
        </div>
    </div>
</header>

<!-- ── Contenido principal ─────────────────────────────────────────── -->
<main class="page-content" id="doctor-historial-page">

    <div class="page-header">
        <h2>Consultas Registradas</h2>
        <p class="text-secondary">
            Todos los historiales médicos que has registrado — Dr. <?= htmlspecialchars($_SESSION['nombre'] ?? '') ?>.
        </p>
    </div>

    <?php if ($flashError): ?>
        <div class="alert alert-error" role="alert" id="alert-flash-error">
            ⚠️ <?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if ($flashSuccess): ?>
        <div class="alert alert-success" role="alert" id="alert-flash-success">
            ✅ <?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- ── Tabla de historiales ───────────────────────────────────── -->
    <section class="content-grid" style="grid-template-columns:1fr;">
        <div class="card" id="card-historiales-doctor">
            <div class="card-header">
                <h3 class="card-title">🗂 Registro de Consultas</h3>
                <span class="badge badge-muted"><?= count($historiales) ?> registros</span>
            </div>

            <?php if (empty($historiales)): ?>
                <div class="table-empty">
                    <span>📂</span>
                    No tienes historiales médicos registrados aún.
                    <br><small>Accede a tu agenda y usa el botón <strong>"Atender"</strong> en una cita aprobada.</small>
                </div>
            <?php else: ?>
            <div class="table-wrapper">
                <table id="tabla-historiales-doctor">
                    <thead>
                        <tr>
                            <th>Fecha Consulta</th>
                            <th>Hora</th>
                            <th>Paciente</th>
                            <th>Identificación</th>
                            <th>Diagnóstico</th>
                            <th>Registrado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historiales as $h): ?>
                        <tr id="historial-row-<?= (int) $h['id_historial'] ?>">
                            <td><?= date('d/m/Y', strtotime($h['fecha_cita'])) ?></td>
                            <td><strong><?= htmlspecialchars(substr($h['hora_cita'], 0, 5)) ?></strong></td>
                            <td><?= htmlspecialchars($h['nombre_paciente'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><code><?= htmlspecialchars($h['numeroIdentificacion'], ENT_QUOTES, 'UTF-8') ?></code></td>
                            <td class="diagnostico-preview">
                                <?= htmlspecialchars(mb_substr($h['diagnostico'], 0, 60), ENT_QUOTES, 'UTF-8') ?>
                                <?= mb_strlen($h['diagnostico']) > 60 ? '…' : '' ?>
                            </td>
                            <td>
                                <span class="text-muted" style="font-size:.8rem">
                                    <?= date('d/m/Y H:i', strtotime($h['fecha_registro'])) ?>
                                </span>
                            </td>
                            <td>
                                <button type="button"
                                        class="btn btn-secondary btn-sm"
                                        id="btn-ver-historial-<?= (int) $h['id_historial'] ?>"
                                        onclick="verDetalle(<?= (int) $h['id_historial'] ?>)"
                                        data-diagnostico="<?= htmlspecialchars($h['diagnostico'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-tratamiento="<?= htmlspecialchars($h['tratamiento'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-notas="<?= htmlspecialchars($h['notas'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-paciente="<?= htmlspecialchars($h['nombre_paciente'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-fecha="<?= date('d/m/Y', strtotime($h['fecha_cita'])) ?>"
                                        data-hora="<?= htmlspecialchars(substr($h['hora_cita'], 0, 5)) ?>"
                                        data-registrado="<?= date('d/m/Y H:i', strtotime($h['fecha_registro'])) ?>">
                                    🔍 Ver
                                </button>
                                <?php
                                $tiempoTranscurrido = time() - strtotime($h['fecha_registro']);
                                $horasPermitidas = 24 * 3600;
                                if ($tiempoTranscurrido <= $horasPermitidas):
                                ?>
                                    <a href="<?= $bp ?>/historial/editar?id=<?= (int) $h['id_historial'] ?>" class="btn btn-secondary btn-sm" title="Editar antes de 24h">
                                        ✏️ Editar
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </section>

</main>

<!-- ── Modal detalle historial ──────────────────────────────────────── -->
<div class="modal-overlay" id="modal-detalle-historial" role="dialog" aria-modal="true" aria-labelledby="modal-historial-title" style="display:none">
    <div class="modal-box" id="modal-historial-box">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-historial-title">Detalle de Consulta</h3>
            <button class="modal-close" id="btn-cerrar-modal-historial" onclick="cerrarModal()" aria-label="Cerrar">✕</button>
        </div>
        <div class="modal-body" id="modal-historial-body">
            <p><strong>Paciente:</strong> <span id="m-paciente"></span></p>
            <p><strong>Fecha / Hora:</strong> <span id="m-fecha"></span> — <span id="m-hora"></span></p>
            <p><strong>Registrado el:</strong> <span id="m-registrado"></span></p>
            <hr style="border-color:var(--border-subtle);margin:1rem 0">
            <p><strong>Diagnóstico:</strong></p>
            <p id="m-diagnostico" class="detalle-texto"></p>
            <p><strong>Tratamiento:</strong></p>
            <p id="m-tratamiento" class="detalle-texto"></p>
            <div id="m-notas-bloque">
                <p><strong>Notas:</strong></p>
                <p id="m-notas" class="detalle-texto"></p>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="cerrarModal()" id="btn-cerrar-modal-footer">Cerrar</button>
        </div>
    </div>
</div>

<style>
.alert-error   { background:rgba(239,68,68,.08); border:1px solid rgba(239,68,68,.25); border-radius:var(--radius-md); color:#f87171; padding:.75rem 1rem; font-size:.875rem; margin-bottom:1rem; }
.alert-success { background:rgba(16,185,129,.08); border:1px solid rgba(16,185,129,.25); border-radius:var(--radius-md); color:#6ee7b7; padding:.75rem 1rem; font-size:.875rem; margin-bottom:1rem; }
.diagnostico-preview { max-width: 260px; font-size:.82rem; color:var(--text-muted); }

/* Modal */
.modal-overlay { position:fixed;inset:0;background:rgba(0,0,0,.55);display:flex;align-items:center;justify-content:center;z-index:1000;animation:fadeIn .2s; }
.modal-box { background:var(--bg-elevated);border:1px solid var(--border-subtle);border-radius:var(--radius-lg);width:min(580px,94vw);max-height:85vh;overflow-y:auto;box-shadow:0 25px 60px rgba(0,0,0,.4);display:flex;flex-direction:column;animation:slideUp .25s; }
.modal-header { display:flex;align-items:center;justify-content:space-between;padding:1.25rem 1.5rem;border-bottom:1px solid var(--border-subtle); }
.modal-title { font-size:1rem;font-weight:700;color:var(--text-primary);margin:0; }
.modal-close { background:none;border:none;color:var(--text-muted);font-size:1.1rem;cursor:pointer;padding:.2rem .4rem;border-radius:var(--radius-sm);transition:color .2s; }
.modal-close:hover { color:var(--text-primary); }
.modal-body { padding:1.25rem 1.5rem;font-size:.9rem;color:var(--text-secondary);display:flex;flex-direction:column;gap:.5rem; }
.modal-body p { margin:0; }
.modal-body strong { color:var(--text-primary); }
.modal-footer { padding:.75rem 1.5rem 1.25rem;display:flex;justify-content:flex-end;border-top:1px solid var(--border-subtle); }
.detalle-texto { background:var(--bg-card);border:1px solid var(--border-subtle);border-radius:var(--radius-md);padding:.6rem .85rem;font-size:.85rem;white-space:pre-wrap;word-break:break-word;color:var(--text-primary);margin-top:.25rem; }
@keyframes fadeIn { from{opacity:0} to{opacity:1} }
@keyframes slideUp { from{transform:translateY(20px);opacity:0} to{transform:translateY(0);opacity:1} }
</style>

<script>
function verDetalle(id) {
    const btn = document.getElementById('btn-ver-historial-' + id);
    document.getElementById('m-paciente').textContent   = btn.dataset.paciente;
    document.getElementById('m-fecha').textContent      = btn.dataset.fecha;
    document.getElementById('m-hora').textContent       = btn.dataset.hora;
    document.getElementById('m-registrado').textContent = btn.dataset.registrado;
    document.getElementById('m-diagnostico').textContent = btn.dataset.diagnostico;
    document.getElementById('m-tratamiento').textContent = btn.dataset.tratamiento;
    const notas = btn.dataset.notas;
    const bloque = document.getElementById('m-notas-bloque');
    if (notas.trim()) {
        document.getElementById('m-notas').textContent = notas;
        bloque.style.display = '';
    } else {
        bloque.style.display = 'none';
    }
    document.getElementById('modal-detalle-historial').style.display = 'flex';
}
function cerrarModal() {
    document.getElementById('modal-detalle-historial').style.display = 'none';
}
document.getElementById('modal-detalle-historial').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') cerrarModal();
});
</script>

<?php include __DIR__ . '/../layout/foot.php'; ?>
