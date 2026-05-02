<?php
/**
 * views/historial/paciente_historial.php
 * Vista del Paciente — muestra sus diagnosticos medicos recibidos.
 * Variables: $historiales (array[]), $bp (string)
 */
$pageTitle = 'Mi Historial Médico — Clini-K';
include __DIR__ . '/../layout/head.php';
?>

<!-- ── Topbar ──────────────────────────────────────────────────────── -->
<header class="topbar no-print">
    <h1 class="topbar-title">📋 Mi Historial Médico</h1>
    <div class="topbar-actions">
        <button onclick="window.print()" class="btn btn-secondary btn-sm" id="btn-imprimir-historial"
                title="Imprimir o guardar como PDF">
            🖨️ Imprimir
        </button>
        <button class="notification-btn" id="notif-bell-btn" aria-label="Notificaciones">
            🔔 <span class="notification-dot" id="notif-dot"></span>
        </button>
        <div class="user-avatar" title="<?= htmlspecialchars($_SESSION['nombre'] ?? '') ?>">
            <?= strtoupper(substr($_SESSION['nombre'] ?? 'P', 0, 1)) ?>
        </div>
    </div>
</header>

<!-- ── Contenido principal ─────────────────────────────────────────── -->
<main class="page-content" id="paciente-historial-page">

    <div class="page-header">
        <h2>Mis Diagnósticos</h2>
        <p class="text-secondary">
            Consulta el historial médico de tus atenciones anteriores.
        </p>
    </div>

    <?php if (empty($historiales)): ?>
        <!-- Estado vacío -->
        <section class="content-grid" style="grid-template-columns:1fr;">
            <div class="card" id="card-historial-vacio">
                <div class="table-empty">
                    <span>🩺</span>
                    Aún no tienes consultas registradas en tu historial.<br>
                    <small>Cuando un doctor atienda tu cita, el diagnóstico aparecerá aquí.</small>
                </div>
            </div>
        </section>

    <?php else: ?>

        <!-- ── Tarjetas de resumen ──────────────────────────────────── -->
        <section class="stats-grid" aria-label="Resumen de historial médico">
            <div class="stat-card" id="stat-total-consultas">
                <div class="stat-icon blue">🩺</div>
                <div>
                    <div class="stat-value"><?= count($historiales) ?></div>
                    <div class="stat-label">Consultas Totales</div>
                </div>
            </div>
            <div class="stat-card" id="stat-ultima-consulta">
                <div class="stat-icon cyan">📅</div>
                <div>
                    <div class="stat-value" style="font-size:1.1rem">
                        <?= date('d/m/Y', strtotime($historiales[0]['fecha_cita'])) ?>
                    </div>
                    <div class="stat-label">Última Consulta</div>
                </div>
            </div>
            <div class="stat-card" id="stat-especialidades">
                <div class="stat-icon green">👨‍⚕️</div>
                <div>
                    <div class="stat-value">
                        <?= count(array_unique(array_column($historiales, 'nombre_doctor'))) ?>
                    </div>
                    <div class="stat-label">Médicos Distintos</div>
                </div>
            </div>
        </section>

        <!-- ── Timeline de consultas ───────────────────────────────── -->
        <section class="content-grid" style="grid-template-columns:1fr;">
            <div class="card" id="card-historial-timeline">
                <div class="card-header">
                    <h3 class="card-title">🗂 Historial de Consultas</h3>
                </div>

                <div class="historial-timeline" id="historial-timeline">
                    <?php foreach ($historiales as $i => $h): ?>
                    <div class="timeline-item" id="timeline-item-<?= (int) $h['id_historial'] ?>">

                        <!-- Fecha label -->
                        <div class="timeline-date-col">
                            <span class="timeline-date"><?= date('d/m/Y', strtotime($h['fecha_cita'])) ?></span>
                            <span class="timeline-time"><?= htmlspecialchars(substr($h['hora_cita'], 0, 5)) ?></span>
                        </div>

                        <!-- Conector -->
                        <div class="timeline-connector">
                            <div class="timeline-dot"></div>
                            <?php if ($i < count($historiales) - 1): ?>
                                <div class="timeline-line"></div>
                            <?php endif; ?>
                        </div>

                        <!-- Tarjeta de la consulta -->
                        <div class="timeline-card">
                            <div class="timeline-card-header">
                                <div>
                                    <p class="timeline-doctor">
                                        👨‍⚕️ <?= htmlspecialchars($h['nombre_doctor'], ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                    <p class="timeline-especialidad">
                                        <?= htmlspecialchars($h['especialidad'] ?? 'General', ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                </div>
                                <span class="badge estado-atendida">Atendida</span>
                            </div>

                            <div class="timeline-section">
                                <p class="timeline-section-label">Diagnóstico</p>
                                <p class="timeline-section-text"><?= nl2br(htmlspecialchars($h['diagnostico'], ENT_QUOTES, 'UTF-8')) ?></p>
                            </div>

                            <div class="timeline-section">
                                <p class="timeline-section-label">Tratamiento</p>
                                <p class="timeline-section-text"><?= nl2br(htmlspecialchars($h['tratamiento'], ENT_QUOTES, 'UTF-8')) ?></p>
                            </div>

                            <?php if (!empty($h['notas'])): ?>
                            <div class="timeline-section">
                                <p class="timeline-section-label">Receta / Indicaciones</p>
                                <p class="timeline-section-text timeline-notas"><?= nl2br(htmlspecialchars($h['notas'], ENT_QUOTES, 'UTF-8')) ?></p>
                            </div>
                            <?php endif; ?>

                            <p class="timeline-registered">
                                Registrado el <?= date('d/m/Y \a \l\a\s H:i', strtotime($h['fecha_registro'])) ?>
                            </p>
                        </div>

                    </div>
                    <?php endforeach; ?>
                </div>

            </div>
        </section>

    <?php endif; ?>

</main>

<style>
/* ── Timeline de historial médico ── */
.historial-timeline {
    display: flex;
    flex-direction: column;
    gap: 0;
    padding: .5rem 0;
}
.timeline-item {
    display: grid;
    grid-template-columns: 90px 28px 1fr;
    gap: 0 1rem;
    align-items: start;
}
.timeline-date-col {
    text-align: right;
    padding-top: .6rem;
    display: flex;
    flex-direction: column;
    gap: .1rem;
}
.timeline-date { font-size: .78rem; font-weight: 700; color: var(--text-primary); }
.timeline-time { font-size: .7rem; color: var(--text-muted); }
.timeline-connector {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0;
}
.timeline-dot {
    width: 12px; height: 12px;
    border-radius: 50%;
    background: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(6,182,212,.2);
    flex-shrink: 0;
    margin-top: .75rem;
}
.timeline-line {
    width: 2px;
    flex: 1;
    min-height: 1.5rem;
    background: var(--border-subtle);
}
.timeline-card {
    background: var(--bg-elevated);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    padding: 1rem 1.25rem;
    margin-bottom: 1rem;
    display: flex;
    flex-direction: column;
    gap: .75rem;
    transition: border-color .2s, box-shadow .2s;
}
.timeline-card:hover {
    border-color: rgba(6,182,212,.3);
    box-shadow: 0 4px 20px rgba(0,0,0,.15);
}
.timeline-card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: .5rem;
}
.timeline-doctor { font-size: .9rem; font-weight: 700; color: var(--text-primary); margin: 0; }
.timeline-especialidad { font-size: .75rem; color: var(--text-muted); margin: .1rem 0 0; }
.timeline-section-label {
    font-size: .7rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--color-primary);
    font-weight: 700;
    margin: 0 0 .25rem;
}
.timeline-section-text {
    font-size: .875rem;
    color: var(--text-secondary);
    line-height: 1.5;
    margin: 0;
    background: var(--bg-card);
    border-radius: var(--radius-md);
    padding: .5rem .75rem;
}
.timeline-notas { color: var(--text-muted); font-style: italic; }
.timeline-registered {
    font-size: .7rem;
    color: var(--text-muted);
    text-align: right;
    margin: 0;
    padding-top: .25rem;
    border-top: 1px solid var(--border-subtle);
}

