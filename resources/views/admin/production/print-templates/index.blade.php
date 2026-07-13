<x-admin-layout :title="__('Print Product Templates')" :breadcrumbs="[['label' => __('Production'), 'url' => route('admin.workspaces.production')], ['label' => __('Print Templates')]]">
    <x-admin.page-header :title="__('Print Product Templates')" :description="__('Manufacturing presets for common print products')">
        <x-slot name="export">
            @can('viewAny', App\Models\Production\PrintProductTemplate::class)
                <x-admin.export-dropdown
                    export-route="admin.production.print-templates.export"
                    :export-query="request()->query()"
                />
            @endcan
        </x-slot>
        <x-slot name="actions">
            @can('create', App\Models\Production\PrintProductTemplate::class)
                <a href="{{ route('admin.production.print-templates.create') }}" class="erp-btn-primary">{{ __('New template') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar
            :action="route('admin.production.print-templates.index')"
            :reset-url="route('admin.production.print-templates.index')"
        >
            <input
                type="search"
                name="search"
                value="{{ $filters['search'] }}"
                placeholder="{{ __('Search templates…') }}"
                class="erp-toolbar-input min-w-[12rem] flex-1"
                aria-label="{{ __('Search') }}"
                data-erp-auto-search
            >
            <select name="category" class="erp-toolbar-select" aria-label="{{ __('Category') }}">
                <option value="">{{ __('All categories') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->value }}" @selected($filters['category'] === $category->value)>{{ $category->label() }}</option>
                @endforeach
            </select>
            <select name="active" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
                <option value="">{{ __('All statuses') }}</option>
                <option value="1" @selected($filters['active'] === '1')>{{ __('Active') }}</option>
                <option value="0" @selected($filters['active'] === '0')>{{ __('Inactive') }}</option>
            </select>
        </x-admin.index-toolbar>
    </x-admin.card>

    <x-admin.data-table :searchable="false" :exportable="false">
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Code') }}</th>
                <th scope="col">{{ __('Name') }}</th>
                <th scope="col">{{ __('Category') }}</th>
                <th scope="col">{{ __('Department') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($templates as $template)
                <tr>
                    <td class="font-mono text-xs">{{ $template->code }}</td>
                    <td>
                        <a href="{{ route('admin.production.print-templates.show', $template) }}" class="font-medium text-erp-accent hover:underline">{{ $template->name }}</a>
                    </td>
                    <td>{{ $template->category?->label() }}</td>
                    <td>{{ $template->production_type ? str_replace('_', ' ', ucfirst($template->production_type->value)) : '—' }}</td>
                    <td>
                        <x-admin.status-badge :tone="$template->is_active ? 'green' : 'slate'">
                            {{ $template->is_active ? __('Active') : __('Inactive') }}
                        </x-admin.status-badge>
                    </td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            @can('view', $template)
                                <x-admin.table-row-action :href="route('admin.production.print-templates.show', $template)">{{ __('View') }}</x-admin.table-row-action>
                            @endcan
                            @can('update', $template)
                                <x-admin.table-row-action :href="route('admin.production.print-templates.edit', $template)">{{ __('Edit') }}</x-admin.table-row-action>
                            @endcan
                            @can('duplicate', $template)
                                <x-admin.table-row-action method="POST" :action="route('admin.production.print-templates.duplicate', $template)">{{ __('Duplicate') }}</x-admin.table-row-action>
                            @endcan
                            @can('update', $template)
                                <x-admin.table-row-action method="POST" :action="route('admin.production.print-templates.toggle-active', $template)">
                                    {{ $template->is_active ? __('Deactivate') : __('Activate') }}
                                </x-admin.table-row-action>
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <x-admin.empty-state icon="document-text" :title="__('No templates yet')" :description="__('Create manufacturing presets for common print products.')" />
                    </td>
                </tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$templates" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
