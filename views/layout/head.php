<?php
/**
 * views/layout/head.php
 * <head> compartido — incluir al inicio de cada vista de dashboard.
 * Variable requerida: $pageTitle (string)
 */
$pageTitle = $pageTitle ?? 'Dashboard — Clini-K';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= defined('BASE_PATH') ? BASE_PATH : '' ?>/assets/css/main.css">
    <style>
        /* ── Sidebar user block ────────────────────────── */
        .sidebar-user {
            display: flex;
            align-items: center;
            gap: .875rem;
            padding: 1rem 1.5rem 1.25rem;
            border-bottom: 1px solid var(--border-subtle);
        }
        .sidebar-avatar {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            border-radius: var(--radius-full);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .875rem; color: #fff;
            flex-shrink: 0;
        }
        .sidebar-user-name  { font-size: .85rem; font-weight: 600; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px; }
        .sidebar-user-role  { font-size: .72rem; color: var(--text-muted); margin-top: .1rem; }
        .sidebar-footer     { margin-top: auto; padding: .75rem 0; border-top: 1px solid var(--border-subtle); }

        /* ── Estado badge en tablas ────────────────────── */
        .estado-pendiente { background: rgba(245,158,11,.15); color: #fcd34d; }
        .estado-aprobada  { background: rgba(16,185,129,.15); color: #6ee7b7; }
        .estado-atendida  { background: rgba(59,130,246,.15); color: #93c5fd; }
        .estado-cancelada { background: rgba(239,68,68,.15);  color: #fca5a5; }
        .estado-activo    { background: rgba(16,185,129,.15); color: #6ee7b7; }

        /* ── Tabla scroll ──────────────────────────────── */
        .table-empty {
            text-align: center;
            padding: 2.5rem 1rem;
            color: var(--text-muted);
            font-size: .9rem;
        }
        .table-empty span { display: block; font-size: 2rem; margin-bottom: .5rem; }
    </style>
</head>
<body>
<div class="app-layout">
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="main-content">
