@php
    use App\Support\Navigation\WorkspaceEmbed;

    $turboFrame = WorkspaceEmbed::turboFrame();
@endphp

@if ($can_view_categories ?? false)
    <x-admin.card id="asset-categories" class="mb-4">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <div>
                <h2 class="text-sm font-semibold">{{ __('Asset Categories') }}</h2>
                <p class="text-xs text-slate-500">{{ __('Classification, useful life, and depreciation policies.') }}</p>
            </div>
            @can('create', \App\Models\Assets\AssetCategory::class)
                <a href="{{ WorkspaceEmbed::url(route('admin.assets.categories.create')) }}" class="erp-btn-secondary erp-btn--sm" data-erp-modal-open>{{ __('New category') }}</a>
            @endcan
        </div>

        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Useful life') }}</th>
                        <th>{{ __('Assets') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="erp-table-actions-col">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td class="font-medium">
                                <a
                                    href="{{ WorkspaceEmbed::url(route('admin.assets.index', WorkspaceEmbed::queryParams(['category_id' => $category->id]))) }}"
                                    data-turbo-frame="{{ $turboFrame }}"
                                    class="erp-link"
                                >{{ $category->name }}</a>
                            </td>
                            <td>{{ $category->code ?? '—' }}</td>
                            <td>{{ $category->asset_type?->label() ?? '—' }}</td>
                            <td>{{ $category->useful_life_years ?? ceil($category->useful_life_months / 12) }} {{ __('years') }}</td>
                            <td>{{ $category->assets_count }}</td>
                            <td>
                                @if ($category->is_active)
                                    <x-admin.status-badge variant="success">{{ __('Active') }}</x-admin.status-badge>
                                @else
                                    <x-admin.status-badge variant="neutral">{{ __('Inactive') }}</x-admin.status-badge>
                                @endif
                            </td>
                            <td class="erp-table-actions-col">
                                <x-admin.table-row-actions>
                                    @can('update', $category)
                                        <x-admin.table-row-action :href="route('admin.assets.categories.edit', $category)">{{ __('Edit') }}</x-admin.table-row-action>
                                    @endcan
                                    @can('archive', $category)
                                        <x-admin.table-row-action method="POST" :action="route('admin.assets.categories.archive', $category)" variant="danger" :confirm="__('Archive this category?')">{{ __('Archive') }}</x-admin.table-row-action>
                                    @endcan
                                </x-admin.table-row-actions>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-admin.empty-state icon="clipboard-list" :title="__('No categories found')" :description="__('Create a category to classify assets and set useful life.')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
@endif
