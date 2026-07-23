<x-admin-layout
    :title="__('Employee 360').' · '.$employee->full_name"
    :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Employees'), 'url' => route('admin.employees.index')], ['label' => $employee->full_name]]"
>
    <div
        class="employee-360"
        x-data="{
            tab: new URLSearchParams(window.location.search).get('tab') || 'overview',
            setTab(id) {
                this.tab = id;
                const url = new URL(window.location.href);
                url.searchParams.set('tab', id);
                window.history.replaceState({}, '', url);
                const more = this.$el.querySelector('.employee-360__more[open]');
                if (more) more.removeAttribute('open');
            },
        }"
    >
        @include('admin.hr.employees.360.header')
        @include('admin.hr.employees.360.kpi-strip')

        <nav class="employee-360__tabs" aria-label="{{ __('Employee workspace tabs') }}">
            <div class="employee-360__tabs-track">
                @foreach ($tabs as $tabDef)
                    <button
                        type="button"
                        class="employee-360__tab"
                        :class="tab === @js($tabDef['id']) ? 'is-active' : ''"
                        @click="setTab(@js($tabDef['id']))"
                    >
                        {{ $tabDef['label'] }}
                    </button>
                @endforeach
            </div>
        </nav>

        <div class="employee-360__panels">
            @foreach ($tabs as $tabDef)
                <div
                    class="employee-360__panel"
                    x-show="tab === @js($tabDef['id'])"
                    @if ($tabDef['id'] !== 'overview') x-cloak @endif
                >
                    @include('admin.hr.employees.360.tabs.'.$tabDef['id'])
                </div>
            @endforeach
        </div>

        <nav class="employee-360__mobile-bar" aria-label="{{ __('Quick actions') }}">
            @can('update', $employee)
                <a href="{{ route('admin.employees.edit', $employee) }}" class="erp-btn-primary" data-erp-modal-open>{{ __('Edit') }}</a>
            @endcan
            <button type="button" class="erp-btn-secondary" @click="setTab('timeline')">{{ __('Timeline') }}</button>
        </nav>
    </div>
</x-admin-layout>
