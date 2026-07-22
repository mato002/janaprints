@php
    use App\Support\Navigation\WorkspaceEmbed;

    $filters = $filters ?? [];
    $filterOptions = $filter_options ?? [];
    $bulkActions = $bulk_actions ?? [];
    $hasBulk = count($bulkActions) > 0;
    $turboFrame = WorkspaceEmbed::turboFrame();
@endphp

<x-admin-layout
    :title="__('Asset Management')"
    :breadcrumbs="[
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Asset Management')],
    ]"
>
    <x-admin.page-header
        :title="__('Asset Management')"
        :description="__('Register, categories, and KPIs in one workspace.')"
    >
        <x-slot name="actions">
            @if ($can_create ?? false)
                <a href="{{ WorkspaceEmbed::url(route('admin.assets.create')) }}" class="erp-btn-primary" data-erp-modal-open>{{ __('Register asset') }}</a>
            @endif
            @can('create', \App\Models\Assets\AssetCategory::class)
                <a href="{{ WorkspaceEmbed::url(route('admin.assets.categories.create')) }}" class="erp-btn-secondary" data-erp-modal-open>{{ __('New category') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    @include('admin.assets.partials.management-summary')

    @include('admin.assets.partials.management-categories')

    <div class="mb-3">
        <h2 class="text-sm font-semibold">{{ __('Asset Register') }}</h2>
        <p class="text-xs text-slate-500">{{ __('All company assets.') }}</p>
    </div>

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar
            :action="route('admin.assets.index')"
            :reset-url="route('admin.assets.index')"
            compact
            class="erp-index-toolbar-form--compact"
        >
            @if ($can_export ?? false)
                <x-slot name="export">
                    <x-admin.export-dropdown
                        export-route="admin.assets.export"
                        :export-query="request()->query()"
                        :format-in-path="true"
                        :can-export="true"
                    />
                </x-slot>
            @endif

            <input
                type="search"
                name="search"
                value="{{ $filters['search'] ?? '' }}"
                class="erp-toolbar-input"
                placeholder="{{ __('Asset number, name, serial…') }}"
                data-erp-auto-search
                aria-label="{{ __('Search') }}"
            >
            <select name="category_id" class="erp-toolbar-select" aria-label="{{ __('Category') }}">
                <option value="">{{ __('All categories') }}</option>
                @foreach ($filterOptions['categories'] ?? [] as $category)
                    <option value="{{ $category->id }}" @selected((string) ($filters['category_id'] ?? '') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <select name="branch_id" class="erp-toolbar-select" aria-label="{{ __('Branch') }}">
                <option value="">{{ __('All branches') }}</option>
                @foreach ($filterOptions['branches'] ?? [] as $branch)
                    <option value="{{ $branch->id }}" @selected((string) ($filters['branch_id'] ?? '') === (string) $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
            <select name="assigned_to_user_id" class="erp-toolbar-select" aria-label="{{ __('Assigned To') }}">
                <option value="">{{ __('All assignees') }}</option>
                @foreach ($filterOptions['users'] ?? [] as $user)
                    <option value="{{ $user->id }}" @selected((string) ($filters['assigned_to_user_id'] ?? '') === (string) $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
            <select name="sort" class="erp-toolbar-select" aria-label="{{ __('Sort') }}">
                <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>{{ __('Newest') }}</option>
                <option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>{{ __('Oldest') }}</option>
                <option value="cost_high" @selected(($filters['sort'] ?? '') === 'cost_high')>{{ __('Cost High-Low') }}</option>
                <option value="cost_low" @selected(($filters['sort'] ?? '') === 'cost_low')>{{ __('Cost Low-High') }}</option>
            </select>
            <select name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
                <option value="">{{ __('All statuses') }}</option>
                @foreach ($filterOptions['statuses'] ?? [] as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </x-admin.index-toolbar>
    </x-admin.card>

    @if ($hasBulk)
        <form method="POST" action="{{ route('admin.assets.bulk') }}" id="asset-bulk-form">
            @csrf
    @endif

    <x-admin.card>
        @if ($hasBulk)
            <x-admin.bulk-action-bar select-all-id="select-all-assets" checkbox-class="asset-row-checkbox" class="mb-3">
                <select name="action" class="erp-toolbar-select text-xs" required aria-label="{{ __('Bulk action') }}">
                    <option value="">{{ __('Bulk action…') }}</option>
                    @foreach ($bulkActions as $action)
                        <option value="{{ $action['key'] }}">{{ $action['label'] }}</option>
                    @endforeach
                </select>
                <select name="assigned_to_user_id" class="erp-toolbar-select text-xs" aria-label="{{ __('Assign to user') }}">
                    <option value="">{{ __('Assign to user…') }}</option>
                    @foreach ($filterOptions['users'] ?? [] as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="erp-toolbar-select text-xs" aria-label="{{ __('Change status') }}">
                    <option value="">{{ __('Change status…') }}</option>
                    @foreach ($filterOptions['statuses'] ?? [] as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </select>
                <button type="submit" class="erp-btn-secondary py-1 text-xs">{{ __('Apply to selected') }}</button>
            </x-admin.bulk-action-bar>
        @endif

        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        @if ($hasBulk)
                            <th class="w-8"><input type="checkbox" id="select-all-assets" aria-label="{{ __('Select all') }}"></th>
                        @endif
                        <th>{{ __('Asset Number') }}</th>
                        <th>{{ __('Asset Name') }}</th>
                        <th>{{ __('Category') }}</th>
                        <th>{{ __('Branch') }}</th>
                        <th>{{ __('Assigned To') }}</th>
                        <th class="text-right">{{ __('Acquisition Cost') }}</th>
                        <th class="text-right">{{ __('Book Value') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Created') }}</th>
                        <th class="erp-table-actions-col">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assets as $asset)
                        <tr>
                            @if ($hasBulk)
                                <td><input type="checkbox" name="asset_ids[]" value="{{ $asset->id }}" class="asset-row-checkbox" form="asset-bulk-form"></td>
                            @endif
                            <td class="font-medium">{{ $asset->asset_number }}</td>
                            <td>{{ $asset->asset_name }}</td>
                            <td>{{ $asset->category?->name }}</td>
                            <td>{{ $asset->branch?->name ?? '—' }}</td>
                            <td>{{ $asset->assignedUser?->name ?? '—' }}</td>
                            <td class="text-right tabular-nums">{{ number_format($asset->acquisition_cost, 2) }}</td>
                            <td class="text-right tabular-nums">{{ number_format($asset->netBookValue(), 2) }}</td>
                            <td><x-admin.status-badge :variant="$asset->status->badgeVariant()">{{ $asset->status->label() }}</x-admin.status-badge></td>
                            <td class="whitespace-nowrap">{{ $asset->created_at?->format('Y-m-d') }}</td>
                            <td class="erp-table-actions-col">
                                <x-admin.table-row-actions>
                                    <x-admin.table-row-action :href="route('admin.assets.show', $asset)">{{ __('View') }}</x-admin.table-row-action>
                                </x-admin.table-row-actions>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $hasBulk ? 11 : 10 }}">
                                <x-admin.empty-state icon="cube" :title="__('No assets found')" :description="__('Register an asset to start tracking the company register.')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-erp-border bg-white">
            <x-admin.table-pagination :paginator="$assets" />
        </div>
    </x-admin.card>

    @if ($hasBulk)
        </form>
        <script>
            document.getElementById('select-all-assets')?.addEventListener('change', (event) => {
                document.querySelectorAll('.asset-row-checkbox').forEach((checkbox) => {
                    checkbox.checked = event.target.checked;
                    checkbox.dispatchEvent(new Event('change', { bubbles: true }));
                });
            });
        </script>
    @endif
</x-admin-layout>
