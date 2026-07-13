<x-admin-layout
    :title="__('Media Library')"
    :breadcrumbs="[
        ['label' => __('Administration'), 'url' => route('admin.workspaces.administration')],
        ['label' => __('Website Content'), 'url' => route('admin.workspaces.administration.section', 'website-content')],
        ['label' => __('Media Library')],
    ]"
>
    <x-admin.page-header
        :title="__('Website Media Library')"
        :description="__('Replace storefront images by section. Each slot maps to a specific public page area. Uploads override config fallbacks without deleting static files.')"
    />

<div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <x-admin.stat-card :label="__('Total slots')" :value="(string) $summary['total']" />
        <x-admin.stat-card :label="__('Uploaded images')" :value="(string) $summary['uploaded']" />
        <x-admin.stat-card :label="__('Inactive slots')" :value="(string) $summary['inactive']" />
    </div>

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="route('admin.website.media.index')" :reset-url="route('admin.website.media.index')">
            <input
                type="search"
                name="q"
                value="{{ $filters['q'] ?? '' }}"
                class="erp-toolbar-input min-w-[12rem] flex-1"
                placeholder="{{ __('Search label or slot key…') }}"
                aria-label="{{ __('Search') }}"
                data-erp-auto-search
            >
            <select name="section" class="erp-toolbar-select" aria-label="{{ __('Section') }}">
                <option value="">{{ __('All sections') }}</option>
                @foreach ($sections as $value => $label)
                    <option value="{{ $value }}" @selected($activeSection === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
                <option value="">{{ __('All statuses') }}</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>{{ __('Active') }}</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>{{ __('Inactive') }}</option>
            </select>
            <select name="source" class="erp-toolbar-select" aria-label="{{ __('Image source') }}">
                <option value="">{{ __('All sources') }}</option>
                <option value="uploaded" @selected(($filters['source'] ?? '') === 'uploaded')>{{ __('Uploaded') }}</option>
                <option value="fallback" @selected(($filters['source'] ?? '') === 'fallback')>{{ __('Config fallback') }}</option>
            </select>
        </x-admin.index-toolbar>
    </x-admin.card>

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th class="w-24">{{ __('Preview') }}</th>
                        <th>{{ __('Slot') }}</th>
                        <th>{{ __('Where used') }}</th>
                        <th>{{ __('Section') }}</th>
                        <th>{{ __('Source') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="erp-table-actions-col w-24">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        @php($status = $item->sourceStatus())
                        <tr class="align-middle">
                            <td>
                                <div class="overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                                    <img
                                        src="{{ $item->previewUrl() }}"
                                        alt="{{ $item->alt_text }}"
                                        class="h-14 w-20 object-cover"
                                        loading="lazy"
                                        onerror="this.onerror=null;this.src='{{ asset('images/storefront/facility/production-floor.jpg') }}';"
                                    >
                                </div>
                            </td>
                            <td>
                                <div class="font-medium text-slate-900">{{ $item->label ?? $item->slot_key }}</div>
                                <div class="mt-0.5 font-mono text-[11px] text-slate-400">{{ $item->slot_key }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ Str::limit($item->alt_text, 60) }}</div>
                            </td>
                            <td class="max-w-xs text-xs text-slate-600">
                                {{ $usage->labelFor($item->slot_key, $item->section) }}
                            </td>
                            <td>
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700">
                                    {{ str_starts_with($item->slot_key, 'products.') ? ($sections['products'] ?? __('Products')) : ($sections[$item->section] ?? $item->section) }}
                                </span>
                            </td>
                            <td>
                                <x-admin.status-badge :variant="$status['variant']">{{ $status['label'] }}</x-admin.status-badge>
                            </td>
                            <td>
                                <x-admin.status-badge :variant="$item->is_active ? 'success' : 'neutral'">
                                    {{ $item->is_active ? __('Active') : __('Inactive') }}
                                </x-admin.status-badge>
                            </td>
                            <td class="erp-table-actions-col">
                                <x-admin.table-row-actions>
                                    @can('update', $item)
                                        <x-admin.table-row-action :href="route('admin.website.media.edit', $item)">
                                            <x-admin.icon name="cog" class="h-4 w-4" />
                                            {{ $item->hasUploadedImage() ? __('Replace') : __('Upload') }}
                                        </x-admin.table-row-action>
                                        @if ($item->hasUploadedImage())
                                            <x-admin.table-row-action
                                                :action="route('admin.website.media.reset-image', $item)"
                                                method="POST"
                                                :confirm="__('Remove the uploaded image and revert to config fallback?')"
                                            >
                                                <x-admin.icon name="refresh" class="h-4 w-4" />
                                                {{ __('Reset') }}
                                            </x-admin.table-row-action>
                                        @endif
                                        <x-admin.table-row-action
                                            :action="route('admin.website.media.toggle-active', $item)"
                                            method="POST"
                                            :confirm="$item->is_active ? __('Deactivate this slot? The storefront will use the config fallback.') : __('Activate this media slot?')"
                                        >
                                            <x-admin.icon name="switch-horizontal" class="h-4 w-4" />
                                            {{ $item->is_active ? __('Deactivate') : __('Activate') }}
                                        </x-admin.table-row-action>
                                    @endcan
                                </x-admin.table-row-actions>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12">
                                <x-admin.empty-state
                                    icon="photograph"
                                    :title="__('No media slots match your filters')"
                                    :description="__('Try clearing filters or run php artisan website:content-baseline to seed registry slots.')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
</x-admin-layout>
