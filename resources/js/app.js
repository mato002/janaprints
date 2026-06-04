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

            if (rows.length === 0) {
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

            if (row.querySelector('[data-export-skip]')) {
                return false;
            }

            return true;
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
    const frame = document.getElementById('erp-main');

    if (frame) {
        refreshFrameAlpine(frame);
    } else {
        syncShellFromFrame();
    }
});
