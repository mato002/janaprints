@php
    $crm360Tabs = [
        'overview' => __('Overview'),
        'commercial' => __('Commercial'),
        'conversations' => __('Conversations'),
        'activities' => __('Activities'),
        'files' => __('Files'),
        'print-specifications' => __('Print Specifications'),
        'communications' => __('Communications'),
        'notes' => __('Notes'),
        'timeline' => __('Timeline'),
    ];
@endphp

<x-admin-layout
    :title="__('Customer 360 Workspace').' · '.$customer->company_name"
    :breadcrumbs="[['label' => __('Customers'), 'url' => route('admin.crm.customers.index')], ['label' => $customer->company_name]]"
>
    <div
        class="crm-360"
        x-data="{
            tab: (() => {
                const requested = new URLSearchParams(window.location.search).get('tab') || 'overview';
                return requested === 'artwork' ? 'print-specifications' : requested;
            })(),
            tabsMoreOpen: false,
            overflowIds: [],
            measuring: false,
            tabLabels: @js($crm360Tabs),
            setTab(id) {
                this.tab = id;
                this.tabsMoreOpen = false;
                const url = new URL(window.location.href);
                url.searchParams.set('tab', id);
                window.history.replaceState({}, '', url);
            },
            tabVisible(id) {
                return this.measuring || ! this.overflowIds.includes(id);
            },
            isMoreTabActive() {
                return this.overflowIds.includes(this.tab);
            },
            moreLabel() {
                if (this.isMoreTabActive() && this.tabLabels[this.tab]) {
                    return this.tabLabels[this.tab];
                }
                return @js(__('More'));
            },
            async measureTabs() {
                const nav = this.$refs.tabNav;
                if (! nav) return;

                if (window.matchMedia('(max-width: 767px)').matches) {
                    this.overflowIds = [];
                    this.tabsMoreOpen = false;
                    this.measuring = false;
                    return;
                }

                this.measuring = true;
                this.overflowIds = [];
                await this.$nextTick();

                const tabs = Array.from(nav.querySelectorAll('[data-crm-tab]'));
                const moreWrap = this.$refs.tabsMore;
                let moreWidth = 72;

                if (moreWrap) {
                    moreWrap.style.visibility = 'hidden';
                    moreWrap.style.display = 'block';
                    moreWidth = Math.max(moreWrap.offsetWidth || 0, 72);
                    moreWrap.style.visibility = '';
                    moreWrap.style.display = '';
                }

                const styles = getComputedStyle(nav);
                const gap = parseFloat(styles.columnGap || styles.gap || '0') || 0;
                const pad = (parseFloat(styles.paddingLeft) || 0) + (parseFloat(styles.paddingRight) || 0);
                const available = nav.clientWidth - pad;
                const widths = tabs.map((el) => el.offsetWidth);
                const total = widths.reduce((sum, w, i) => sum + w + (i > 0 ? gap : 0), 0);

                if (total <= available + 0.5) {
                    this.overflowIds = [];
                    this.measuring = false;
                    return;
                }

                let used = 0;
                let cutAt = tabs.length;

                for (let i = 0; i < tabs.length; i++) {
                    const next = used + (i > 0 ? gap : 0) + widths[i];
                    const remaining = tabs.length - i - 1;
                    if (remaining > 0 && next + gap + moreWidth > available) {
                        cutAt = Math.max(1, i);
                        break;
                    }
                    used = next;
                }

                this.overflowIds = tabs.slice(cutAt).map((el) => el.dataset.crmTab);
                this.measuring = false;
            },
            init() {
                this.$nextTick(() => this.measureTabs());
                this._onResize = () => {
                    clearTimeout(this._resizeTimer);
                    this._resizeTimer = setTimeout(() => this.measureTabs(), 80);
                };
                window.addEventListener('resize', this._onResize);
                if (typeof ResizeObserver !== 'undefined') {
                    this._ro = new ResizeObserver(() => this._onResize());
                    this.$nextTick(() => {
                        if (this.$refs.tabNav) this._ro.observe(this.$refs.tabNav);
                    });
                }
            },
            destroy() {
                window.removeEventListener('resize', this._onResize);
                clearTimeout(this._resizeTimer);
                if (this._ro) this._ro.disconnect();
            },
        }"
    >
        @include('admin.crm.customers.360._data')
        @include('admin.crm.customers.360.header')
        @include('admin.crm.customers.360.kpi-strip')

        <nav
            class="crm-360__tabs"
            x-ref="tabNav"
            aria-label="{{ __('Customer workspace tabs') }}"
        >
            @foreach ($crm360Tabs as $id => $label)
                <button
                    type="button"
                    class="crm-360__tab"
                    data-crm-tab="{{ $id }}"
                    x-show="tabVisible(@js($id))"
                    :class="tab === @js($id) && 'crm-360__tab--active'"
                    @click="setTab(@js($id))"
                    :aria-selected="tab === @js($id)"
                >
                    {{ $label }}
                </button>
            @endforeach

            <div
                class="crm-360__tabs-more relative shrink-0"
                x-ref="tabsMore"
                x-show="overflowIds.length > 0 && ! measuring"
                x-cloak
                @click.outside="tabsMoreOpen = false"
            >
                <button
                    type="button"
                    class="crm-360__tab crm-360__tab--more"
                    :class="isMoreTabActive() && 'crm-360__tab--active'"
                    @click="tabsMoreOpen = !tabsMoreOpen"
                    :aria-expanded="tabsMoreOpen"
                    aria-haspopup="true"
                >
                    <span x-text="moreLabel()"></span>
                    <svg class="ml-0.5 inline h-3.5 w-3.5 transition-transform" :class="tabsMoreOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div
                    x-show="tabsMoreOpen"
                    x-cloak
                    class="crm-360__tabs-more-menu"
                    role="menu"
                >
                    <template x-for="id in overflowIds" :key="id">
                        <button
                            type="button"
                            class="crm-360__tabs-more-item"
                            :class="tab === id && 'crm-360__tabs-more-item--active'"
                            role="menuitem"
                            @click="setTab(id)"
                            x-text="tabLabels[id]"
                        ></button>
                    </template>
                </div>
            </div>
        </nav>

        <div class="crm-360__panels">
            <div x-show="tab === 'overview'" class="crm-360__panel">
                @include('admin.crm.customers.360.tab-overview')
            </div>
            <div x-show="tab === 'conversations'" x-cloak class="crm-360__panel">
                @include('admin.crm.customers.360.tab-conversations')
            </div>
            <div x-show="tab === 'communications'" x-cloak class="crm-360__panel">
                @include('admin.crm.customers.360.tab-communications')
            </div>
            <div x-show="tab === 'commercial'" x-cloak class="crm-360__panel">
                @include('admin.crm.customers.360.tab-commercial')
            </div>
            <div x-show="tab === 'files'" x-cloak class="crm-360__panel">
                @include('admin.crm.customers.360.tab-files')
            </div>
            <div x-show="tab === 'activities'" x-cloak class="crm-360__panel">
                @include('admin.crm.customers.360.tab-activities')
            </div>
            <div x-show="tab === 'notes'" x-cloak class="crm-360__panel">
                @include('admin.crm.customers.360.tab-notes')
            </div>
            <div x-show="tab === 'timeline'" x-cloak class="crm-360__panel">
                @include('admin.crm.customers.360.tab-timeline')
            </div>
            <div x-show="tab === 'print-specifications'" x-cloak class="crm-360__panel">
                @include('admin.crm.customers.360.tab-print-specifications')
            </div>
        </div>
    </div>
</x-admin-layout>
