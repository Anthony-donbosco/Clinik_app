/**
 * notificaciones.js  (v2 — con estado de leídas en localStorage)
 *
 * Las notificaciones son generadas en tiempo real desde la BD.
 * Para persistir el estado "leída" sin modificar el esquema de BD,
 * guardamos los IDs en localStorage con TTL de 24 horas.
 *
 * Endpoint: GET /api/notificaciones
 * Respuesta: { ok, count, items: [{id, tipo, icono, titulo, mensaje, tiempo, accion}] }
 */

(function () {
    'use strict';

    const POLLING_INTERVAL = 10000;      // 10 s
    const STORAGE_KEY      = 'clinik_notif_read';
    const TTL_MS           = 24 * 60 * 60 * 1000; // 24 h

    const BASE    = window.CLINIK_BASE_URL || '';
    const API_URL = BASE + '/api/notificaciones';

    let lastCount = 0;
    let panelOpen = false;
    let allItems  = [];   // todas las notificaciones del último fetch
    let panelEl   = null;
    let bellBtn   = null;

    // ══════════════════════════════════════════════════════════════════════
    //  ESTADO LEÍDAS (localStorage)
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Devuelve el Set de IDs ya leídos (purgando los expirados).
     */
    function getReadIds() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) return new Set();
            const obj = JSON.parse(raw);
            const now = Date.now();
            // Filtrar entradas expiradas
            const cleaned = Object.fromEntries(
                Object.entries(obj).filter(([, ts]) => now - ts < TTL_MS)
            );
            localStorage.setItem(STORAGE_KEY, JSON.stringify(cleaned));
            return new Set(Object.keys(cleaned));
        } catch {
            return new Set();
        }
    }

    const TOASTS_KEY = 'clinik_notif_toasts';
    function getShownToasts() {
        try {
            const raw = sessionStorage.getItem(TOASTS_KEY);
            return raw ? new Set(JSON.parse(raw)) : new Set();
        } catch { return new Set(); }
    }
    function markToastShown(id) {
        try {
            const set = getShownToasts();
            set.add(String(id));
            sessionStorage.setItem(TOASTS_KEY, JSON.stringify([...set]));
        } catch {}
    }

    function markRead(id) {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            const obj = raw ? JSON.parse(raw) : {};
            obj[String(id)] = Date.now();
            localStorage.setItem(STORAGE_KEY, JSON.stringify(obj));
        } catch { /* sin localStorage */ }
    }

    function markAllRead(ids) {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            const obj = raw ? JSON.parse(raw) : {};
            const now = Date.now();
            ids.forEach(id => { obj[String(id)] = now; });
            localStorage.setItem(STORAGE_KEY, JSON.stringify(obj));
        } catch { /* sin localStorage */ }
    }

    /**
     * Filtra allItems quitando las ya leídas.
     */
    function getUnread() {
        const readIds = getReadIds();
        return allItems.filter(item => !readIds.has(String(item.id)));
    }

    // ══════════════════════════════════════════════════════════════════════
    //  PANEL DROPDOWN
    // ══════════════════════════════════════════════════════════════════════

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
                <div class="np-header-actions">
                    <button class="np-mark-all" id="np-mark-all-btn" title="Marcar todas como leídas">
                        ✅ Todas leídas
                    </button>
                    <button class="np-close" id="np-close-btn" aria-label="Cerrar notificaciones">✕</button>
                </div>
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
        document.getElementById('np-mark-all-btn').addEventListener('click', marcarTodasLeidas);

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
        // Al abrir el panel el badge se resetea visualmente
        const unread = getUnread();
        updateBadge(unread.length);
    }

    function cerrarPanel() {
        panelOpen = false;
        if (panelEl) panelEl.classList.remove('np-visible');
    }

    function renderItems() {
        const body  = document.getElementById('np-body');
        const empty = document.getElementById('np-empty');
        const markAllBtn = document.getElementById('np-mark-all-btn');
        if (!body) return;

        // Limpiar items previos
        body.querySelectorAll('.np-item').forEach(el => el.remove());

        const unread  = getUnread();
        const readIds = getReadIds();

        // Mostrar también las ya leídas pero de forma atenuada (hasta 5)
        const toShow = [
            ...unread,
            ...allItems.filter(item => readIds.has(String(item.id))).slice(0, 5 - unread.length),
        ].slice(0, 10);

        if (toShow.length === 0) {
            if (empty)       empty.style.display = '';
            if (markAllBtn)  markAllBtn.style.display = 'none';
            return;
        }

        if (empty)       empty.style.display = 'none';
        if (markAllBtn)  markAllBtn.style.display = unread.length > 0 ? '' : 'none';

        toShow.forEach((item) => {
            const isRead = readIds.has(String(item.id));
            const el = document.createElement('div');
            el.className = `np-item np-item-${item.tipo || 'info'}${isRead ? ' np-item-read' : ''}`;
            el.id = `np-item-${item.id}`;

            const accion = item.accion
                ? `<a href="${BASE}${escapeHtml(item.accion.href)}" class="np-accion">${escapeHtml(item.accion.label)} →</a>`
                : '';

            const markBtn = !isRead
                ? `<button class="np-mark-one" data-id="${escapeHtml(String(item.id))}"
                          title="Marcar como leída" aria-label="Marcar como leída">✓</button>`
                : `<span class="np-read-badge" title="Leída">✓</span>`;

            el.innerHTML = `
                <div class="np-item-icon">${item.icono || '🔔'}</div>
                <div class="np-item-body">
                    <div class="np-item-titulo">${escapeHtml(item.titulo)}</div>
                    <div class="np-item-mensaje">${escapeHtml(item.mensaje)}</div>
                    ${accion}
                </div>
                <div class="np-item-right">
                    <div class="np-item-tiempo">${escapeHtml(item.tiempo || '')}</div>
                    ${markBtn}
                </div>
            `;

            // Listener botón marcar una
            if (!isRead) {
                el.querySelector('.np-mark-one').addEventListener('click', function (e) {
                    e.stopPropagation();
                    markRead(item.id);
                    renderItems();
                    updateBadge(getUnread().length);
                });
            }

            body.appendChild(el);
        });
    }

    // ── Marcar TODAS como leídas ──────────────────────────────────────────
    function marcarTodasLeidas() {
        markAllRead(allItems.map(i => i.id));
        renderItems();
        updateBadge(0);
        lastCount = 0;
    }

    // ══════════════════════════════════════════════════════════════════════
    //  BADGE + DOT
    // ══════════════════════════════════════════════════════════════════════

    function updateBadge(count) {
        const badge = document.getElementById('nav-notif-count');
        const dot   = document.getElementById('notif-dot');
        const bell  = document.getElementById('notif-bell-btn');

        if (badge) {
            badge.textContent    = count > 9 ? '9+' : String(count);
            badge.style.display  = count > 0 ? 'inline-flex' : 'none';
        }
        if (dot)  dot.classList.toggle('visible', count > 0);
        if (bell) bell.classList.toggle('notif-bell-active', count > 0);
    }

    // ══════════════════════════════════════════════════════════════════════
    //  TOASTS
    // ══════════════════════════════════════════════════════════════════════

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
        const tipo  = item.tipo || 'info';
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

        toast.querySelector('.toast-dismiss').addEventListener('click', () => dismissToast(toast));
        container.appendChild(toast);
        setTimeout(() => dismissToast(toast), 6000);
    }

    function dismissToast(toast) {
        if (!toast.parentNode) return;
        toast.classList.add('toast-out');
        toast.addEventListener('animationend', () => toast.remove(), { once: true });
    }

    // ══════════════════════════════════════════════════════════════════════
    //  POLLING
    // ══════════════════════════════════════════════════════════════════════

    async function fetchNotificaciones() {
        try {
            const res  = await fetch(API_URL, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            if (!res.ok) return;
            const data = await res.json();
            if (!data || typeof data.count === 'undefined') return;

            allItems = Array.isArray(data.items) ? data.items : [];

            // Calcular no leídas reales
            const unread = getUnread();

            if (panelOpen) {
                // Panel abierto: refrescar lista
                renderItems();
            } else {
                // Badge solo con no leídas
                updateBadge(unread.length);
            }

            // Toasts solo para notificaciones realmente nuevas Y no leídas que no se hayan mostrado en esta sesión
            const shownToasts = getShownToasts();
            const paraMostrar = unread.filter(item => !shownToasts.has(String(item.id)));

            if (!panelOpen && paraMostrar.length > 0) {
                paraMostrar.forEach(item => {
                    showToast(item);
                    markToastShown(item.id);
                });
            }

            lastCount = unread.length;

        } catch (err) {
            console.debug('[Clini-K] Polling error: ' + err.message);
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    //  HELPERS
    // ══════════════════════════════════════════════════════════════════════

    function escapeHtml(str) {
        const d = document.createElement('div');
        d.appendChild(document.createTextNode(str || ''));
        return d.innerHTML;
    }

    // ══════════════════════════════════════════════════════════════════════
    //  INIT
    // ══════════════════════════════════════════════════════════════════════

    document.addEventListener('DOMContentLoaded', function () {
        bellBtn = document.getElementById('notif-bell-btn');

        function togglePanel(e) {
            e.preventDefault();
            e.stopPropagation();
            if (panelOpen) {
                cerrarPanel();
            } else {
                abrirPanel();
            }
        }

        if (bellBtn) bellBtn.addEventListener('click', togglePanel);

        const sidebarBtn = document.getElementById('nav-notificaciones');
        if (sidebarBtn) sidebarBtn.addEventListener('click', togglePanel);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && panelOpen) cerrarPanel();
        });

        fetchNotificaciones();
        setInterval(fetchNotificaciones, POLLING_INTERVAL);
    });

})();
