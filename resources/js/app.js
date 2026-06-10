import Alpine from 'alpinejs';
import * as Turbo from '@hotwired/turbo';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

window.Alpine = Alpine;
window.Turbo = Turbo;

Turbo.session.drive = true;

const WORKSPACE_STATE_KEY = 'erp.workspace.tableState';

const erpModalManager = {
    pendingModalLoad: false,
    pendingDrawerLoad: false,
    modalLoadSeq: 0,
    modalAbortController: null,

    overlay() {
        return document.getElementById('erp-modal-overlay');
    },

    drawerOverlay() {
        return document.getElementById('erp-drawer-overlay');
    },

    modalFrame() {
        return document.getElementById('erp-form-modal');
    },

    drawerFrame() {
        return document.getElementById('erp-preview-drawer');
    },

    toastHost() {
        return document.getElementById('erp-toast-host');
    },

    openModal(url) {
        return this.loadForm(url);
    },

    modalConfig() {
        return window.__erpModalForm ?? { blockedPathFragments: [] };
    },

    isModalFormUrl(url) {
        try {
            const parsed = new URL(url, window.location.origin);

            if (parsed.origin !== window.location.origin) {
                return false;
            }

            const path = parsed.pathname.toLowerCase();
            const blocked = this.modalConfig().blockedPathFragments ?? [];

            if (blocked.some((fragment) => path.includes(String(fragment).toLowerCase()))) {
                return false;
            }

            return /\/(create|edit)(\/|$)/.test(path);
        } catch {
            return false;
        }
    },

    shouldOpenLinkAsModal(link) {
        if (! link?.href) {
            return false;
        }

        if (link.hasAttribute('data-erp-modal-open') || link.hasAttribute('data-no-modal')) {
            return false;
        }

        if (link.getAttribute('target') === '_blank' || link.getAttribute('data-turbo') === 'false') {
            return false;
        }

        if (link.closest('#erp-form-modal') || link.closest('[data-turbo-frame="_top"]')) {
            return false;
        }

        return this.isModalFormUrl(link.href);
    },

    prepareModalFormContent(root, sourceUrl = null) {
        root.querySelectorAll('form').forEach((form) => {
            form.removeAttribute('data-turbo-frame');
            form.removeAttribute('data-turbo');

            if (! form.querySelector('[name="_erp_modal"]')) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = '_erp_modal';
                input.value = '1';
                form.appendChild(input);
            }

            if (sourceUrl && ! form.querySelector('[name="_erp_modal_return"]')) {
                const returnInput = document.createElement('input');
                returnInput.type = 'hidden';
                returnInput.name = '_erp_modal_return';
                returnInput.value = sourceUrl;
                form.appendChild(returnInput);
            }
        });
    },

    hasValidationErrors(doc) {
        return Boolean(
            doc?.querySelector('[data-erp-validation-errors]')
            || doc?.querySelector('[data-erp-field-error]')
            || doc?.querySelector('[role="alert"]'),
        );
    },

    highlightInvalidFields(panel, doc) {
        if (! panel || ! doc) {
            return;
        }

        panel.querySelectorAll('.erp-input--invalid, .erp-select--invalid').forEach((element) => {
            element.classList.remove('erp-input--invalid', 'erp-select--invalid');
        });

        const invalidNames = new Set(
            [...doc.querySelectorAll('[data-erp-field-error]')]
                .map((element) => element.dataset.erpFieldError)
                .filter(Boolean),
        );

        doc.querySelectorAll('input:invalid, select:invalid, textarea:invalid').forEach((element) => {
            if (element.name) {
                invalidNames.add(element.name);
            }
        });

        invalidNames.forEach((name) => {
            const field = panel.querySelector(`[name="${CSS.escape(name)}"]`);

            if (! field) {
                return;
            }

            if (field.matches('select')) {
                field.classList.add('erp-select--invalid');
            } else {
                field.classList.add('erp-input--invalid');
            }
        });
    },

    ensureValidationSummary(panel, doc) {
        if (! panel || panel.querySelector('[data-erp-validation-errors]')) {
            return;
        }

        const errors = [...doc.querySelectorAll('[data-erp-validation-errors] li, [data-erp-field-error] li')]
            .map((element) => element.textContent?.trim())
            .filter(Boolean);

        if (errors.length === 0) {
            return;
        }

        const alert = document.createElement('div');
        alert.className = 'erp-alert erp-alert--danger mb-4';
        alert.setAttribute('role', 'alert');
        alert.setAttribute('data-erp-validation-errors', '');
        alert.innerHTML = `
            <p class="font-medium">Please correct the highlighted fields.</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                ${errors.map((error) => `<li>${error}</li>`).join('')}
            </ul>
        `;

        const body = panel.querySelector('.erp-form-modal__body') ?? panel;
        const form = body.querySelector('form');

        if (form) {
            form.prepend(alert);
        } else {
            body.prepend(alert);
        }
    },

    buildModalPanel(title, bodyHtml) {
        const wrapper = document.createElement('div');
        wrapper.className = 'erp-form-modal w-full';
        wrapper.setAttribute('data-erp-form-modal-panel', '');

        const header = document.createElement('div');
        header.className = 'erp-form-modal__header';

        const heading = document.createElement('h2');
        heading.id = 'erp-form-modal-title';
        heading.className = 'erp-form-modal__title';
        heading.textContent = title || 'Form';

        const closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = 'erp-form-modal__close';
        closeButton.setAttribute('data-erp-form-modal-close', '');
        closeButton.setAttribute('aria-label', 'Close');
        closeButton.innerHTML = '<svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>';

        header.append(heading, closeButton);

        const body = document.createElement('div');
        body.className = 'erp-form-modal__body';
        body.innerHTML = bodyHtml;
        this.prepareModalFormContent(body);

        wrapper.append(header, body);

        return wrapper;
    },

    extractModalPanel(html) {
        const doc = new DOMParser().parseFromString(html, 'text/html');

        const nativePanel = doc.querySelector('[data-erp-form-modal-panel]')
            ?? doc.querySelector('#erp-form-modal [data-erp-form-modal-panel]')
            ?? doc.querySelector('#erp-form-modal .erp-form-modal')
            ?? doc.querySelector('.erp-form-modal');

        if (nativePanel) {
            return nativePanel;
        }

        const main = doc.querySelector('#erp-main main') ?? doc.querySelector('main');
        const form = main?.querySelector('form');

        if (! form) {
            return null;
        }

        const title = doc.querySelector('#erp-page-title')?.textContent?.trim()
            ?? doc.querySelector('h1')?.textContent?.trim()
            ?? doc.title.split('—')[0]?.trim()
            ?? 'Form';

        const container = form.closest('.bg-white')
            ?? form.closest('.erp-data-grid')?.parentElement
            ?? form.closest('[class*="card"]')
            ?? form.parentElement;

        const bodyHtml = container?.innerHTML ?? form.outerHTML;
        const panel = this.buildModalPanel(title, bodyHtml);
        this.prepareModalFormContent(panel, doc.baseURI || window.location.href);

        return panel;
    },

    abortModalLoad() {
        if (this.modalAbortController) {
            this.modalAbortController.abort();
            this.modalAbortController = null;
        }
    },

    renderModalLoading() {
        const frame = this.modalFrame();

        if (! frame) {
            return;
        }

        frame.innerHTML = `
            <div class="erp-form-modal w-full" data-erp-form-modal-loading>
                <div class="erp-form-modal__header">
                    <h2 id="erp-form-modal-title" class="erp-form-modal__title">Loading…</h2>
                    <button type="button" class="erp-form-modal__close" data-erp-form-modal-close aria-label="Close">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="erp-form-modal__body erp-form-modal__body--loading">
                    <div class="erp-modal-spinner" role="status" aria-label="Loading form">
                        <span class="sr-only">Loading form</span>
                    </div>
                </div>
            </div>
        `;

        this.showOverlay();
    },

    async loadForm(url) {
        const frame = this.modalFrame();

        if (! frame || ! url) {
            return;
        }

        this.abortModalLoad();
        const loadId = ++this.modalLoadSeq;
        this.modalAbortController = new AbortController();
        this.pendingModalLoad = true;
        this.renderModalLoading();

        try {
            const response = await fetch(url, {
                signal: this.modalAbortController.signal,
                headers: {
                    'Turbo-Frame': 'erp-form-modal',
                    'Accept': 'text/html, application/xhtml+xml',
                },
                credentials: 'same-origin',
            });

            if (loadId !== this.modalLoadSeq) {
                return;
            }

            if (! response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const panel = this.extractModalPanel(await response.text());

            if (! panel) {
                throw new Error('Modal form markup was not found in the response.');
            }

            this.prepareModalFormContent(panel, url);
            frame.replaceChildren(panel);
            this.pendingModalLoad = false;
            this.modalAbortController = null;
            this.showOverlay();

            await new Promise((resolve) => window.requestAnimationFrame(resolve));
            Alpine.initTree(frame);
        } catch (error) {
            if (error?.name === 'AbortError' || loadId !== this.modalLoadSeq) {
                return;
            }

            console.error('erpModalManager.loadForm', error);
            this.pendingModalLoad = false;
            this.modalAbortController = null;
            this.closeModal();
            this.showToast('Unable to open form. Please try again.', 'error');
        }
    },

    closeModal() {
        this.modalLoadSeq += 1;
        this.abortModalLoad();
        this.pendingModalLoad = false;

        const frame = this.modalFrame();

        if (frame) {
            frame.removeAttribute('src');
            frame.innerHTML = '';
        }

        this.hideOverlay();
    },

    openDrawer(url) {
        const frame = this.drawerFrame();

        if (! frame || ! url) {
            return;
        }

        frame.src = url;
        this.showDrawer();
    },

    closeDrawer() {
        this.pendingDrawerLoad = false;

        const frame = this.drawerFrame();

        if (frame) {
            frame.removeAttribute('src');
            frame.innerHTML = '';
        }

        this.hideDrawer();
    },

    showOverlay() {
        const overlay = this.overlay();

        if (overlay) {
            overlay.hidden = false;
            overlay.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');
        }
    },

    hideOverlay() {
        const overlay = this.overlay();

        if (overlay) {
            overlay.hidden = true;
            overlay.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('overflow-hidden');
        }
    },

    showDrawer() {
        const overlay = this.drawerOverlay();

        if (overlay) {
            overlay.hidden = false;
            overlay.setAttribute('aria-hidden', 'false');
        }
    },

    hideDrawer() {
        const overlay = this.drawerOverlay();

        if (overlay) {
            overlay.hidden = true;
            overlay.setAttribute('aria-hidden', 'true');
        }
    },

    showToast(message, variant = 'success') {
        const host = this.toastHost();

        if (! host || ! message) {
            return;
        }

        const toast = document.createElement('div');
        toast.className = `erp-toast erp-toast--${variant}`;
        toast.setAttribute('role', 'status');
        toast.textContent = message;
        host.appendChild(toast);

        window.setTimeout(() => {
            toast.remove();
        }, 4500);
    },

    saveWorkspaceState() {
        const frame = document.getElementById('erp-main');

        if (! frame) {
            return;
        }

        const tables = frame.querySelectorAll('[x-data]');
        const states = [];

        tables.forEach((element) => {
            const data = element._x_dataStack?.[0];

            if (! data || typeof data.rowVisible !== 'function') {
                return;
            }

            states.push({
                query: data.query ?? '',
                activeChip: data.activeChip ?? 'all',
                filterOpen: data.filterOpen ?? false,
            });
        });

        sessionStorage.setItem(WORKSPACE_STATE_KEY, JSON.stringify({
            scrollY: shellScrollY(),
            tables: states,
        }));
    },

    restoreWorkspaceState() {
        const raw = sessionStorage.getItem(WORKSPACE_STATE_KEY);

        if (! raw) {
            return;
        }

        sessionStorage.removeItem(WORKSPACE_STATE_KEY);

        let saved;

        try {
            saved = JSON.parse(raw);
        } catch {
            return;
        }

        const frame = document.getElementById('erp-main');

        if (! frame) {
            return;
        }

        const tables = [...frame.querySelectorAll('[x-data]')].filter((element) => {
            const data = element._x_dataStack?.[0];

            return data && typeof data.rowVisible === 'function';
        });

        saved.tables?.forEach((state, index) => {
            const data = tables[index]?._x_dataStack?.[0];

            if (! data) {
                return;
            }

            if ('query' in state) {
                data.query = state.query;
            }

            if ('activeChip' in state) {
                data.activeChip = state.activeChip;
            }

            if ('filterOpen' in state) {
                data.filterOpen = state.filterOpen;
            }
        });

        if (typeof saved.scrollY === 'number') {
            window.requestAnimationFrame(() => {
                setShellScrollY(saved.scrollY);
            });
        }
    },

    refreshTable() {
        const frame = document.getElementById('erp-main');

        if (! frame || ! window.Turbo) {
            return Promise.resolve();
        }

        this.saveWorkspaceState();

        return window.Turbo.visit(window.location.href, {
            frame: 'erp-main',
            action: 'replace',
        });
    },

    async submitFormRequest(form) {
        if (! form) {
            return;
        }

        const formData = new FormData(form);
        const method = (formData.get('_method') || form.method || 'POST').toString().toUpperCase();
        const submitButton = form.querySelector('[type="submit"]');
        const modalReturnUrl = formData.get('_erp_modal_return')?.toString()
            || form.dataset.erpModalReturn
            || null;

        if (submitButton) {
            submitButton.disabled = true;
        }

        try {
            const response = await fetch(form.action, {
                method: method === 'GET' ? 'GET' : 'POST',
                body: method === 'GET' ? null : formData,
                headers: {
                    'Turbo-Frame': 'erp-form-modal',
                    'Accept': 'text/html, application/xhtml+xml',
                },
                credentials: 'same-origin',
                redirect: 'follow',
            });

            const html = await response.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const successMarker = doc.querySelector('[data-erp-modal-success]');

            if (successMarker) {
                this.handleSuccess({
                    message: successMarker.dataset.message ?? '',
                    refresh: successMarker.dataset.refresh !== '0',
                });

                return;
            }

            const errorPanel = doc.querySelector('[data-erp-form-modal-panel]')
                ?? doc.querySelector('#erp-form-modal [data-erp-form-modal-panel]');

            if (! response.ok && errorPanel) {
                const frame = this.modalFrame();

                if (frame) {
                    frame.replaceChildren(errorPanel);
                    this.showOverlay();
                    Alpine.initTree(frame);
                }

                const errorMessage = doc.querySelector('[data-erp-validation-errors] p')?.textContent?.trim()
                    ?? `Unable to save form (${response.status}). Please try again.`;

                this.showToast(errorMessage, 'error');

                return;
            }

            const panel = this.extractModalPanel(html);
            const frame = this.modalFrame();

            if (panel && frame) {
                this.prepareModalFormContent(panel, modalReturnUrl || response.url);
                frame.replaceChildren(panel);
                this.showOverlay();
                Alpine.initTree(frame);

                if (this.hasValidationErrors(doc)) {
                    this.ensureValidationSummary(panel, doc);
                    this.highlightInvalidFields(panel, doc);

                    const modalError = doc.querySelector('[data-erp-modal-error] p:last-child')?.textContent?.trim()
                        ?? doc.querySelector('[data-erp-validation-errors] li')?.textContent?.trim();

                    this.showToast(modalError || 'Please correct the highlighted fields.', 'error');
                }

                return;
            }

            if (! response.ok) {
                console.error('erpModalManager.submitFormRequest', {
                    status: response.status,
                    url: form.action,
                });
                this.showToast(`Unable to save form (${response.status}). Please try again.`, 'error');

                return;
            }

            this.showToast('Unable to save form. Please try again.', 'error');
        } catch (error) {
            console.error('erpModalManager.submitFormRequest', error);
            this.showToast('Unable to save form. Please try again.', 'error');
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
            }
        }
    },

    submitForm(form) {
        if (! form) {
            return;
        }

        if (form.closest('#erp-form-modal')) {
            this.submitFormRequest(form);

            return;
        }

        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
        } else {
            form.submit();
        }
    },

    handleSuccess({ message = '', refresh = true } = {}) {
        this.closeModal();

        if (message) {
            this.showToast(message);
        }

        if (refresh) {
            this.refreshTable().then(() => {
                this.restoreWorkspaceState();
            });
        }
    },

    handleDocumentClick(event) {
        const modalLink = event.target.closest('[data-erp-modal-open]');

        if (modalLink?.href) {
            event.preventDefault();
            event.stopPropagation();
            this.loadForm(modalLink.href);

            return;
        }

        const formLink = event.target.closest('a[href]');

        if (formLink && this.shouldOpenLinkAsModal(formLink)) {
            event.preventDefault();
            event.stopPropagation();
            this.loadForm(formLink.href);

            return;
        }

        const drawerLink = event.target.closest('a[data-turbo-frame="erp-preview-drawer"]');

        if (drawerLink?.href) {
            this.pendingDrawerLoad = true;
            this.showDrawer();
        }

        const closeTrigger = event.target.closest('[data-erp-form-modal-close]');

        if (closeTrigger) {
            event.preventDefault();
            this.closeModal();
        }

        const drawerClose = event.target.closest('[data-erp-drawer-close]');

        if (drawerClose) {
            event.preventDefault();
            this.closeDrawer();
        }
    },

    bind() {
        // Capture phase so we intercept before Turbo frame / drive navigation.
        document.addEventListener('click', (event) => this.handleDocumentClick(event), true);

        document.addEventListener('submit', (event) => {
            const form = event.target;

            if (! (form instanceof HTMLFormElement)) {
                return;
            }

            if (! form.closest('#erp-form-modal')) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            this.submitFormRequest(form);
        }, true);

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            const overlay = this.overlay();

            if (overlay && ! overlay.hidden) {
                event.preventDefault();
                this.closeModal();
            }

            const drawer = this.drawerOverlay();

            if (drawer && ! drawer.hidden) {
                event.preventDefault();
                this.closeDrawer();
            }
        });
    },

    initFrame(frame) {
        if (! frame) {
            return;
        }

        if (frame.id === 'erp-form-modal') {
            const successMarker = frame.querySelector('[data-erp-modal-success]');

            if (successMarker) {
                this.handleSuccess({
                    message: successMarker.dataset.message ?? '',
                    refresh: successMarker.dataset.refresh !== '0',
                });

                return;
            }

            if (frame.innerHTML.trim() !== '') {
                this.pendingModalLoad = false;
                this.showOverlay();
                Alpine.initTree(frame);
            } else if (! this.pendingModalLoad) {
                this.hideOverlay();
            }
        }

        if (frame.id === 'erp-preview-drawer') {
            if (frame.innerHTML.trim() !== '') {
                this.pendingDrawerLoad = false;
                this.showDrawer();
                Alpine.initTree(frame);
            } else if (! this.pendingDrawerLoad) {
                this.hideDrawer();
            }
        }
    },
};

