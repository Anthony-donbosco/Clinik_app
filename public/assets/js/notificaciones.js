/**
 * notificaciones.js
 * Sistema de notificaciones contextual con panel dropdown tipo mensajeria.
 *
 * Endpoint: GET /api/notificaciones
 * Respuesta: { ok, count, items: [{tipo, icono, titulo, mensaje, tiempo, accion}] }
 */

(function () {
    'use strict';

    const POLLING_INTERVAL = 10000; // 10 segundos

    // Usamos la variable inyectada por PHP en foot.php, o vacio por defecto
    const BASE = window.CLINIK_BASE_URL || '';
    const API_URL = BASE + '/api/notificaciones';

    let lastCount = 0;
    let panelOpen = false;
    let allItems = [];
    let panelEl = null;
    let bellBtn = null;

    // ────────────────────────────────────────────────────────────────────
    //  PANEL DROPDOWN
    // ────────────────────────────────────────────────────────────────────

    function crearPanel() {
        panelEl = document.createElement('div');
        panelEl.id = 'notif-panel';
        panelEl.setAttribute('role', 'dialog');
        panelEl.setAttribute('aria-label', 'Panel de notificaciones');
        panelEl.innerHTML = `
            <div class="np-header">
                <div class="np-title">
                    <span class="np-title-icon">🔔</span>
                    Notificaciones
                </div>
                <button class="np-close" id="np-close-btn" aria-label="Cerrar notificaciones">✕</button>
            </div>
            <div class="np-body" id="np-body">
                <div class="np-empty" id="np-empty">
                    <span>🎉</span>
                    <p>Todo al día. No tienes notificaciones pendientes.</p>
                </div>
            </div>
        `;
        document.body.appendChild(panelEl);

        document.getElementById('np-close-btn').addEventListener('click', cerrarPanel);

        // Cierre al hacer clic fuera
        document.addEventListener('click', function (e) {
            if (panelOpen && panelEl && !panelEl.contains(e.target) && e.target !== bellBtn) {
                cerrarPanel();
            }
        });
    }

    function abrirPanel() {
        if (!panelEl) crearPanel();
        panelOpen = true;
        panelEl.classList.add('np-visible');
        renderItems();
        // Resetear badge al abrir
        updateBadge(0);
        lastCount = 0;
    }

    function cerrarPanel() {
        panelOpen = false;
        if (panelEl) panelEl.classList.remove('np-visible');
    }

    function renderItems() {
        const body = document.getElementById('np-body');
        const empty = document.getElementById('np-empty');
        if (!body) return;

        // Limpiar items previos (pero no el empty)
        body.querySelectorAll('.np-item').forEach(el => el.remove());

        if (allItems.length === 0) {
            if (empty) empty.style.display = '';
            return;
        }

        if (empty) empty.style.display = 'none';

        allItems.forEach((item, i) => {
            const el = document.createElement('div');
            el.className = `np-item np-item-${item.tipo || 'info'}`;
            el.id = `np-item-${i}`;

            const accion = item.accion
                ? `<a href="${BASE}${escapeHtml(item.accion.href)}" class="np-accion">${escapeHtml(item.accion.label)} →</a>`
                : '';

            el.innerHTML = `
                <div class="np-item-icon">${item.icono || '🔔'}</div>
                <div class="np-item-body">
                    <div class="np-item-titulo">${escapeHtml(item.titulo)}</div>
                    <div class="np-item-mensaje">${escapeHtml(item.mensaje)}</div>
                    ${accion}
                </div>
                <div class="np-item-tiempo">${escapeHtml(item.tiempo || '')}</div>
            `;
            body.appendChild(el);
        });
    }

    // ────────────────────────────────────────────────────────────────────
    //  BADGE + DOT
    // ────────────────────────────────────────────────────────────────────

    function updateBadge(count) {
        const badge = document.getElementById('nav-notif-count');
        const dot = document.getElementById('notif-dot');
        const bell = document.getElementById('notif-bell-btn');

        if (badge) {
            badge.textContent = count > 9 ? '9+' : String(count);
            badge.style.display = count > 0 ? 'inline-flex' : 'none';
        }
        if (dot) {
            dot.classList.toggle('visible', count > 0);
        }
        if (bell) {
            bell.classList.toggle('notif-bell-active', count > 0);
        }
    }

    // ────────────────────────────────────────────────────────────────────
    //  TOASTS (para nuevas notificaciones que llegan en segundo plano)
    // ────────────────────────────────────────────────────────────────────

    function getToastContainer() {
        let c = document.getElementById('toast-container');
        if (!c) {
            c = document.createElement('div');
            c.id = 'toast-container';
            c.setAttribute('aria-live', 'polite');
            document.body.appendChild(c);
        }
        return c;
    }

    function showToast(item) {
        const container = getToastContainer();
        const toast = document.createElement('div');
        const tipo = item.tipo || 'info';
        toast.className = `toast toast-${tipo}`;
        toast.setAttribute('role', 'alert');
        toast.id = `toast-${Date.now()}`;

        const accionHtml = item.accion
            ? `<a href="${BASE}${escapeHtml(item.accion.href)}" class="toast-link">${escapeHtml(item.accion.label)} →</a>`
            : '';

        toast.innerHTML = `
            <div class="toast-icon-wrap toast-icon-${tipo}">${item.icono || '🔔'}</div>
            <div class="toast-content">
                <div class="toast-title">${escapeHtml(item.titulo)}</div>
                <div class="toast-body">${escapeHtml(item.mensaje)}</div>
                ${accionHtml}
            </div>
            <button class="toast-dismiss" aria-label="Cerrar">✕</button>
        `;

        toast.querySelector('.toast-dismiss').addEventListener('click', () => {
            dismissToast(toast);
        });

        container.appendChild(toast);

        // Auto-dismiss en 6 segundos
        setTimeout(() => dismissToast(toast), 6000);
    }

    function dismissToast(toast) {
        if (!toast.parentNode) return;
        toast.classList.add('toast-out');
        toast.addEventListener('animationend', () => toast.remove(), { once: true });
    }

    // ────────────────────────────────────────────────────────────────────
    //  POLLING
    // ────────────────────────────────────────────────────────────────────

    async function fetchNotificaciones() {
        try {
            const res = await fetch(API_URL, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });

            if (!res.ok) return;
            const data = await res.json();
            if (!data || typeof data.count === 'undefined') return;

            allItems = Array.isArray(data.items) ? data.items : [];

            // Si el panel esta abierto, actualizar en tiempo real
            if (panelOpen) {
                renderItems();
            } else {
                updateBadge(data.count);
            }

            // Toasts solo para nuevas notificaciones (cuando no esta el panel abierto)
            if (!panelOpen && data.count > lastCount && allItems.length > 0) {
                const nuevas = allItems.slice(0, data.count - lastCount);
                nuevas.forEach(item => showToast(item));
            }

            lastCount = data.count;

        } catch (err) {
            console.debug('[Clini-K] Polling: ' + err.message);
        }
    }

    // ────────────────────────────────────────────────────────────────────
    //  HELPERS
    // ────────────────────────────────────────────────────────────────────

    function escapeHtml(str) {
        const d = document.createElement('div');
        d.appendChild(document.createTextNode(str || ''));
        return d.innerHTML;
    }

    // ────────────────────────────────────────────────────────────────────
    //  INIT
    // ────────────────────────────────────────────────────────────────────

    document.addEventListener('DOMContentLoaded', function () {
        bellBtn = document.getElementById('notif-bell-btn');
        const sidebarBtn = document.getElementById('nav-notificaciones'); // El ID que genera el sidebar.php

        // Función centralizada para abrir/cerrar el panel
        function togglePanel(e) {
            e.preventDefault(); // ¡Magia! Evita que el enlace recargue la página
            e.stopPropagation();
            if (panelOpen) {
                cerrarPanel();
            } else {
                abrirPanel();
            }
        }

        // Conectamos la campanita de arriba
        if (bellBtn) {
            bellBtn.addEventListener('click', togglePanel);
        }

        // Conectamos el botón del menú lateral
        if (sidebarBtn) {
            sidebarBtn.addEventListener('click', togglePanel);
        }

        // Tecla Escape cierra el panel
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && panelOpen) cerrarPanel();
        });

        fetchNotificaciones();
        setInterval(fetchNotificaciones, POLLING_INTERVAL);
    });

})();

