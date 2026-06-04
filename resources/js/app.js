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
    Alpine.data('erpShell', (searchIndex = []) => ({
        sidebarCollapsed: localStorage.getItem('erp.sidebarCollapsed') === '1',
        mobileNavOpen: false,
        query: '',
        searchOpen: false,
        searchIndex: Array.isArray(searchIndex) ? searchIndex : [],
        favorites: JSON.parse(localStorage.getItem('erp.nav.favorites') || '[]'),

        init() {
            this.$watch('sidebarCollapsed', (value) => {
                localStorage.setItem('erp.sidebarCollapsed', value ? '1' : '0');
            });

            this.$watch('favorites', (value) => {
                localStorage.setItem('erp.nav.favorites', JSON.stringify(value));
            });
        },

        get searchHits() {
            const query = this.query.trim().toLowerCase();

            if (! query) {
                return [];
            }

            return this.searchIndex
                .filter((entry) => {
                    const haystack = `${entry.label} ${entry.path}`.toLowerCase();

                    return haystack.includes(query) && ! entry.coming_soon && entry.route;
                })
                .slice(0, 12)
                .map((entry) => ({
                    ...entry,
                    url: this.routeUrl(entry.route),
                }));
        },

        get favoriteItems() {
            return this.favorites
                .map((route) => this.searchIndex.find((entry) => entry.route === route))
                .filter((entry) => entry && ! entry.coming_soon && entry.route)
                .map((entry) => ({
                    ...entry,
                    url: this.routeUrl(entry.route),
                }));
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

            const table = document.getElementById(this.tableId);

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

            table.querySelectorAll('tbody tr').forEach((row) => {
                if (row.offsetParent === null) {
                    return;
                }

                const cells = [...row.querySelectorAll('td')]
                    .filter((td) => !td.classList.contains('erp-table-checkbox-col'))
                    .filter((td) => !td.classList.contains('erp-table-actions-col'))
                    .map((td) => td.textContent.trim().replace(/\s+/g, ' '));

                if (cells.length) {
                    rows.push(cells);
                }
            });

            const csv = rows.map((row) => row.map((cell) => `"${cell.replace(/"/g, '""')}"`).join(',')).join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `${this.exportFilename}.csv`;
            link.click();
            URL.revokeObjectURL(link.href);
        },

        exportSelected() {
            this.exportTable('csv');
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

    Alpine.data('workspaceHub', (cards = []) => ({
        query: '',
        cards: cards.map((card) => ({
            ...card,
            search_text: [card.label, card.description, card.group_label].filter(Boolean).join(' '),
        })),

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

            return this.matches(card.search_text);
        },

        groupVisible(groupLabel) {
            return this.cards.some(
                (card) => card.group_label === groupLabel && this.cardVisible(card.id),
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

function refreshFrameAlpine(frame) {
    if (! frame) {
        return;
    }

    cleanupRowActionMenus(frame);
    Alpine.destroyTree(frame);
    Alpine.initTree(frame);
    syncShellFromFrame();
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

document.addEventListener('turbo:frame-load', (event) => {
    if (event.target.id === 'erp-main') {
        refreshFrameAlpine(event.target);
    }
});

document.addEventListener('turbo:load', () => {
    syncShellFromFrame();
});
