import Alpine from 'alpinejs';
import * as Turbo from '@hotwired/turbo';

window.Alpine = Alpine;
window.Turbo = Turbo;

Turbo.session.drive = true;

const progressBar = () => document.getElementById('turbo-progress');

document.addEventListener('turbo:visit', () => {
    const bar = progressBar();

    if (bar) {
        bar.classList.add('turbo-progress--visible');
        bar.style.width = '30%';
    }
});

document.addEventListener('turbo:frame-render', () => {
    const bar = progressBar();

    if (bar) {
        bar.style.width = '100%';
        window.setTimeout(() => {
            bar.classList.remove('turbo-progress--visible');
            bar.style.width = '0';
        }, 200);
    }
});

document.addEventListener('alpine:init', () => {
    Alpine.data('erpShell', () => ({
        sidebarCollapsed: localStorage.getItem('erp.sidebarCollapsed') === '1',
        mobileNavOpen: false,

        init() {
            this.$watch('sidebarCollapsed', (value) => {
                localStorage.setItem('erp.sidebarCollapsed', value ? '1' : '0');
            });
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

    Alpine.data('navGroup', (groupId, initiallyOpen = false) => ({
        open: initiallyOpen,

        init() {
            const stored = localStorage.getItem(`erp.nav.${groupId}`);
            if (stored !== null) {
                this.open = stored === '1';
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

    Alpine.data('tableSearch', () => ({
        query: '',

        matches(text) {
            if (!this.query.trim()) {
                return true;
            }

            return String(text).toLowerCase().includes(this.query.trim().toLowerCase());
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

    Alpine.data('roleGovernanceDashboard', () => ({
        query: '',
        drawerOpen: false,
        drawerRole: null,
        previewRole: null,

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

        setPreview(role) {
            this.previewRole = role;
        },

        clearPreview() {
            this.previewRole = null;
        },

        openRole(url) {
            if (window.Turbo) {
                window.Turbo.visit(url, { action: 'advance' });
            } else {
                window.location.href = url;
            }
        },
    }));

});

Alpine.start();

const navActiveClasses = ['bg-erp-accent', 'text-white', 'bg-erp-accent/90'];
const navInactiveClasses = ['text-slate-400', 'hover:bg-white/5', 'hover:text-white', 'text-slate-300'];

function navRouteIsActive(linkRoute, currentRoute) {
    if (! linkRoute || ! currentRoute) {
        return false;
    }

    if (linkRoute.endsWith('.index')) {
        const prefix = linkRoute.replace(/\.index$/, '');

        return currentRoute === linkRoute || currentRoute.startsWith(`${prefix}.`);
    }

    return currentRoute === linkRoute;
}

function syncShellFromFrame() {
    const meta = document.getElementById('erp-route-meta');

    if (! meta) {
        return;
    }

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
        const active = navRouteIsActive(link.dataset.navRoute, currentRoute);
        const isChild = link.classList.contains('pl-9') || link.dataset.navDepth === 'child';

        link.classList.remove(...navActiveClasses, ...navInactiveClasses);

        if (active) {
            link.classList.add(...(isChild ? ['bg-erp-accent/90', 'text-white'] : ['bg-erp-accent', 'text-white']));
        } else {
            link.classList.add(...(isChild ? navInactiveClasses : ['text-slate-300', 'hover:bg-white/5', 'hover:text-white']));
        }
    });

    document.querySelectorAll('#erp-sidebar [data-nav-group]').forEach((group) => {
        const routes = (group.dataset.navGroupRoutes ?? '').split(',').filter(Boolean);
        const open = routes.some((route) => navRouteIsActive(route, currentRoute));
        group.dataset.navGroupOpen = open ? '1' : '0';
    });

    document.dispatchEvent(new CustomEvent('erp:page-loaded'));
}

function refreshFrameAlpine(frame) {
    if (! frame) {
        return;
    }

    Alpine.destroyTree(frame);
    Alpine.initTree(frame);
    syncShellFromFrame();
}

document.addEventListener('turbo:before-cache', () => {
    const frame = document.getElementById('erp-main');

    if (frame) {
        Alpine.destroyTree(frame);
    }
});

document.addEventListener('turbo:frame-load', (event) => {
    if (event.target.id === 'erp-main') {
        refreshFrameAlpine(event.target);
    }
});

document.addEventListener('turbo:load', () => {
    syncShellFromFrame();
});