window.erpModalManager = erpModalManager;
erpModalManager.bind();
erpModalManager.hideOverlay();
erpModalManager.hideDrawer();

const progressBar = () => document.getElementById('turbo-progress');

document.addEventListener('turbo:visit', () => {
    const bar = progressBar();

    if (bar) {
        bar.classList.add('turbo-progress--visible');
        bar.style.width = '30%';
    }
});

document.addEventListener('turbo:frame-render', (event) => {
    const bar = progressBar();

    if (bar) {
        bar.style.width = '100%';
        window.setTimeout(() => {
            bar.classList.remove('turbo-progress--visible');
            bar.style.width = '0';
        }, 200);
    }

    if (event.target?.id === 'erp-main') {
        syncShellFromFrame();
    }

    if (event.target?.id === 'erp-form-modal' || event.target?.id === 'erp-preview-drawer') {
        erpModalManager.initFrame(event.target);
    }
});

function discoveryTokenize(value) {
    return String(value ?? '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, ' ')
        .trim()
        .split(/\s+/)
        .filter(Boolean);
}

function discoveryEntryMatches(entry, tokens) {
    if (! entry || entry.coming_soon || ! entry.url) {
        return false;
    }

    const haystack = String(entry.search_text ?? `${entry.label} ${entry.path}`).toLowerCase();

    return tokens.every((token) => haystack.includes(token));
}

function discoveryResolveUrl(entry) {
    if (entry?.url) {
        return entry.url;
    }

    const routeName = entry?.route;

    if (routeName && window.__erpRoutes && window.__erpRoutes[routeName]) {
        return window.__erpRoutes[routeName];
    }

    return routeName ? `/admin?nav=${encodeURIComponent(routeName)}` : '#';
}

function discoverySearchUrl() {
    return window.__erpFeatureDiscovery?.searchUrl ?? '';
}

function discoveryEntryId(entry) {
    return typeof entry === 'string' ? entry : entry?.id;
}

function discoveryNormalizeStoredEntries(entries) {
    if (! Array.isArray(entries)) {
        return [];
    }

    return entries
        .map((entry) => {
            if (typeof entry === 'string') {
                return null;
            }

            if (! entry?.url) {
                return null;
            }

            return {
                id: entry.id,
                label: entry.label ?? '',
                path: entry.path ?? entry.label ?? '',
                url: entry.url,
                turbo_frame: entry.turbo_frame ?? 'module-workspace-content',
            };
        })
        .filter(Boolean);
}

async function discoveryFetchResults(query, moduleKey = null, limit = 24) {
    const searchUrl = discoverySearchUrl();

    if (! searchUrl || ! String(query ?? '').trim()) {
        return [];
    }

    const params = new URLSearchParams({ q: String(query).trim() });

    if (moduleKey) {
        params.set('module', moduleKey);
    }

    if (limit !== 24) {
        params.set('limit', String(limit));
    }

    const response = await fetch(`${searchUrl}?${params.toString()}`, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
    });

    if (! response.ok) {
        return [];
    }

    const payload = await response.json();

    return Array.isArray(payload.results) ? payload.results : [];
}

