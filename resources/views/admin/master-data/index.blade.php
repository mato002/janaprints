@php
    $registry = app(\App\Support\MasterData\MasterDataRegistry::class);
@endphp

<x-admin-layout
    :title="__('Master Data')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('Configuration'), 'url' => route('admin.workspaces.administration.section', ['section' => 'configuration'])],
        ['label' => __('Master Data')],
    ]"
>
    <x-admin.page-header
        :title="__('Master Data Center')"
        :description="__('Centralized reference data used across Commercial, Production, Supply Chain, Inventory, Accounting, HR, and Communications.')"
    >
        <x-slot name="actions">
            @if ($canExport)
                <a href="{{ route('admin.master-data.export', array_filter(['category' => $category !== 'all' ? $category : null])) }}" class="erp-btn-secondary">{{ __('Export') }}</a>
            @endif
            @if ($canCreate)
                <a href="{{ route('admin.master-data.create', array_filter(['category' => $category !== 'all' ? $category : null])) }}" class="erp-btn-primary">{{ __('Create value') }}</a>
            @endif
        </x-slot>
    </x-admin.page-header>

    <form method="GET" action="{{ route('admin.master-data.index') }}" class="mb-4 grid gap-3 lg:grid-cols-4">
        <div class="lg:col-span-2">
            <input type="search" name="search" value="{{ $search }}" class="erp-input w-full text-sm" placeholder="{{ __('Search code, name, or description…') }}" />
        </div>
        <select name="category" class="erp-input text-sm">
            <option value="all">{{ __('All categories') }}</option>
            @foreach ($categories as $option)
                <option value="{{ $option['value'] }}" @selected($category === $option['value'])>{{ $option['module'] }} · {{ $option['label'] }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <select name="status" class="erp-input flex-1 text-sm">
                <option value="all">{{ __('All statuses') }}</option>
                <option value="active" @selected($status === 'active')>{{ __('Active') }}</option>
                <option value="inactive" @selected($status === 'inactive')>{{ __('Inactive') }}</option>
            </select>
            <button type="submit" class="erp-btn-primary text-sm">{{ __('Apply') }}</button>
        </div>
    </form>

    @if ($canImport)
        <x-admin.card class="mb-4">
            <form method="POST" action="{{ route('admin.master-data.import') }}" enctype="multipart/form-data" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                @csrf
                <div class="flex-1">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Bulk import (CSV)') }}</label>
                    <input type="file" name="file" accept=".csv,text/csv" class="erp-input mt-1 w-full text-sm" required />
                </div>
                <button type="submit" class="erp-btn-secondary">{{ __('Import') }}</button>
            </form>
        </x-admin.card>
    @endif

    <x-admin.data-table
        :search-placeholder="__('Search master data…')"
        export-filename="master-data"
        :chips="[
            ['id' => 'all', 'label' => __('All')],
            ['id' => 'active', 'label' => __('Active')],
            ['id' => 'inactive', 'label' => __('Inactive')],
        ]"
    >
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Category') }}</th>
                <th scope="col">{{ __('Code') }}</th>
                <th scope="col">{{ __('Name') }}</th>
                <th scope="col" class="hidden lg:table-cell">{{ __('Description') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Sort') }}</th>
                <th scope="col" class="hidden xl:table-cell">{{ __('Created By') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($values as $value)
                @php
                    $chip = $value->is_active ? 'active' : 'inactive';
                    $searchBlob = strtolower(implode(' ', [$value->category_key, $value->code, $value->name, $value->description]));
                @endphp
                <tr x-show="rowVisible(@js($searchBlob), @js($chip))">
                    <td>
                        <div class="font-medium text-erp-primary">{{ $registry->categoryLabel($value->category_key) }}</div>
                        <div class="text-[11px] text-slate-500">{{ $registry->moduleLabel($value->category_key) }}</div>
                    </td>
                    <td class="font-mono text-xs">{{ $value->code }}</td>
                    <td class="font-medium">{{ $value->name }}</td>
                    <td class="hidden lg:table-cell text-slate-600">{{ Str::limit($value->description, 60) ?: '—' }}</td>
                    <td>
                        <x-admin.status-badge :variant="$value->is_active ? 'success' : 'draft'">
                            {{ $value->is_active ? __('Active') : __('Inactive') }}
                        </x-admin.status-badge>
                    </td>
                    <td class="hidden md:table-cell text-slate-500">{{ $value->sort_order }}</td>
                    <td class="hidden xl:table-cell text-slate-500">{{ $value->creator?->name ?? '—' }}</td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            @can('update', $value)
                                <x-admin.table-row-action :href="route('admin.master-data.edit', $value)">{{ __('Edit') }}</x-admin.table-row-action>
                            @endcan
                            @can('deactivate', $value)
                                @if ($value->is_active)
                                    <x-admin.table-row-action
                                        :action="route('admin.master-data.deactivate', $value)"
                                        method="PATCH"
                                        :confirm="__('Deactivate this value? Active workflows using it may be affected.')"
                                    >{{ __('Deactivate') }}</x-admin.table-row-action>
                                @else
                                    <x-admin.table-row-action :action="route('admin.master-data.reactivate', $value)" method="PATCH">{{ __('Reactivate') }}</x-admin.table-row-action>
                                @endif
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">
                        <x-admin.empty-state icon="template" :title="__('No master data values yet')" :description="__('Create lookup values to replace hardcoded dropdowns across the ERP.')" />
                    </td>
                </tr>
            @endforelse
        </x-slot>
        <x-slot name="footer">
            <x-admin.table-pagination :paginator="$values" />
        </x-slot>
    </x-admin.data-table>
</x-admin-layout>