@media (max-width: 600px) {
    .timeline-item { grid-template-columns: 70px 20px 1fr; gap: 0 .5rem; }
    .timeline-date { font-size:.7rem; }
}

/* ── Estilos de Impresion (Diagrama 7 Paso 9) ─────────── */
@media print {
    .sidebar, .no-print, .topbar-actions { display: none !important; }
    .page-content { margin-left: 0 !important; padding: 1rem !important; }
    .stats-grid { display: none; }

    /* Cabecera institucional */
    body::before {
        content: 'CLINI-K -- Historial Medico del Paciente';
        display: block;
        font-size: 1.1rem;
        font-weight: 700;
        text-align: center;
        margin-bottom: 1rem;
        border-bottom: 2px solid #000;
        padding-bottom: .5rem;
        color: #000;
    }

    body, .timeline-card, .page-content, main {
        background: #fff !important;
        color: #000 !important;
    }
    .timeline-card {
        border: 1px solid #ccc !important;
        page-break-inside: avoid;
        margin-bottom: 1rem;
        box-shadow: none !important;
    }
    .timeline-dot { background: #000 !important; box-shadow: none !important; }
    .timeline-line { background: #ccc !important; }
    .timeline-date, .timeline-time, .timeline-doctor,
    .timeline-section-label { color: #000 !important; }
    .timeline-section-text {
        background: #f5f5f5 !important;
        color: #222 !important;
        border: 1px solid #ddd !important;
    }
    .badge { border: 1px solid #999 !important; color: #000 !important; background: #eee !important; }
    .timeline-registered { color: #555 !important; }
    .topbar { background: #fff !important; }
    .topbar-title { color: #000 !important; font-size: 1rem; }
    a { text-decoration: none !important; color: #000 !important; }
    .page-header h2 { color: #000 !important; }
    .page-header p { color: #333 !important; }
}
</style>

<?php include __DIR__ . '/../layout/foot.php'; ?>