document.addEventListener('alpine:init', () => {
    Alpine.data('erpShell', () => ({
        sidebarCollapsed: localStorage.getItem('erp.sidebarCollapsed') === '1',
        mobileNavOpen: false,
        query: '',
        searchOpen: false,
        sidebarResults: [],
        sidebarLoading: false,
        sidebarSearchTimer: null,
        discoveryFavorites: discoveryNormalizeStoredEntries(JSON.parse(localStorage.getItem('erp.discovery.favorites') || '[]')),
        discoveryRecent: discoveryNormalizeStoredEntries(JSON.parse(localStorage.getItem('erp.discovery.recent') || '[]')),
        paletteOpen: false,
        paletteQuery: '',
        paletteHighlightIndex: 0,
        paletteResults: [],
        paletteLoading: false,
        paletteSearchTimer: null,
        favorites: JSON.parse(localStorage.getItem('erp.nav.favorites') || '[]'),

        init() {
            this.$watch('sidebarCollapsed', (value) => {
                localStorage.setItem('erp.sidebarCollapsed', value ? '1' : '0');
            });

            this.$watch('favorites', (value) => {
                localStorage.setItem('erp.nav.favorites', JSON.stringify(value));
            });

            this.$watch('discoveryFavorites', (value) => {
                localStorage.setItem('erp.discovery.favorites', JSON.stringify(value));
            });

            this.$watch('discoveryRecent', (value) => {
                localStorage.setItem('erp.discovery.recent', JSON.stringify(value));
            });

            this.$watch('paletteQuery', () => {
                this.paletteHighlightIndex = 0;
                this.schedulePaletteSearch();
            });

            this.$watch('query', () => {
                this.scheduleSidebarSearch();
            });

            document.addEventListener('erp:page-loaded', () => {
                this.trackRecentVisit();
            });

            this.trackRecentVisit();
        },

        scheduleSidebarSearch() {
            clearTimeout(this.sidebarSearchTimer);

            if (! this.query.trim()) {
                this.sidebarResults = [];
                this.sidebarLoading = false;

                return;
            }

            this.sidebarSearchTimer = setTimeout(() => this.runSidebarSearch(), 150);
        },

        async runSidebarSearch() {
            const tokens = this.query.trim();

            if (! tokens) {
                this.sidebarResults = [];
                this.sidebarLoading = false;

                return;
            }

            this.sidebarLoading = true;

            try {
                this.sidebarResults = await discoveryFetchResults(tokens, null, 12);
            } catch {
                this.sidebarResults = [];
            } finally {
                this.sidebarLoading = false;
            }
        },

        schedulePaletteSearch() {
            clearTimeout(this.paletteSearchTimer);

            if (! this.paletteQuery.trim()) {
                this.paletteResults = [];
                this.paletteLoading = false;

                return;
            }

            this.paletteSearchTimer = setTimeout(() => this.runPaletteSearch(), 150);
        },

        async runPaletteSearch() {
            const tokens = this.paletteQuery.trim();

            if (! tokens) {
                this.paletteResults = [];
                this.paletteLoading = false;

                return;
            }

            this.paletteLoading = true;

            try {
                this.paletteResults = await discoveryFetchResults(tokens, null, 24);
            } catch {
                this.paletteResults = [];
            } finally {
                this.paletteLoading = false;
            }
        },

        get searchHits() {
            return this.sidebarResults;
        },

        get favoriteItems() {
            return this.discoveryFavorites;
        },

        get favoriteDiscoveryItems() {
            return this.discoveryFavorites;
        },

        get recentItems() {
            return this.discoveryRecent;
        },

        get paletteFlatResults() {
            return this.paletteResults;
        },

        get paletteSections() {
            if (! this.paletteQuery.trim()) {
                return [];
            }

            const buckets = {
                features: [],
                reports: [],
                settings: [],
                workflows: [],
                workspaces: [],
            };

            for (const item of this.paletteResults) {
                const key = buckets[item.category] ? item.category : 'features';
                buckets[key].push(item);
            }

            const labels = {
                features: 'Features',
                reports: 'Reports',
                settings: 'Settings',
                workflows: 'Workflows',
                workspaces: 'Workspaces',
            };

            return Object.entries(buckets)
                .filter(([, items]) => items.length > 0)
                .map(([key, items]) => ({
                    key,
                    label: labels[key] ?? key,
                    items,
                }));
        },

        get paletteSelectableItems() {
            if (this.paletteQuery.trim()) {
                return this.paletteSections.flatMap((section) => section.items);
            }

            return [...this.recentItems, ...this.favoriteDiscoveryItems];
        },

        paletteSectionOffset(sectionKey, index) {
            let offset = this.paletteQuery.trim() ? 0 : this.recentItems.length;

            for (const section of this.paletteSections) {
                if (section.key === sectionKey) {
                    return offset + index;
                }

                offset += section.items.length;
            }

            if (! this.paletteQuery.trim()) {
                return this.recentItems.length + index;
            }

            return index;
        },

        openPalette() {
            this.paletteOpen = true;
            this.paletteQuery = '';
            this.paletteHighlightIndex = 0;

            this.$nextTick(() => {
                this.$refs.paletteInput?.focus();
            });
        },

        closePalette() {
            this.paletteOpen = false;
            this.paletteQuery = '';
            this.paletteHighlightIndex = 0;
        },

        movePaletteSelection(step) {
            const total = this.paletteSelectableItems.length;

            if (total === 0) {
                return;
            }

            this.paletteHighlightIndex = (this.paletteHighlightIndex + step + total) % total;
        },

        openPaletteSelection() {
            const item = this.paletteSelectableItems[this.paletteHighlightIndex];

            if (item) {
                this.navigatePaletteItem(item);
            }
        },

        navigatePaletteItem(item) {
            if (! item?.url) {
                return;
            }

            this.recordRecent(item);
            this.closePalette();
            window.Turbo.visit(item.url, { frame: 'erp-main', action: 'advance' });
        },

        openPaletteItemNewTab(item) {
            if (! item?.url) {
                return;
            }

            this.recordRecent(item);
            window.open(item.url, '_blank', 'noopener,noreferrer');
        },

        async copyPaletteItemLink(item) {
            if (! item?.url) {
                return;
            }

            try {
                await navigator.clipboard.writeText(new URL(item.url, window.location.origin).href);
            } catch {
                // Clipboard may be unavailable in non-secure contexts.
            }
        },

        isDiscoveryFavorite(id) {
            return this.discoveryFavorites.some((entry) => discoveryEntryId(entry) === id);
        },

        toggleDiscoveryFavorite(idOrItem) {
            const id = discoveryEntryId(idOrItem);
            const item = typeof idOrItem === 'object' && idOrItem !== null
                ? idOrItem
                : this.paletteResults.find((entry) => entry.id === id)
                    ?? this.sidebarResults.find((entry) => entry.id === id);

            if (! id) {
                return;
            }

            if (this.isDiscoveryFavorite(id)) {
                this.discoveryFavorites = this.discoveryFavorites.filter((entry) => discoveryEntryId(entry) !== id);

                return;
            }

            if (! item?.url) {
                return;
            }

            this.discoveryFavorites = [
                {
                    id: item.id,
                    label: item.label,
                    path: item.path ?? item.label,
                    url: item.url,
                    turbo_frame: item.turbo_frame ?? 'module-workspace-content',
                },
                ...this.discoveryFavorites,
            ].slice(0, 12);
        },

        recordRecent(item) {
            if (! item?.url) {
                return;
            }

            const entry = {
                id: item.id ?? item.route ?? item.url,
                label: item.label ?? '',
                path: item.path ?? item.label ?? '',
                url: item.url,
                turbo_frame: item.turbo_frame ?? 'module-workspace-content',
            };

            this.discoveryRecent = [
                entry,
                ...this.discoveryRecent.filter((existing) => discoveryEntryId(existing) !== entry.id),
            ].slice(0, 20);
        },

        trackRecentVisit() {
            const meta = document.getElementById('erp-route-meta');

            if (! meta) {
                return;
            }

            const route = meta.dataset.route ?? '';
            const title = meta.dataset.title ?? '';

            if (! route || ! title) {
                return;
            }

            this.recordRecent({
                id: route,
                label: title,
                path: title,
                url: `${window.location.pathname}${window.location.search}`,
            });
        },

        routeUrl(routeName) {
            if (window.__erpRoutes && window.__erpRoutes[routeName]) {
                return window.__erpRoutes[routeName];
            }

            return `/admin?nav=${encodeURIComponent(routeName)}`;
        },

        isFavorite(route) {
            return this.favorites.includes(route);
        },

        toggleFavorite(route) {
            if (this.isFavorite(route)) {
                this.favorites = this.favorites.filter((entry) => entry !== route);
            } else {
                this.favorites = [...this.favorites, route].slice(0, 8);
            }
        },

        clearSearch() {
            this.query = '';
            this.searchOpen = false;
        },

        toggleSidebar() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
        },

        toggleMobileNav() {
            this.mobileNavOpen = !this.mobileNavOpen;
        },

        closeMobileNav() {
            this.mobileNavOpen = false;
        },
    }));

    Alpine.data('moduleWorkspaceSearch', (moduleKey = null) => ({
        query: '',
        open: false,
        hits: [],
        loading: false,
        searchTimer: null,
        moduleKey: moduleKey || null,

        scheduleSearch() {
            clearTimeout(this.searchTimer);
            this.$dispatch('module-workspace-search', { query: this.query });

            if (! this.query.trim()) {
                this.hits = [];
                this.loading = false;

                return;
            }

            this.searchTimer = setTimeout(() => this.runSearch(), 150);
        },

        async runSearch() {
            const tokens = this.query.trim();

            if (! tokens) {
                this.hits = [];
                this.loading = false;

                return;
            }

            this.loading = true;

            try {
                this.hits = await discoveryFetchResults(tokens, this.moduleKey, 16);
            } catch {
                this.hits = [];
            } finally {
                this.loading = false;
            }
        },

        clear() {
            this.query = '';
            this.hits = [];
            this.loading = false;
            this.open = false;
            this.$dispatch('module-workspace-search', { query: '' });
        },
    }));

    Alpine.data('moduleWorkspaceShell', () => ({
        query: '',

        init() {
            this.$watch('query', () => this.applyTabFilter());
            this.applyTabFilter();
        },

        applyTabFilter() {
            const tokens = discoveryTokenize(this.query);
            const tabs = this.$root.querySelectorAll('[data-workspace-tab]');

            tabs.forEach((tab) => {
                const label = tab.dataset.searchLabel ?? '';

                if (tokens.length === 0) {
                    tab.removeAttribute('data-workspace-tab-hidden');
                    return;
                }

                const matches = tokens.every((token) => label.includes(token));

                if (matches) {
                    tab.removeAttribute('data-workspace-tab-hidden');
                } else {
                    tab.setAttribute('data-workspace-tab-hidden', 'true');
                }
            });
        },
    }));

    Alpine.data('navGroup', (groupId, initiallyOpen = false) => ({
        open: initiallyOpen,

        init() {
            if (this.$el.dataset.navGroupOpen === '1') {
                this.open = true;
            } else {
                const stored = localStorage.getItem(`erp.nav.${groupId}`);

                if (stored !== null) {
                    this.open = stored === '1';
                }
            }

            document.addEventListener('erp:page-loaded', () => {
                if (this.$el.dataset.navGroupOpen === '1') {
                    this.open = true;
                }
            });
        },

        toggle() {
            this.open = !this.open;
            localStorage.setItem(`erp.nav.${groupId}`, this.open ? '1' : '0');
        },
    }));

    Alpine.data('erpDataTable', (config = {}) => ({
        query: '',
        debouncedQuery: '',
        debounceTimer: null,
        activeChip: config.chips?.[0]?.id ?? 'all',
        filterValues: {},
        filterOpen: false,
        pageSize: Number(localStorage.getItem(`erp.table.${config.tableId ?? 'default'}.pageSize`) || 25),
        currentPage: 1,
        exportOpen: false,
        selectable: config.selectable ?? false,
        selected: new Set(),
        tableId: config.tableId ?? null,
        exportFilename: config.exportFilename ?? 'export',

        _tableRevision: 0,

        init() {
            const bumpTableRevision = () => {
                this._tableRevision += 1;
            };

            this.$watch('query', (value) => {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => {
                    this.debouncedQuery = value;
                    this.currentPage = 1;
                    bumpTableRevision();
                }, 250);
            });

            this.$watch('filterValues', () => {
                this.currentPage = 1;
                bumpTableRevision();
            });

            this.$watch('currentPage', bumpTableRevision);
            this.$watch('activeChip', bumpTableRevision);
        },

        get selectedCount() {
            return this.selected.size;
        },

        matches(text) {
            if (!this.debouncedQuery.trim()) {
                return true;
            }

            return String(text).toLowerCase().includes(this.debouncedQuery.trim().toLowerCase());
        },

        hasActiveFilters() {
            return Object.entries(this.filterValues).some(([, value]) => {
                if (value === null || value === undefined || value === '' || value === 'all') {
                    return false;
                }

                return true;
            });
        },

        usesClientPagination(rowIndex = null) {
            if (rowIndex === null) {
                return false;
            }

            if (this.debouncedQuery.trim() !== '' || this.hasActiveFilters() || this.activeChip !== 'all') {
                return false;
            }

            return true;
        },

        rowVisible(searchText, chipValue = null, filters = {}, rowIndex = null) {
            if (this.usesClientPagination(rowIndex)) {
                const index = Number(rowIndex);
                const start = (this.currentPage - 1) * this.pageSize + 1;
                const end = this.currentPage * this.pageSize;

                if (index < start || index > end) {
                    return false;
                }
            }

            if (this.activeChip !== 'all' && chipValue !== null && chipValue !== '' && String(chipValue) !== String(this.activeChip)) {
                return false;
            }

            return this.matches(searchText) && this.matchesFilters(filters);
        },

        resetFilters() {
            this.filterValues = {};
            this.currentPage = 1;
        },

        countVisibleRows() {
            if (! this.tableId) {
                return 0;
            }

            const table = document.getElementById(this.tableId);

            if (! table) {
                return 0;
            }

            return [...table.querySelectorAll('tbody tr')].filter((row) => {
                if (row.querySelector('[data-empty-state], .erp-empty-state')) {
                    return false;
                }

                return row.offsetParent !== null;
            }).length;
        },

        get showNoResults() {
            void this._tableRevision;

            const searching = this.debouncedQuery.trim() !== '' || this.hasActiveFilters() || this.activeChip !== 'all';

            if (! searching) {
                return false;
            }

            return this.countVisibleRows() === 0;
        },

        matchesFilters(filters = {}) {
            return Object.entries(this.filterValues).every(([key, expected]) => {
                if (expected === null || expected === undefined || expected === '' || expected === 'all') {
                    return true;
                }

                if (key.endsWith('_from')) {
                    const actual = filters[key.replace(/_from$/, '')];

                    return !actual || String(actual) >= String(expected);
                }

                if (key.endsWith('_to')) {
                    const actual = filters[key.replace(/_to$/, '')];

                    return !actual || String(actual) <= String(expected);
                }

                const actual = filters[key];

                if (Array.isArray(actual)) {
                    return actual.map(String).includes(String(expected));
                }

                return String(actual ?? '') === String(expected);
            });
        },

        setPageSize(size) {
            this.pageSize = Number(size);
            this.currentPage = 1;
            localStorage.setItem(`erp.table.${this.tableId ?? 'default'}.pageSize`, String(this.pageSize));
        },

        nextPage() {
            this.currentPage += 1;
        },

        previousPage() {
            this.currentPage = Math.max(1, this.currentPage - 1);
        },

        setChip(id) {
            this.activeChip = id;
        },

        isSelected(id) {
            return this.selected.has(String(id));
        },

        toggleRow(id, event) {
            const key = String(id);

            if (event.target.checked) {
                this.selected.add(key);
            } else {
                this.selected.delete(key);
            }
        },

        toggleAll(event) {
            const table = document.getElementById(this.tableId);

            if (!table) {
                return;
            }

            table.querySelectorAll('tbody tr[data-row-id]').forEach((row) => {
                const id = row.dataset.rowId;
                const checkbox = row.querySelector('input[type="checkbox"]');
                const visible = row.offsetParent !== null;

                if (!visible || !checkbox) {
                    return;
                }

                checkbox.checked = event.target.checked;

                if (event.target.checked) {
                    this.selected.add(id);
                    row.dataset.selected = 'true';
                } else {
                    this.selected.delete(id);
                    delete row.dataset.selected;
                }
            });
        },

        exportTable(format = 'csv') {
            if (format !== 'csv') {
                return;
            }

            this.exportOpen = false;

            const table = this.tableId
                ? this.$el.querySelector(`#${CSS.escape(this.tableId)}`)
                : this.$el.querySelector('table');

            if (!table) {
                return;
            }

            const rows = [];
            const headers = [...table.querySelectorAll('thead th')]
                .filter((th) => !th.classList.contains('erp-table-checkbox-col'))
                .filter((th) => !th.classList.contains('erp-table-actions-col'))
                .map((th) => th.textContent.trim());

            if (headers.length) {
                rows.push(headers);
            }

            let dataRowCount = 0;

            table.querySelectorAll('tbody tr').forEach((row) => {
                if (!this.isExportableRow(row)) {
                    return;
                }

                const cells = [...row.querySelectorAll('td')]
                    .filter((td) => !td.classList.contains('erp-table-checkbox-col'))
                    .filter((td) => !td.classList.contains('erp-table-actions-col'))
                    .map((td) => td.textContent.trim().replace(/\s+/g, ' '));

                if (cells.length) {
                    rows.push(cells);
                    dataRowCount += 1;
                }
            });

            if (rows.length === 0 || (rows.length === 1 && headers.length > 0 && dataRowCount === 0)) {
                window.alert(this.$el?.dataset?.exportEmptyMessage ?? 'No rows to export.');

                return;
            }

            const csv = rows.map((row) => row.map((cell) => `"${cell.replace(/"/g, '""')}"`).join(',')).join('\n');
            const blob = new Blob([`\uFEFF${csv}`], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.href = url;
            link.download = `${this.exportFilename}${dataRowCount === 0 ? '-headers-only' : ''}.csv`;
            document.body.appendChild(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(url);
        },

        isExportableRow(row) {
            if (row.offsetParent === null || row.hidden) {
                return false;
            }

            if (row.querySelector('[data-export-skip], [data-empty-state], .erp-empty-state')) {
                return false;
            }

            return true;
        },

        exportSelected() {
            this.exportTable('csv');
        },
    }));

    Alpine.data('jobCardsRegister', (config = {}) => ({
        columns: config.columns ?? [],
        presets: config.presets ?? [],
        indexUrl: config.indexUrl ?? '',
        columnsOpen: false,
        visibleColumns: {},
        customViews: [],
        storageKeyColumns: 'erp.job-cards.register.columns',
        storageKeyViews: 'erp.job-cards.register.savedViews',

        init() {
            const storedColumns = JSON.parse(localStorage.getItem(this.storageKeyColumns) || 'null');

            this.columns.forEach((column) => {
                this.visibleColumns[column.key] = storedColumns?.[column.key] ?? column.default ?? true;
            });

            this.customViews = JSON.parse(localStorage.getItem(this.storageKeyViews) || '[]');
        },

        isColumnVisible(key) {
            return this.visibleColumns[key] !== false;
        },

        toggleColumn(key) {
            this.visibleColumns[key] = !this.isColumnVisible(key);
            localStorage.setItem(this.storageKeyColumns, JSON.stringify(this.visibleColumns));
        },

        buildUrl(query = {}) {
            const params = new URLSearchParams();

            Object.entries(query).forEach(([name, value]) => {
                if (value !== null && value !== undefined && value !== '' && value !== false) {
                    params.set(name, String(value));
                }
            });

            const qs = params.toString();

            return qs ? `${this.indexUrl}?${qs}` : this.indexUrl;
        },

        applyPreset(key) {
            if (!key) {
                return;
            }

            const preset = this.presets.find((item) => item.key === key);

            if (preset) {
                window.location.href = this.buildUrl(preset.query ?? {});

                return;
            }

            const custom = this.customViews.find((view) => view.id === key);

            if (custom) {
                window.location.href = this.buildUrl(custom.query ?? {});
            }
        },

        saveCurrentView() {
            const label = window.prompt('Name this view');

            if (!label || !label.trim()) {
                return;
            }

            const query = Object.fromEntries(new URLSearchParams(window.location.search));
            delete query.page;

            const view = {
                id: `custom-${Date.now()}`,
                label: label.trim(),
                query,
            };

            this.customViews = [...this.customViews, view];
            localStorage.setItem(this.storageKeyViews, JSON.stringify(this.customViews));
        },
    }));

    Alpine.data('tableSearch', () => ({
        query: '',
        debouncedQuery: '',
        debounceTimer: null,
        activeChip: 'all',

        init() {
            this.$watch('query', (value) => {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => {
                    this.debouncedQuery = value;
                }, 250);
            });
        },

        matches(text) {
            if (!this.debouncedQuery.trim()) {
                return true;
            }

            return String(text).toLowerCase().includes(this.debouncedQuery.trim().toLowerCase());
        },

        rowVisible(searchText, chipValue = null) {
            if (this.activeChip !== 'all' && chipValue !== null && chipValue !== '' && String(chipValue) !== String(this.activeChip)) {
                return false;
            }

            return this.matches(searchText);
        },
    }));

    Alpine.data('permissionMatrixWorkspace', (config) => ({
        activeModule: '',
        search: '',
        modules: config.modules ?? [],
        granted: [...(config.granted ?? [])],
        grantedSet: new Set(config.granted ?? []),
        uncatalogued: [...(config.uncatalogued ?? [])],
        editable: config.editable ?? false,
        storageKey: config.storageKey ?? null,
        lastUpdated: config.lastUpdated ?? null,
        renderLimit: 30,
        renderBatch: 20,

        init() {
            const stored = this.storageKey ? localStorage.getItem(this.storageKey) : null;

            if (stored === 'all') {
                localStorage.removeItem(this.storageKey);
            }

            const storedIsValid = stored && stored !== 'all' && this.modules.some((module) => module.key === stored);

            this.activeModule = storedIsValid ? stored : (this.modules[0]?.key ?? '');

            this.$watch('activeModule', (value) => {
                if (this.storageKey && value) {
                    localStorage.setItem(this.storageKey, value);
                }

                this.resetRenderWindow();
            });

            this.$watch('search', () => this.resetRenderWindow());
        },

        get activeModuleData() {
            return this.modules.find((module) => module.key === this.activeModule) ?? null;
        },

        get activeModuleLabel() {
            return this.activeModuleData?.label ?? '';
        },

        get activeColumns() {
            return this.activeModuleData?.columns ?? [];
        },

        get gridTemplateColumns() {
            const count = Math.max(this.activeColumns.length, 1);

            return `grid-template-columns: 10rem repeat(${count}, 3.25rem)`;
        },

        get visibleRows() {
            const rows = (this.activeModuleData?.rows ?? []).map((row) => ({
                ...row,
                moduleKey: this.activeModule,
                moduleLabel: this.activeModuleLabel,
            }));

            const query = this.search.trim().toLowerCase();

            if (! query) {
                return rows;
            }

            return rows.filter((row) => String(row.label).toLowerCase().includes(query));
        },

        get pagedRows() {
            return this.visibleRows.slice(0, this.renderLimit);
        },

        get hasMoreRows() {
            return this.pagedRows.length < this.visibleRows.length;
        },

        get bulkColumnActions() {
            return this.activeColumns.filter((column) => this.visibleRows.some((row) => row.cells[column.key]));
        },

        get activeModuleStats() {
            const module = this.activeModuleData;

            if (! module) {
                return { capabilities: 0, permissionsEnabled: 0, totalPermissions: 0 };
            }

            const permissions = [];

            module.rows.forEach((row) => {
                Object.values(row.cells).forEach((permission) => {
                    if (permission) {
                        permissions.push(permission);
                    }
                });
            });

            return {
                capabilities: module.rows.length,
                permissionsEnabled: permissions.filter((permission) => this.grantedSet.has(permission)).length,
                totalPermissions: permissions.length,
            };
        },

        isGranted(permission) {
            return permission ? this.grantedSet.has(permission) : false;
        },

        setGranted(permission, enabled) {
            if (! this.editable || ! permission) {
                return;
            }

            if (enabled) {
                this.grantedSet.add(permission);
            } else {
                this.grantedSet.delete(permission);
            }

            this.granted = Array.from(this.grantedSet);
        },

        toggle(permission) {
            this.setGranted(permission, ! this.isGranted(permission));
        },

        modulePermissions() {
            const permissions = [];

            this.visibleRows.forEach((row) => {
                this.activeColumns.forEach((column) => {
                    const permission = row.cells[column.key];

                    if (permission) {
                        permissions.push(permission);
                    }
                });
            });

            return permissions;
        },

        bulkEnableModule() {
            this.modulePermissions().forEach((permission) => this.setGranted(permission, true));
        },

        bulkDisableModule() {
            this.modulePermissions().forEach((permission) => this.setGranted(permission, false));
        },

        bulkEnableColumn(columnKey) {
            this.visibleRows.forEach((row) => {
                const permission = row.cells[columnKey];

                if (permission) {
                    this.setGranted(permission, true);
                }
            });
        },

        setModule(moduleKey) {
            this.activeModule = moduleKey;
        },

        resetRenderWindow() {
            this.renderLimit = 30;
        },

        onTableScroll(event) {
            const element = event.target;

            if (element.scrollTop + element.clientHeight >= element.scrollHeight - 96) {
                this.renderLimit = Math.min(this.visibleRows.length, this.renderLimit + this.renderBatch);
            }
        },
    }));

    function workspaceHubSearchText(card) {
        const label = String(card.label ?? '').toLowerCase();
        const aliases = [];

        if (label.includes('general ledger') || label === 'gl report') {
            aliases.push('gl ledger');
        }

        if (label.includes('profit') || label.includes('p&l')) {
            aliases.push('pnl income statement');
        }

        if (label.includes('trial balance')) {
            aliases.push('tb');
        }

        if (label.includes('chart of accounts')) {
            aliases.push('coa accounts');
        }

        return [
            card.search_text,
            card.label,
            card.description,
            card.group_label,
            ...aliases,
            ...(Array.isArray(card.keywords) ? card.keywords : []),
        ]
            .filter(Boolean)
            .join(' ')
            .toLowerCase();
    }

    Alpine.data('workspaceHub', (cards = []) => ({
        query: '',
        normalizedQuery: '',
        visibleCardIds: [],
        cards: cards.map((card) => ({
            ...card,
            search_text: workspaceHubSearchText(card),
        })),

        init() {
            this.syncVisibleCards();

            this.$watch('query', (value) => {
                this.normalizedQuery = this.normalizeQuery(value);
                this.syncVisibleCards();
            });
        },

        normalizeQuery(value) {
            return String(value ?? '')
                .toLowerCase()
                .trim()
                .replace(/\s+/g, ' ');
        },

        syncVisibleCards() {
            this.normalizedQuery = this.normalizeQuery(this.query);

            if (! this.normalizedQuery) {
                this.visibleCardIds = this.cards.map((card) => card.id);

                return;
            }

            const tokens = this.normalizedQuery.split(' ').filter(Boolean);

            this.visibleCardIds = this.cards
                .filter((card) => tokens.every((token) => card.search_text.includes(token)))
                .map((card) => card.id);
        },

        get visibleCount() {
            return this.visibleCardIds.length;
        },

        cardVisible(cardId) {
            return this.visibleCardIds.includes(cardId);
        },

        groupVisible(groupLabel) {
            return this.cards.some(
                (card) => card.group_label === groupLabel && this.visibleCardIds.includes(card.id),
            );
        },
    }));

    Alpine.data('settingsControlCenter', (cards = []) => ({
        query: '',
        activeFilter: localStorage.getItem('erp.settingsControlCenter.filter') || 'all',
        viewMode: localStorage.getItem('erp.settingsControlCenter.viewMode') || 'grid',
        cards,

        init() {
            this.$watch('activeFilter', (value) => {
                localStorage.setItem('erp.settingsControlCenter.filter', value);
            });

            this.$watch('viewMode', (value) => {
                localStorage.setItem('erp.settingsControlCenter.viewMode', value);
            });
        },

        get visibleCount() {
            return this.cards.filter((card) => this.cardVisible(card.id)).length;
        },

        matches(searchText) {
            if (! this.query.trim()) {
                return true;
            }

            return String(searchText).toLowerCase().includes(this.query.trim().toLowerCase());
        },

        cardVisible(cardId) {
            const card = this.cards.find((entry) => entry.id === cardId);

            if (! card) {
                return false;
            }

            if (! this.matches(card.search_text)) {
                return false;
            }

            if (this.query.trim()) {
                return true;
            }

            if (this.activeFilter !== 'all' && card.domain_slug !== this.activeFilter) {
                return false;
            }

            return true;
        },

        setFilter(slug) {
            this.activeFilter = slug;
        },

        setViewMode(mode) {
            this.viewMode = mode;
        },
    }));

    Alpine.data('formsControlCenter', (config = {}) => ({
        query: '',
        activeCategory: localStorage.getItem('erp.formsControlCenter.category') || 'all',
        auditMode: false,
        importModalOpen: false,
        importMessage: '',
        cards: config.cards ?? [],
        exportPayload: config.exportPayload ?? {},
        auditUrl: config.auditUrl ?? null,

        init() {
            this.$watch('activeCategory', (value) => {
                localStorage.setItem('erp.formsControlCenter.category', value);
            });
        },

        get visibleCount() {
            return this.cards.filter((card) => this.cardVisible(card.id)).length;
        },

        normalizeQuery(value) {
            return String(value ?? '')
                .toLowerCase()
                .trim()
                .replace(/\s+/g, ' ');
        },

        matchesSearch(card) {
            const normalized = this.normalizeQuery(this.query);

            if (! normalized) {
                return true;
            }

            const tokens = normalized.split(' ').filter(Boolean);
            const haystack = String(card.search_text ?? '').toLowerCase();

            return tokens.every((token) => haystack.includes(token));
        },

        cardVisible(cardId) {
            const card = this.cards.find((entry) => entry.id === cardId);

            if (! card) {
                return false;
            }

            if (this.auditMode && ! card.has_governance_issues) {
                return false;
            }

            if (! this.matchesSearch(card)) {
                return false;
            }

            if (this.activeCategory === 'all') {
                return true;
            }

            return card.category_slug === this.activeCategory;
        },

        sectionVisible(categorySlug) {
            if (this.activeCategory !== 'all' && this.activeCategory !== categorySlug) {
                return false;
            }

            return this.cards.some(
                (card) => ! card.comingSoon
                    && card.category_slug === categorySlug
                    && this.cardVisible(card.id),
            );
        },

        plannedSectionVisible() {
            if (this.auditMode) {
                return false;
            }

            return this.cards.some(
                (card) => card.comingSoon && this.cardVisible(card.id),
            );
        },

        setCategory(slug) {
            this.activeCategory = slug;
        },

        exportConfiguration() {
            const payload = JSON.stringify(this.exportPayload, null, 2);
            const blob = new Blob([payload], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const stamp = new Date().toISOString().slice(0, 10);
            const anchor = document.createElement('a');

            anchor.href = url;
            anchor.download = `jana-forms-configuration-${stamp}.json`;
            anchor.click();

            URL.revokeObjectURL(url);
        },

        handleImportSelect(event) {
            const file = event.target.files?.[0];

            if (! file) {
                return;
            }

            this.importMessage = 'Import applies server-side configuration changes and is not yet enabled from this screen. Use Export to download a snapshot for review.';
            event.target.value = '';
        },

        auditForms() {
            this.auditMode = true;
            this.activeCategory = 'all';
            this.query = '';

            this.$nextTick(() => {
                document.getElementById('forms-health-widget')?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest',
                });
            });

            if (this.auditUrl && this.visibleCount === 0) {
                window.location.href = this.auditUrl;
            }
        },
    }));

    Alpine.data('roleGovernanceDashboard', () => ({
        query: '',
        drawerOpen: false,
        drawerRole: null,
        previewRole: null,
        previewTop: 0,
        previewLeft: 0,

        matches(text) {
            if (! this.query.trim()) {
                return true;
            }

            return String(text).toLowerCase().includes(this.query.trim().toLowerCase());
        },

        openDrawer(role) {
            this.drawerRole = role;
            this.drawerOpen = true;
        },

        closeDrawer() {
            this.drawerOpen = false;
            this.drawerRole = null;
        },

        setPreview(role, event) {
            this.previewRole = role;
            this._previewAnchor = event?.currentTarget?.querySelector('[data-role-preview-anchor]')
                ?? event?.currentTarget
                ?? null;
            this.$nextTick(() => this.placePreview());
        },

        clearPreview() {
            this.previewRole = null;
            this._previewAnchor = null;
        },

        placePreview() {
            const anchor = this._previewAnchor;

            if (! anchor || ! this.previewRole) {
                return;
            }

            const rect = anchor.getBoundingClientRect();
            const gap = 6;
            const cardWidth = 256;
            const card = this.$refs.rolePreview;
            const cardHeight = card?.offsetHeight ?? 180;
            const spaceBelow = window.innerHeight - rect.bottom;
            let top = rect.bottom + gap;

            if (spaceBelow < cardHeight + gap && rect.top > cardHeight + gap) {
                top = rect.top - cardHeight - gap;
            }

            let left = rect.left;

            if (left + cardWidth > window.innerWidth - 8) {
                left = window.innerWidth - cardWidth - 8;
            }

            this.previewTop = Math.max(8, top);
            this.previewLeft = Math.max(8, left);
        },

        get previewStyle() {
            return {
                top: `${this.previewTop}px`,
                left: `${this.previewLeft}px`,
            };
        },

        openRole(url) {
            if (window.Turbo) {
                window.Turbo.visit(url, { action: 'advance' });
            } else {
                window.location.href = url;
            }
        },
    }));

    Alpine.data('erpRowActionsMenu', (align = 'right') => ({
        open: false,
        align,
        menuTop: 0,
        menuLeft: 0,
        menuTransform: 'translateX(-100%)',
        _outsideLockedUntil: 0,
        _menuHost: null,

        init() {
            this.close();
        },

        toggle(event) {
            event?.stopPropagation?.();

            if (this.open) {
                this.close();

                return;
            }

            const menu = this.$refs.menu;

            if (menu && ! menu.querySelector('a, button, form')) {
                return;
            }

            if (window.__erpOpenRowMenu && window.__erpOpenRowMenu !== this) {
                window.__erpOpenRowMenu.close();
            }

            window.__erpOpenRowMenu = this;
            this._outsideLockedUntil = Date.now() + 250;
            this.open = true;
            this.mountMenu();

            if (menu) {
                menu.classList.add('erp-row-actions-menu--open');
            }

            this.$nextTick(() => {
                this.placeMenu();
                requestAnimationFrame(() => this.placeMenu());
            });
        },

        close() {
            this.open = false;

            const menu = this.$refs.menu;

            if (menu) {
                menu.classList.remove('erp-row-actions-menu--open');
            }

            this.restoreMenu();

            if (window.__erpOpenRowMenu === this) {
                window.__erpOpenRowMenu = null;
            }
        },

        closeFromOutside() {
            if (Date.now() < this._outsideLockedUntil) {
                return;
            }

            this.close();
        },

        mountMenu() {
            const menu = this.$refs.menu;

            if (! menu || menu.parentElement === document.body) {
                return;
            }

            this._menuHost = this.$el;
            menu.__erpMenuHost = this._menuHost;
            document.body.appendChild(menu);
        },

        restoreMenu() {
            const menu = this.$refs.menu;
            const host = this._menuHost ?? menu?.__erpMenuHost;

            if (! menu || ! host || menu.parentElement !== document.body) {
                return;
            }

            host.appendChild(menu);
            this._menuHost = null;
        },

        placeMenu() {
            const trigger = this.$refs.trigger;

            if (! trigger || ! this.open) {
                return;
            }

            const menu = this.$refs.menu;

            if (menu) {
                menu.classList.add('erp-row-actions-menu--open');
            }

            const rect = trigger.getBoundingClientRect();
            const gap = 4;
            const menuWidth = menu?.offsetWidth ?? 192;
            const menuHeight = menu?.offsetHeight ?? 0;
            const spaceBelow = window.innerHeight - rect.bottom;
            const spaceAbove = rect.top;

            this.menuTop = spaceBelow < menuHeight + gap && spaceAbove > menuHeight + gap
                ? rect.top - menuHeight - gap
                : rect.bottom + gap;

            if (this.align === 'left') {
                this.menuLeft = Math.max(8, rect.left);
                this.menuTransform = 'none';
            } else {
                this.menuLeft = Math.min(window.innerWidth - 8, rect.right);
                this.menuTransform = 'translateX(-100%)';
            }

            if (this.menuLeft - menuWidth < 8) {
                this.menuLeft = Math.min(rect.left, window.innerWidth - menuWidth - 8);
                this.menuTransform = 'none';
            }
        },

        get menuStyle() {
            if (! this.open) {
                return {};
            }

            return {
                position: 'fixed',
                top: `${this.menuTop}px`,
                left: `${this.menuLeft}px`,
                transform: this.menuTransform,
                zIndex: 99999,
            };
        },
    }));

    Alpine.data('chartOfAccountsExplorer', (bootstrap = {}) => ({
        stats: bootstrap.stats ?? { total: 0, active: 0, locked: 0, groups: 0 },
        types: bootstrap.types ?? [],
        routes: bootstrap.routes ?? {},
        permissions: bootstrap.permissions ?? {},
        selectedTypeId: bootstrap.initialTypeId ?? null,
        selectedGroupId: bootstrap.initialGroupId ?? null,
        selectedAccountId: null,
        groups: [],
        accounts: [],
        groupsLoading: false,
        accountsLoading: false,
        query: '',
        searchResults: [],
        searchOpen: false,
        searchLoading: false,
        drawerOpen: false,
        panel: null,
        panelLoading: false,
        mobileStack: 'types',

        init() {
            if (this.selectedTypeId) {
                this.loadGroups(this.selectedTypeId, this.selectedGroupId);
            }

            this.$watch('selectedTypeId', () => this.syncUrl());
            this.$watch('selectedGroupId', () => this.syncUrl());
        },

        async loadGroups(typeId, selectGroupId = null) {
            this.selectedTypeId = typeId;
            this.groupsLoading = true;
            this.groups = [];
            this.accounts = [];
            this.selectedGroupId = null;

            try {
                const response = await fetch(`${this.routes.groups}?type_id=${typeId}`, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (! response.ok) {
                    return;
                }

                const data = await response.json();
                this.groups = data.groups ?? [];

                const groupId = selectGroupId ?? this.groups[0]?.id ?? null;

                if (groupId) {
                    await this.loadAccounts(groupId);
                }
            } finally {
                this.groupsLoading = false;
            }

            if (window.innerWidth < 1024) {
                this.mobileStack = 'groups';
            }
        },

        async loadAccounts(groupId) {
            this.selectedGroupId = groupId;
            this.accountsLoading = true;
            this.accounts = [];

            try {
                const response = await fetch(`${this.routes.accounts}?group_id=${groupId}`, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (! response.ok) {
                    return;
                }

                const data = await response.json();
                this.accounts = data.accounts ?? [];
            } finally {
                this.accountsLoading = false;
            }

            if (window.innerWidth < 1024) {
                this.mobileStack = 'accounts';
            }
        },

        selectType(typeId) {
            this.loadGroups(typeId);
        },

        selectGroup(groupId) {
            this.loadAccounts(groupId);
        },

        async runSearch() {
            const term = this.query.trim();
            this.searchOpen = term !== '';

            if (! term) {
                this.searchResults = [];

                return;
            }

            this.searchLoading = true;

            try {
                const response = await fetch(`${this.routes.search}?q=${encodeURIComponent(term)}`, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (response.ok) {
                    const data = await response.json();
                    this.searchResults = data.results ?? [];
                }
            } finally {
                this.searchLoading = false;
            }
        },

        async goToSearchResult(hit) {
            this.query = '';
            this.searchOpen = false;
            this.searchResults = [];

            if (hit.type_id) {
                await this.loadGroups(hit.type_id, hit.group_id ?? null);
            }

            if (hit.account_id) {
                this.openDrawer(hit.account_id);
            }
        },

        panelUrl(accountId) {
            return (this.routes.panel ?? '').replace('__ID__', String(accountId));
        },

        deactivateUrl(accountId) {
            return (this.routes.deactivate ?? '').replace('__ID__', String(accountId));
        },

        async openDrawer(accountId) {
            this.selectedAccountId = accountId;
            this.drawerOpen = true;
            this.panelLoading = true;
            this.panel = null;

            try {
                const response = await fetch(this.panelUrl(accountId), {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (response.ok) {
                    const data = await response.json();
                    this.panel = data.panel ?? null;
                }
            } finally {
                this.panelLoading = false;
            }
        },

        closeDrawer() {
            this.drawerOpen = false;
            this.panel = null;
            this.selectedAccountId = null;
        },

        async deactivateAccount(accountId = null) {
            const id = accountId ?? this.panel?.id;

            if (! id || ! this.permissions.edit) {
                return;
            }

            const token = document.querySelector('meta[name="csrf-token"]')?.content;

            const response = await fetch(this.deactivateUrl(id), {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token ?? '',
                },
            });

            if (! response.ok) {
                return;
            }

            const data = await response.json();

            if (this.panel?.id === id) {
                this.panel.status = data.status;
                this.panel.status_label = data.status_label;
            }

            const row = this.accounts.find((item) => item.id === id);

            if (row) {
                row.status = data.status;
                row.status_label = data.status_label;
            }

            if (data.status === 'inactive') {
                this.stats.active = Math.max(0, this.stats.active - 1);
            }
        },

        mobileBack() {
            if (this.mobileStack === 'accounts') {
                this.mobileStack = 'groups';

                return;
            }

            if (this.mobileStack === 'groups') {
                this.mobileStack = 'types';
            }
        },

        mobileTitle() {
            if (this.mobileStack === 'groups') {
                const type = this.types.find((item) => item.id === this.selectedTypeId);

                return type?.name ?? '';
            }

            if (this.mobileStack === 'accounts') {
                const group = this.groups.find((item) => item.id === this.selectedGroupId);

                return group?.name ?? '';
            }

            return '';
        },

        syncUrl() {
            if (! window.history?.replaceState) {
                return;
            }

            const params = new URLSearchParams();

            if (this.selectedTypeId) {
                params.set('type_id', String(this.selectedTypeId));
            }

            if (this.selectedGroupId) {
                params.set('group_id', String(this.selectedGroupId));
            }

            const query = params.toString();
            const url = query ? `${window.location.pathname}?${query}` : window.location.pathname;

            window.history.replaceState({}, '', url);
        },
    }));

    Alpine.data('smsCampaignForm', (bootstrap = {}) => ({
        previewUrl: bootstrap.previewUrl,
        sendMode: 'immediate',
        messageTemplate: '',
        preview: null,

        onTemplateChange(event) {
            const option = event.target.selectedOptions[0];
            const body = option?.dataset?.body;

            if (body) {
                this.messageTemplate = body;
            }
        },

        async runPreview() {
            const templateId = document.querySelector('[name=communication_template_id]')?.value || null;

            const response = await fetch(this.previewUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    communication_template_id: templateId || null,
                    message_template: this.messageTemplate,
                }),
            });

            if (response.ok) {
                this.preview = await response.json();
            }
        },
    }));

    Alpine.data('erpNotificationBell', (bootstrap = {}) => ({
        routes: bootstrap.routes ?? {},
        unreadCount: bootstrap.unreadCount ?? 0,
        open: false,
        loading: false,
        items: [],

        init() {
            window.addEventListener('erp:notifications-updated', (event) => {
                if (event.detail?.unreadCount !== undefined) {
                    this.unreadCount = event.detail.unreadCount;
                }
            });
        },

        async toggle() {
            this.open = ! this.open;

            if (this.open) {
                await this.fetchPanel();
            }
        },

        close() {
            this.open = false;
        },

        async fetchPanel() {
            this.loading = true;

            try {
                const response = await fetch(this.routes.panel, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (response.ok) {
                    const data = await response.json();
                    this.items = data.notifications ?? [];
                    this.unreadCount = data.unread_count ?? 0;
                }
            } finally {
                this.loading = false;
            }
        },

        async markRead(item) {
            const response = await fetch(this.routes.markRead.replace('__ID__', String(item.id)), {
                method: 'POST',
                headers: this.jsonHeaders(),
            });

            if (response.ok) {
                const data = await response.json();
                this.unreadCount = data.unread_count ?? this.unreadCount;
                item.is_unread = false;
                item.status = 'read';
                this.broadcastUnread();
            }
        },

        async markAllRead() {
            const response = await fetch(this.routes.markAllRead, {
                method: 'POST',
                headers: this.jsonHeaders(),
            });

            if (response.ok) {
                this.unreadCount = 0;
                this.items = this.items.map((item) => ({ ...item, is_unread: false, status: 'read' }));
                this.broadcastUnread();
            }
        },

        async openNotification(item) {
            const response = await fetch(this.routes.open.replace('__ID__', String(item.id)), {
                method: 'POST',
                headers: this.jsonHeaders(),
            });

            if (! response.ok) {
                return;
            }

            const data = await response.json();
            this.unreadCount = data.unread_count ?? this.unreadCount;
            item.is_unread = false;
            this.broadcastUnread();
            this.close();

            if (data.redirect_url) {
                window.Turbo?.visit(data.redirect_url) ?? (window.location.href = data.redirect_url);
            }
        },

        broadcastUnread() {
            window.dispatchEvent(new CustomEvent('erp:notifications-updated', {
                detail: { unreadCount: this.unreadCount },
            }));
        },

        jsonHeaders() {
            return {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                'X-Requested-With': 'XMLHttpRequest',
            };
        },

        formatDate(iso) {
            if (! iso) {
                return '—';
            }

            try {
                return new Date(iso).toLocaleString();
            } catch {
                return iso;
            }
        },
    }));

    Alpine.data('notificationCenterWorkspace', (bootstrap = {}) => ({
        routes: bootstrap.routes ?? {},
        can: bootstrap.can ?? {},
        types: bootstrap.types ?? [],
        preferences: bootstrap.preferences ?? {
            commercial_alerts: true,
            production_alerts: true,
            accounting_alerts: true,
            hr_alerts: true,
            system_alerts: true,
        },
        prefsSaving: false,
        selectedIds: [],
        testForm: {
            recipient_user_id: bootstrap.recipientId,
            type: 'quotation_approved',
            title: 'Test notification',
            body: 'This is an internal ERP test alert.',
        },

        async markRead(id) {
            await fetch(this.routes.mark_read.replace('__ID__', String(id)), {
                method: 'POST',
                headers: this.jsonHeaders(),
            });
            window.location.reload();
        },

        async dismiss(id) {
            await fetch(this.routes.dismiss.replace('__ID__', String(id)), {
                method: 'POST',
                headers: this.jsonHeaders(),
            });
            window.location.reload();
        },

        async archive(id) {
            await fetch(this.routes.archive.replace('__ID__', String(id)), {
                method: 'POST',
                headers: this.jsonHeaders(),
            });
            window.location.reload();
        },

        toggleAll(event) {
            const checked = event.target.checked;
            this.selectedIds = checked
                ? [...document.querySelectorAll('tbody input[type=checkbox][value]')].map((el) => Number(el.value))
                : [];
        },

        async bulkRead() {
            if (this.selectedIds.length === 0) {
                return;
            }

            await fetch(this.routes.bulk_read, {
                method: 'POST',
                headers: this.jsonHeaders(),
                body: JSON.stringify({ ids: this.selectedIds }),
            });
            window.location.reload();
        },

        async bulkDismiss() {
            if (this.selectedIds.length === 0) {
                return;
            }

            await fetch(this.routes.bulk_dismiss, {
                method: 'POST',
                headers: this.jsonHeaders(),
                body: JSON.stringify({ ids: this.selectedIds }),
            });
            window.location.reload();
        },

        async savePreferences() {
            this.prefsSaving = true;

            try {
                await fetch(this.routes.preferences, {
                    method: 'PUT',
                    headers: this.jsonHeaders(),
                    body: JSON.stringify(this.preferences),
                });
            } finally {
                this.prefsSaving = false;
            }
        },

        async sendTest() {
            await fetch(this.routes.store, {
                method: 'POST',
                headers: this.jsonHeaders(),
                body: JSON.stringify(this.testForm),
            });
            window.location.reload();
        },

        jsonHeaders() {
            return {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                'X-Requested-With': 'XMLHttpRequest',
            };
        },
    }));

    Alpine.data('communicationTemplatesWorkspace', (bootstrap = {}) => ({
        routes: bootstrap.routes ?? {},
        can: bootstrap.can ?? {},
        options: bootstrap.options ?? {},
        variables: bootstrap.variables ?? [],
        sampleData: bootstrap.sampleData ?? {},
        templates: bootstrap.templates ?? [],
        viewMode: bootstrap.activeFilters?.view === 'category' ? 'category' : 'list',
        selectedId: null,
        selected: null,
        previewData: { ...bootstrap.sampleData },
        previewResult: null,
        previewLoading: false,
        editorOpen: false,
        editorMode: 'create',
        editorSaving: false,
        editorError: null,
        form: {},
        versionsOpen: false,
        versionsLoading: false,
        versions: [],
        compareLeft: null,
        compareRight: null,
        compareResult: null,

        init() {
            const first = this.templates[0];

            if (first) {
                this.selectTemplate(first.id);
            }
        },

        selectTemplate(id) {
            this.selectedId = id;
            this.selected = this.templates.find((t) => t.id === id) ?? null;
            this.previewResult = null;
            this.previewData = { ...this.sampleData };
        },

        async runPreview() {
            if (! this.selectedId) {
                return;
            }

            this.previewLoading = true;

            try {
                const response = await fetch(this.routes.preview.replace('__ID__', String(this.selectedId)), {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ data: this.previewData }),
                });

                if (response.ok) {
                    this.previewResult = await response.json();
                }
            } finally {
                this.previewLoading = false;
            }
        },

        openEditor(template = null) {
            this.editorMode = template ? 'edit' : 'create';
            this.editorError = null;
            this.form = template
                ? {
                    name: template.name,
                    channel: template.channel,
                    template_type: template.template_type,
                    category: template.category,
                    status: template.status,
                    subject: template.subject ?? '',
                    body: template.body,
                    description: template.description ?? '',
                    change_notes: '',
                }
                : {
                    code: '',
                    name: '',
                    channel: 'sms',
                    template_type: 'transactional',
                    category: 'quotation_ready',
                    status: 'active',
                    subject: '',
                    body: 'Dear {{customer_name}}, your quotation {{quotation_number}} is ready.',
                    description: '',
                    change_notes: '',
                };
            this.editorOpen = true;
        },

        closeEditor() {
            this.editorOpen = false;
        },

        async saveTemplate() {
            this.editorSaving = true;
            this.editorError = null;

            const isCreate = this.editorMode === 'create';
            const url = isCreate
                ? this.routes.store
                : this.routes.update.replace('__ID__', String(this.selectedId));
            const method = isCreate ? 'POST' : 'PUT';

            try {
                const response = await fetch(url, {
                    method,
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(this.form),
                });

                const data = await response.json().catch(() => ({}));

                if (! response.ok) {
                    const message = data.message
                        ?? Object.values(data.errors ?? {}).flat().join(' ')
                        ?? 'Unable to save template.';
                    this.editorError = message;

                    return;
                }

                if (isCreate) {
                    this.templates.push(data.template);
                    this.selectTemplate(data.template.id);
                } else {
                    const index = this.templates.findIndex((t) => t.id === data.template.id);

                    if (index >= 0) {
                        this.templates[index] = data.template;
                    }

                    this.selectTemplate(data.template.id);
                }

                this.closeEditor();
            } finally {
                this.editorSaving = false;
            }
        },

        async openVersions() {
            if (! this.selectedId || ! this.can.versionView) {
                return;
            }

            this.versionsOpen = true;
            this.versionsLoading = true;
            this.compareLeft = null;
            this.compareRight = null;
            this.compareResult = null;

            try {
                const response = await fetch(this.routes.versions.replace('__ID__', String(this.selectedId)), {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (response.ok) {
                    const data = await response.json();
                    this.versions = data.versions ?? [];
                }
            } finally {
                this.versionsLoading = false;
            }
        },

        comparePick(version, side) {
            if (side === 'left') {
                this.compareLeft = version;
            } else {
                this.compareRight = version;
            }

            if (this.compareLeft && this.compareRight) {
                this.runCompare();
            }
        },

        async runCompare() {
            const params = new URLSearchParams({
                left_id: String(this.compareLeft.id),
                right_id: String(this.compareRight.id),
            });

            const response = await fetch(
                `${this.routes.compare.replace('__ID__', String(this.selectedId))}?${params}`,
                { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } },
            );

            if (response.ok) {
                this.compareResult = await response.json();
            }
        },

        async restoreVersion(version) {
            if (! this.can.restore || ! confirm('Restore this version? A new version will be created.')) {
                return;
            }

            const response = await fetch(this.routes.restore.replace('__ID__', String(this.selectedId)), {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ version_id: version.id }),
            });

            if (response.ok) {
                const data = await response.json();
                const index = this.templates.findIndex((t) => t.id === data.template.id);

                if (index >= 0) {
                    this.templates[index] = data.template;
                }

                this.selectTemplate(data.template.id);
                this.openVersions();
            }
        },

        closePanels() {
            this.closeEditor();
            this.versionsOpen = false;
        },

        formatDate(iso) {
            if (! iso) {
                return '—';
            }

            try {
                return new Date(iso).toLocaleString();
            } catch {
                return iso;
            }
        },
    }));

    Alpine.data('postingRulesWorkspace', (bootstrap = {}) => ({
        routes: bootstrap.routes ?? {},
        canAudit: bootstrap.canAudit ?? false,
        drawerOpen: false,
        drawerLoading: false,
        rule: null,

        showUrl(ruleId) {
            return (this.routes.show ?? '').replace('__ID__', String(ruleId));
        },

        async openDrawer(ruleId) {
            this.drawerOpen = true;
            this.drawerLoading = true;
            this.rule = null;

            try {
                const response = await fetch(this.showUrl(ruleId), {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (response.ok) {
                    const data = await response.json();
                    this.rule = data.rule ?? null;
                }
            } finally {
                this.drawerLoading = false;
            }
        },

        closeDrawer() {
            this.drawerOpen = false;
            this.rule = null;
        },

        formatDate(iso) {
            if (! iso) {
                return '—';
            }

            try {
                return new Date(iso).toLocaleString();
            } catch {
                return iso;
            }
        },

        validationBadgeClass(status) {
            if (status === 'valid') {
                return 'bg-emerald-100 text-emerald-800';
            }

            if (status === 'warning') {
                return 'bg-amber-100 text-amber-900';
            }

            if (status === 'broken') {
                return 'bg-red-100 text-red-800';
            }

            return 'bg-slate-100 text-slate-700';
        },
    }));

    Alpine.data('accessAuditWorkspace', (bootstrap = {}) => ({
        showRoute: bootstrap.showRoute ?? '',
        exportRoute: bootstrap.exportRoute ?? '',
        activeFilters: bootstrap.activeFilters ?? {},
        drawerOpen: false,
        loading: false,
        detail: null,

        async openDrawer(id) {
            this.drawerOpen = true;
            this.loading = true;
            this.detail = null;

            try {
                const response = await fetch(this.showRoute.replace('__ID__', String(id)), {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (response.ok) {
                    this.detail = await response.json();
                }
            } finally {
                this.loading = false;
            }
        },

        closeDrawer() {
            this.drawerOpen = false;
            this.detail = null;
        },

        exportUrl(format) {
            const params = new URLSearchParams({ ...this.activeFilters, format });
            return `${this.exportRoute}?${params.toString()}`;
        },
    }));

    Alpine.data('backgroundJobsWorkspace', (bootstrap = {}) => ({
        showRoute: bootstrap.showRoute ?? '',
        drawerOpen: false,
        loading: false,
        detail: null,

        async openDrawer(reference) {
            this.drawerOpen = true;
            this.loading = true;
            this.detail = null;

            try {
                const response = await fetch(this.showRoute.replace('__REF__', encodeURIComponent(String(reference))), {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (response.ok) {
                    this.detail = await response.json();
                }
            } finally {
                this.loading = false;
            }
        },

        closeDrawer() {
            this.drawerOpen = false;
            this.detail = null;
        },
    }));

    Alpine.data('auditLogsWorkspace', (bootstrap = {}) => ({
        showRoute: bootstrap.showRoute ?? '',
        exportRoute: bootstrap.exportRoute ?? '',
        activeFilters: bootstrap.activeFilters ?? {},
        drawerOpen: false,
        loading: false,
        detail: null,

        async openDrawer(id) {
            this.drawerOpen = true;
            this.loading = true;
            this.detail = null;

            try {
                const response = await fetch(this.showRoute.replace('__ID__', String(id)), {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (response.ok) {
                    this.detail = await response.json();
                }
            } finally {
                this.loading = false;
            }
        },

        closeDrawer() {
            this.drawerOpen = false;
            this.detail = null;
        },

        exportUrl(format) {
            const params = new URLSearchParams({ ...this.activeFilters, format });
            return `${this.exportRoute}?${params.toString()}`;
        },
    }));

    Alpine.data('backupManagementWorkspace', (bootstrap = {}) => ({
        readinessRoute: bootstrap.readinessRoute ?? '',
        drawerOpen: false,
        loading: false,
        report: null,

        async openReadiness(id) {
            this.drawerOpen = true;
            this.loading = true;
            this.report = null;

            try {
                const response = await fetch(this.readinessRoute.replace('__ID__', String(id)), {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (response.ok) {
                    this.report = await response.json();
                }
            } finally {
                this.loading = false;
            }
        },

        closeDrawer() {
            this.drawerOpen = false;
            this.report = null;
        },
    }));
});

Alpine.start();

const navActiveClasses = ['erp-nav-link--active', 'text-white', 'border-l-3', 'border-erp-accent', 'bg-erp-primary'];
const navInactiveClasses = ['text-white/80', 'text-slate-400', 'hover:text-white'];

function navRouteIsActive(linkRoute, currentRoute) {
    if (! linkRoute || ! currentRoute) {
        return false;
    }

    if (linkRoute.includes('*')) {
        const pattern = linkRoute.replace(/\./g, '\\.').replace(/\*/g, '.*');
        const regex = new RegExp(`^${pattern}$`);

        return regex.test(currentRoute);
    }

    if (linkRoute.endsWith('.index')) {
        const prefix = linkRoute.replace(/\.index$/, '');

        return currentRoute === linkRoute || currentRoute.startsWith(`${prefix}.`);
    }

    return currentRoute === linkRoute;
}

function navTokenIsActive(token, currentRoute) {
    if (! token || ! currentRoute) {
        return false;
    }

    if (token.includes('*')) {
        return navRouteIsActive(token, currentRoute);
    }

    if (token.includes('.')) {
        return navRouteIsActive(token, currentRoute);
    }

    return currentRoute.startsWith(`${token}.`) || currentRoute === token;
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function parseQuickCreatePayload(meta) {
    if (! meta?.dataset?.quickCreate) {
        return [];
    }

    try {
        const parsed = JSON.parse(meta.dataset.quickCreate);

        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
}

function quickCreateMarkup(items, labels = {}) {
    if (! Array.isArray(items) || items.length === 0) {
        return '';
    }

    const createLabel = escapeHtml(labels.create ?? 'Create');
    const soonLabel = escapeHtml(labels.soon ?? 'Soon');

    const links = items.map((item) => {
        const label = escapeHtml(item.label);

        if (item.coming_soon) {
            return `<span class="block w-full px-4 py-2 text-start text-sm leading-5 text-slate-400 cursor-not-allowed">${label} <span class="text-xs">(${soonLabel})</span></span>`;
        }

        if (! item.href) {
            return '';
        }

        const href = escapeHtml(item.href);
        const modalAttr = item.modal ? ' data-erp-modal-open' : '';
        const turboAttr = item.modal ? '' : ' data-turbo-frame="erp-main"';

        return `<a href="${href}"${modalAttr}${turboAttr} class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">${label}</a>`;
    }).join('');

    return `
        <div
            class="relative z-20 inline-block text-left"
            x-data="erpRowActionsMenu('right')"
            @click.outside="closeFromOutside()"
            @keydown.escape.window="close()"
            @scroll.window="close()"
            @resize.window="close()"
        >
            <div x-ref="trigger" @click.stop="toggle($event)">
                <button type="button" class="erp-btn-primary py-2">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span class="hidden sm:inline">${createLabel}</span>
                </button>
            </div>
            <div
                x-ref="menu"
                :style="open ? menuStyle : null"
                :class="open ? 'erp-row-actions-menu--open' : ''"
                class="erp-row-actions-menu w-48 rounded-xl shadow-card-hover"
            >
                <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white border border-erp-border">
                    ${links}
                </div>
            </div>
        </div>
    `;
}

function syncQuickCreateFromFrame(meta) {
    const root = document.getElementById('erp-quick-create');

    if (! root) {
        return;
    }

    const items = parseQuickCreatePayload(meta);
    const markup = quickCreateMarkup(items, {
        create: meta.dataset.i18nCreate,
        soon: meta.dataset.i18nSoon,
    });

    if (window.__erpOpenRowMenu?.close) {
        window.__erpOpenRowMenu.close();
    }

    root.innerHTML = markup;

    if (markup) {
        Alpine.initTree(root);
    }
}

function shellScrollContainer() {
    const frame = document.getElementById('erp-main');

    if (frame?.classList.contains('overflow-y-auto')) {
        return frame;
    }

    return null;
}

function shellScrollY() {
    const container = shellScrollContainer();

    return container ? container.scrollTop : window.scrollY;
}

function setShellScrollY(y) {
    const container = shellScrollContainer();

    if (container) {
        container.scrollTop = y;

        return;
    }

    window.scrollTo(0, y);
}

function applyShellLayout(compact) {
    const shell = document.getElementById('erp-app-shell');
    const frame = document.getElementById('erp-main');

    document.body.classList.add('overflow-hidden');

    if (shell) {
        shell.classList.add('h-screen', 'max-h-screen', 'overflow-hidden');
        shell.classList.remove('min-h-screen');
    }

    if (frame) {
        frame.classList.toggle('overflow-hidden', compact);
        frame.classList.toggle('overflow-x-hidden', ! compact);
        frame.classList.toggle('overflow-y-auto', ! compact);
    }
}

function syncShellFromFrame() {
    const meta = document.getElementById('erp-route-meta');

    if (! meta) {
        return;
    }

    const compact = meta.dataset.compactPage === '1';

    applyShellLayout(compact);

    const currentRoute = meta.dataset.route ?? '';
    const pageTitle = meta.dataset.title ?? '';
    const appName = meta.dataset.appName ?? 'Janaprints';

    const titleEl = document.getElementById('erp-page-title');

    if (titleEl && pageTitle) {
        titleEl.textContent = pageTitle;
    }

    if (pageTitle) {
        document.title = `${pageTitle} — ${appName}`;
    }

    document.querySelectorAll('#erp-sidebar [data-nav-route]').forEach((link) => {
        const activePatterns = (link.dataset.navActiveRoutes ?? '').split(',').filter(Boolean);
        const active = activePatterns.some((pattern) => navTokenIsActive(pattern, currentRoute))
            || navRouteIsActive(link.dataset.navRoute, currentRoute);
        const isChild = link.classList.contains('pl-9') || link.dataset.navDepth === 'child';

        link.classList.remove(...navActiveClasses, ...navInactiveClasses, 'hover:bg-white/5', 'bg-erp-accent', 'bg-erp-accent/90', 'bg-erp-primary', 'border-l-3', 'border-erp-accent');

        if (active) {
            link.classList.add('erp-nav-link--active', 'border-l-3', 'border-erp-accent', 'bg-erp-primary', 'text-white');
        } else {
            link.classList.add(...(isChild ? navInactiveClasses : ['text-white/80', 'hover:text-white']));
            link.style.backgroundColor = '';
        }
    });

    document.querySelectorAll('#erp-sidebar [data-nav-group], #erp-sidebar [data-nav-subgroup]').forEach((group) => {
        const routes = (group.dataset.navGroupRoutes ?? '').split(',').filter(Boolean);
        const open = routes.some((route) => navTokenIsActive(route, currentRoute));
        group.dataset.navGroupOpen = open ? '1' : '0';

        const alpine = group.__x?.$data;

        if (alpine && typeof alpine.open !== 'undefined') {
            alpine.open = open;
        }
    });

    syncQuickCreateFromFrame(meta);

    document.dispatchEvent(new CustomEvent('erp:page-loaded'));
}

function cleanupRowActionMenus(root = document) {
    root.querySelectorAll('[data-erp-row-actions-menu]').forEach((menu) => {
        menu.classList.remove('erp-row-actions-menu--open');
        menu.removeAttribute('hidden');
        menu.style.removeProperty('top');
        menu.style.removeProperty('left');
        menu.style.removeProperty('transform');
        menu.style.removeProperty('position');
        menu.style.removeProperty('z-index');

        const host = menu.__erpMenuHost ?? menu.closest('[data-erp-row-actions]');

        if (host && menu.parentElement === document.body) {
            host.appendChild(menu);
        }
    });

    window.__erpOpenRowMenu = null;
}

function promoteFlashAlertsToToast(root) {
    if (! root) {
        return;
    }

    root.querySelectorAll('[data-erp-flash-status]').forEach((alert) => {
        const message = alert.textContent?.trim();

        if (message) {
            erpModalManager.showToast(message);
        }

        alert.remove();
    });

    root.querySelectorAll('[data-erp-flash-error]').forEach((alert) => {
        const message = alert.textContent?.trim();

        if (message) {
            erpModalManager.showToast(message, 'error');
        }

        alert.remove();
    });
}

const FORM_SETTINGS_SCROLL_KEY = 'erp.formSettings.scrollTop';

function saveFormSettingsScrollPosition() {
    const scrollContainer = shellScrollContainer();

    if (scrollContainer) {
        sessionStorage.setItem(FORM_SETTINGS_SCROLL_KEY, String(scrollContainer.scrollTop));
    }
}

function restoreFormSettingsScrollPosition() {
    const savedScroll = sessionStorage.getItem(FORM_SETTINGS_SCROLL_KEY);

    if (savedScroll === null) {
        return false;
    }

    const scrollContainer = shellScrollContainer();

    if (scrollContainer) {
        scrollContainer.scrollTop = Number.parseInt(savedScroll, 10) || 0;
    }

    sessionStorage.removeItem(FORM_SETTINGS_SCROLL_KEY);

    return true;
}

function setFormSettingsSaveState(form, isSaving) {
    const button = form?.querySelector('[data-erp-form-settings-save]');

    if (! button) {
        return;
    }

    if (isSaving) {
        if (! button.dataset.defaultLabel) {
            button.dataset.defaultLabel = button.textContent?.trim() ?? '';
        }

        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        button.textContent = button.dataset.savingLabel || 'Saving…';
        button.classList.add('opacity-70', 'pointer-events-none');
    } else {
        button.disabled = false;
        button.removeAttribute('aria-busy');

        if (button.dataset.defaultLabel) {
            button.textContent = button.dataset.defaultLabel;
        }

        button.classList.remove('opacity-70', 'pointer-events-none');
    }
}

function resolveFormSettingsFrameContext(form) {
    const workspaceFrame = document.getElementById('module-workspace-content');
    const inWorkspaceFrame = Boolean(form?.closest('#module-workspace-content') && workspaceFrame);

    return {
        frameId: inWorkspaceFrame ? 'module-workspace-content' : 'erp-main',
        targetFrame: inWorkspaceFrame
            ? workspaceFrame
            : document.getElementById('erp-main'),
        embedded: inWorkspaceFrame,
    };
}

function extractFormSettingsFrameContent(doc, frameId) {
    if (! doc) {
        return null;
    }

    return doc.querySelector(`turbo-frame#${frameId}`)
        ?? doc.getElementById(frameId);
}

function extractFormSettingsFlashMessage(root) {
    if (! root) {
        return null;
    }

    const status = root.querySelector('[data-erp-flash-status]');
    const error = root.querySelector('[data-erp-flash-error]');

    if (status) {
        return { message: status.textContent.trim(), variant: 'success' };
    }

    if (error) {
        return { message: error.textContent.trim(), variant: 'error' };
    }

    return null;
}

function showFormSettingsSweetAlert(message, variant = 'success') {
    if (! message) {
        return;
    }

    Swal.fire({
        icon: variant === 'error' ? 'error' : 'success',
        title: variant === 'error' ? 'Unable to save' : 'Saved',
        text: message,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
    });
}

function bindFormSettingsForms(root = document) {
    root.querySelectorAll('form[data-erp-form-settings]').forEach((form) => {
        if (form.dataset.erpFormSettingsBound === '1') {
            return;
        }

        form.dataset.erpFormSettingsBound = '1';

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();

            if (typeof window.__erpSubmitFormSettings === 'function') {
                window.__erpSubmitFormSettings(form);
            }
        }, true);
    });
}

function applyFormSettingsFrameHtml(html, responseUrl = null, turboLocation = null, form = null) {
    const context = form ? resolveFormSettingsFrameContext(form) : {
        frameId: 'erp-main',
        targetFrame: document.getElementById('erp-main'),
        embedded: false,
    };
    const doc = new DOMParser().parseFromString(html, 'text/html');
    let sourceFrame = extractFormSettingsFrameContent(doc, context.frameId);
    const targetFrame = context.targetFrame;

    if (! sourceFrame) {
        const alternateFrameId = context.frameId === 'erp-main'
            ? 'module-workspace-content'
            : 'erp-main';
        sourceFrame = extractFormSettingsFrameContent(doc, alternateFrameId);
    }

    if (! sourceFrame || ! targetFrame) {
        return false;
    }

    targetFrame.innerHTML = sourceFrame.innerHTML;

    const nextUrl = turboLocation || responseUrl;

    if (nextUrl && ! context.embedded) {
        try {
            window.history.pushState({}, '', new URL(nextUrl, window.location.origin).toString());
        } catch {
            // Keep the current URL when the response location cannot be parsed.
        }
    }

    if (context.frameId === 'module-workspace-content') {
        refreshEmbeddedWorkspaceFrame(targetFrame);
    } else {
        refreshFrameAlpine(targetFrame);
        restoreFormSettingsScrollPosition();
    }

    const flash = extractFormSettingsFlashMessage(targetFrame);

    promoteFlashAlertsToToast(targetFrame);
    bindFormSettingsForms(targetFrame);

    if (flash) {
        showFormSettingsSweetAlert(flash.message, flash.variant);
    } else if (form?.dataset?.erpFormLabel) {
        showFormSettingsSweetAlert(`${form.dataset.erpFormLabel} settings saved successfully.`, 'success');
    }

    return true;
}

window.__erpSubmitFormSettings = async function submitFormSettingsRequest(form) {
    if (! form) {
        return;
    }

    const context = resolveFormSettingsFrameContext(form);
    const formData = new FormData(form);
    formData.set('_turbo_frame', '1');

    if (context.embedded) {
        formData.set('_embedded_workspace', '1');
    }

    const method = (formData.get('_method') || form.method || 'POST').toString().toUpperCase();

    saveFormSettingsScrollPosition();
    setFormSettingsSaveState(form, true);

    try {
        const response = await fetch(form.action, {
            method: method === 'GET' ? 'GET' : 'POST',
            body: method === 'GET' ? null : formData,
            headers: {
                'Turbo-Frame': context.frameId,
                'Accept': 'text/html, application/xhtml+xml',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            redirect: 'follow',
        });

        const html = await response.text();
        const turboLocation = response.headers.get('Turbo-Location');

        if (applyFormSettingsFrameHtml(html, response.url, turboLocation, form)) {
            return;
        }

        if (! response.ok) {
            showFormSettingsSweetAlert(`Unable to save form settings (${response.status}). Please try again.`, 'error');
            sessionStorage.removeItem(FORM_SETTINGS_SCROLL_KEY);

            return;
        }

        showFormSettingsSweetAlert('Unable to save form settings. Please try again.', 'error');
        sessionStorage.removeItem(FORM_SETTINGS_SCROLL_KEY);
    } catch (error) {
        console.error('submitFormSettingsRequest', error);
        showFormSettingsSweetAlert('Unable to save form settings. Please try again.', 'error');
        sessionStorage.removeItem(FORM_SETTINGS_SCROLL_KEY);
    } finally {
        setFormSettingsSaveState(form, false);
    }
};

function refreshFrameAlpine(frame) {
    if (! frame) {
        return;
    }

    cleanupRowActionMenus(frame);
    Alpine.destroyTree(frame);
    Alpine.initTree(frame);
    promoteFlashAlertsToToast(frame);
    syncShellFromFrame();
    bindFormSettingsForms(frame);
}

function workspaceTabSlug(value) {
    return String(value ?? '')
        .trim()
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

function syncSecondaryWorkspaceTabActiveState(clickedTab = null) {
    const tabs = clickedTab
        ? [clickedTab]
        : document.querySelectorAll('.module-workspace-switcher--secondary [data-workspace-tab][href]');

    if (clickedTab) {
        const track = clickedTab.closest('[role="tablist"]');

        if (! track) {
            return;
        }

        track.querySelectorAll('[data-workspace-tab]').forEach((tab) => {
            tab.classList.remove('workspace-pill--active');
            tab.removeAttribute('aria-selected');
        });

        clickedTab.classList.add('workspace-pill--active');
        clickedTab.setAttribute('aria-selected', 'true');

        return;
    }

    const currentUrl = new URL(window.location.href);
    const currentTab = workspaceTabSlug(currentUrl.searchParams.get('tab'));
    const workspaceFrame = document.getElementById('module-workspace-content');
    const frameSrc = workspaceFrame?.src || workspaceFrame?.getAttribute('src') || '';

    document.querySelectorAll('.module-workspace-switcher--secondary [role="tablist"]').forEach((track) => {
        let matched = false;

        track.querySelectorAll('[data-workspace-tab][href]').forEach((tab) => {
            let isActive = false;

            try {
                const tabKey = workspaceTabSlug(tab.dataset.workspaceTabKey ?? '');

                if (currentTab && tabKey) {
                    isActive = tabKey === currentTab;
                } else if (frameSrc) {
                    const tabUrl = new URL(tab.getAttribute('href'), window.location.origin);
                    const srcUrl = new URL(frameSrc, window.location.origin);
                    isActive = tabUrl.pathname === srcUrl.pathname;
                } else {
                    const tabUrl = new URL(tab.getAttribute('href'), window.location.origin);
                    isActive = tabUrl.pathname === currentUrl.pathname
                        && tabUrl.searchParams.get('embedded') === currentUrl.searchParams.get('embedded');
                }

                tab.classList.toggle('workspace-pill--active', isActive);
                tab.toggleAttribute('aria-selected', isActive);

                if (isActive) {
                    matched = true;
                }
            } catch {
                // Ignore malformed tab hrefs.
            }
        });

        if (! matched && ! currentTab && ! frameSrc) {
            const first = track.querySelector('[data-workspace-tab][href]');

            if (first) {
                first.classList.add('workspace-pill--active');
                first.setAttribute('aria-selected', 'true');
            }
        }
    });
}

function wireEmbeddedWorkspaceLinks(root) {
    if (! root) {
        return;
    }

    root.querySelectorAll('a[href]').forEach((link) => {
        if (link.getAttribute('data-turbo') === 'false' || link.getAttribute('target') === '_blank') {
            return;
        }

        if (link.closest('#erp-form-modal') || link.hasAttribute('data-erp-modal-open') || link.hasAttribute('data-no-modal')) {
            return;
        }

        const turboFrame = link.getAttribute('data-turbo-frame');
        const targetsWorkspaceContent = turboFrame === 'module-workspace-content';

        if (! link.hasAttribute('data-turbo-frame')) {
            link.setAttribute('data-turbo-frame', 'module-workspace-content');
        } else if (turboFrame === 'erp-main') {
            try {
                const inboxUrl = new URL(link.href, window.location.origin);

                if (! inboxUrl.pathname.includes('/admin/communications/inbox')) {
                    return;
                }

                link.setAttribute('data-turbo-frame', 'module-workspace-content');
            } catch {
                return;
            }
        } else if (! targetsWorkspaceContent) {
            return;
        }

        try {
            const url = new URL(link.href, window.location.origin);

            if (! url.searchParams.has('embedded')) {
                url.searchParams.set('embedded', '1');
                link.href = `${url.pathname}${url.search}${url.hash}`;
            }
        } catch {
            // Keep the original href when it cannot be parsed.
        }
    });

    root.querySelectorAll('form[action]').forEach((form) => {
        if (form.getAttribute('data-turbo') === 'false') {
            return;
        }

        const turboFrame = form.getAttribute('data-turbo-frame');

        if (turboFrame === 'erp-main') {
            try {
                const inboxAction = new URL(form.getAttribute('action'), window.location.origin);

                if (! inboxAction.pathname.includes('/admin/communications/inbox')) {
                    return;
                }

                form.setAttribute('data-turbo-frame', 'module-workspace-content');
            } catch {
                return;
            }
        } else if (turboFrame && turboFrame !== 'module-workspace-content') {
            return;
        } else if (! turboFrame) {
            form.setAttribute('data-turbo-frame', 'module-workspace-content');
        }

        const method = (form.getAttribute('method') ?? 'get').toLowerCase();

        if (method !== 'get') {
            return;
        }

        try {
            const action = new URL(form.getAttribute('action'), window.location.origin);

            if (! action.searchParams.has('embedded')) {
                action.searchParams.set('embedded', '1');
                form.setAttribute('action', `${action.pathname}${action.search}`);
            }
        } catch {
            // Keep the original form action when it cannot be parsed.
        }
    });
}

function promoteEmbeddedWorkspaceNavigation(frame, responseUrl = null) {
    const url = responseUrl || frame?.src || frame?.getAttribute('src');

    if (! url) {
        return false;
    }

    try {
        const target = new URL(url, window.location.origin);
        target.searchParams.delete('embedded');
        Turbo.visit(target.toString(), { frame: 'erp-main', action: 'advance' });

        return true;
    } catch {
        return false;
    }
}

function refreshEmbeddedWorkspaceFrame(frame) {
    if (! frame) {
        return;
    }

    cleanupRowActionMenus(frame);
    Alpine.destroyTree(frame);
    Alpine.initTree(frame);
    wireEmbeddedWorkspaceLinks(frame);
    bindFormSettingsForms(frame);
}

document.addEventListener('turbo:before-cache', () => {
    if (window.__erpOpenRowMenu) {
        window.__erpOpenRowMenu.close();
    }

    const frame = document.getElementById('erp-main');

    if (frame) {
        cleanupRowActionMenus(frame);
        Alpine.destroyTree(frame);
    }
});

document.addEventListener('change', (event) => {
    const requirementSelect = event.target.closest?.('.form-field-requirement');

    if (! requirementSelect || requirementSelect.dataset.registryRequired === '1') {
        return;
    }

    const row = requirementSelect.closest('tr');
    const visibilitySelect = row?.querySelector('.form-field-visibility');
    const visibilityHint = row?.querySelector('.form-field-visibility-hint');

    if (! visibilitySelect || visibilitySelect.dataset.registryRequired === '1') {
        return;
    }

    if (requirementSelect.value === 'required') {
        visibilitySelect.value = 'visible';
        visibilitySelect.disabled = true;
        visibilityHint?.classList.remove('hidden');
    } else {
        visibilitySelect.disabled = false;
        visibilityHint?.classList.add('hidden');
    }
});

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (! form?.hasAttribute?.('data-erp-form-settings')) {
        return;
    }

    event.preventDefault();
    event.stopPropagation();

    if (typeof window.__erpSubmitFormSettings === 'function') {
        window.__erpSubmitFormSettings(form);
    }
}, true);

document.addEventListener('turbo:frame-load', (event) => {
    if (event.target.id === 'erp-main') {
        const scrollContainer = shellScrollContainer();

        if (scrollContainer && ! restoreFormSettingsScrollPosition()) {
            scrollContainer.scrollTop = 0;
        }

        refreshFrameAlpine(event.target);
        erpModalManager.restoreWorkspaceState();
        syncSecondaryWorkspaceTabActiveState();
        bindFormSettingsForms(event.target);
    }

    if (event.target.id === 'module-workspace-content') {
        refreshEmbeddedWorkspaceFrame(event.target);
        syncSecondaryWorkspaceTabActiveState();
    }

    if (event.target.id === 'erp-form-modal' || event.target.id === 'erp-preview-drawer') {
        erpModalManager.initFrame(event.target);
    }
});

document.addEventListener('turbo:frame-missing', async (event) => {
    if (event.target.id === 'module-workspace-content') {
        event.preventDefault();
        promoteEmbeddedWorkspaceNavigation(event.target, event.detail?.response?.url);

        return;
    }

    if (event.target.id !== 'erp-form-modal' && event.target.id !== 'erp-preview-drawer') {
        return;
    }

    event.preventDefault();

    const response = event.detail?.response;

    if (! response) {
        return;
    }

    try {
        const html = await response.clone().text();
        const panel = erpModalManager.extractModalPanel(html);

        if (! panel) {
            return;
        }

        event.target.innerHTML = panel.outerHTML;
        erpModalManager.initFrame(event.target);
    } catch {
        // Keep the modal open; Turbo will surface the error elsewhere.
    }
});

document.addEventListener('click', (event) => {
    const saveButton = event.target.closest?.('[data-erp-form-settings-save]');

    if (saveButton) {
        const form = saveButton.closest('form[data-erp-form-settings]');

        if (form) {
            event.preventDefault();
            event.stopPropagation();

            if (! saveButton.dataset.defaultLabel) {
                saveButton.dataset.defaultLabel = saveButton.textContent.trim();
            }

            saveButton.disabled = true;
            saveButton.textContent = saveButton.dataset.savingLabel || 'Saving…';

            if (typeof window.__erpSubmitFormSettings === 'function') {
                window.__erpSubmitFormSettings(form).finally(() => {
                    saveButton.disabled = false;
                    saveButton.textContent = saveButton.dataset.defaultLabel || 'Save form settings';
                });
            } else {
                saveButton.disabled = false;
                saveButton.textContent = saveButton.dataset.defaultLabel || 'Save form settings';
                window.alert('Unable to save form settings. Please refresh the page and try again.');
            }
        }

        return;
    }

    const tab = event.target.closest?.('.module-workspace-switcher--secondary [data-workspace-tab][href]');

    if (tab) {
        syncSecondaryWorkspaceTabActiveState(tab);
    }
}, true);

document.addEventListener('turbo:load', () => {
    const frame = document.getElementById('erp-main');

    if (frame) {
        refreshFrameAlpine(frame);
    } else {
        syncShellFromFrame();
    }

    const workspaceFrame = document.getElementById('module-workspace-content');

    if (workspaceFrame) {
        wireEmbeddedWorkspaceLinks(workspaceFrame);
        bindFormSettingsForms(workspaceFrame);
    }

    bindFormSettingsForms(document.getElementById('erp-main'));
    syncSecondaryWorkspaceTabActiveState();
});
