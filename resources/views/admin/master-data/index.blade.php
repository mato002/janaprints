@php
    use App\Support\Navigation\WorkspaceEmbed;

    $registry = app(\App\Support\MasterData\MasterDataRegistry::class);
    $embedded = WorkspaceEmbed::isEmbedded();
@endphp

<x-admin-layout
    :title="__('Master Data')"
    :breadcrumbs="$embedded ? [] : [
        ['label' => __('Administration')],
        ['label' => __('Configuration')],
        ['label' => __('Master Data')],
    ]"
    :use-workspace-navigation="! $embedded"
>
    <x-admin.page-header
        :title="__('Master Data Center')"
        :description="__('Centralized reference data used across Commercial, Production, Supply Chain, Inventory, Accounting, HR, and Communications.')"
    >
        <x-slot name="actions">
            @if ($canExport)
                <x-admin.export-dropdown
                    export-route="admin.master-data.export"
                    :export-query="array_filter(['category' => $category !== 'all' ? $category : null, 'search' => $search ?: null])"
                    :format-in-path="true"
                    :can-export="true"
                />
            @endif
            @if ($canCreate)
                <a href="{{ route('admin.master-data.create', array_filter(['category' => $category !== 'all' ? $category : null])) }}" class="erp-btn-primary">{{ __('Create value') }}</a>
            @endif
        </x-slot>
    </x-admin.page-header>

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="route('admin.master-data.index')" :reset-url="route('admin.master-data.index')">
            <input type="search" name="search" value="{{ $search }}" class="erp-toolbar-input min-w-[12rem] flex-1" placeholder="{{ __('Search code, name, or description…') }}" aria-label="{{ __('Search') }}" data-erp-auto-search>
            <select name="category" class="erp-toolbar-select" aria-label="{{ __('Category') }}">
                <option value="all">{{ __('All categories') }}</option>
                @foreach ($categories as $option)
                    <option value="{{ $option['value'] }}" @selected($category === $option['value'])>{{ $option['module'] }} · {{ $option['label'] }}</option>
                @endforeach
            </select>
            <x-admin.status-pills
                :options="[['value' => 'all', 'label' => __('All statuses')], ['value' => 'active', 'label' => __('Active')], ['value' => 'inactive', 'label' => __('Inactive')]]"
                param="status"
                :current="$status"
            />
        </x-admin.index-toolbar>
    </x-admin.card>

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
