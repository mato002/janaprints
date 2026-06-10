@php
    $filters = $filters ?? [];
    $filterOptions = $filter_options ?? [];
    $bulkActions = $bulk_actions ?? [];
    $hasBulk = count($bulkActions) > 0;
@endphp

<x-admin-layout
    :title="__('Asset register')"
    :breadcrumbs="[
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Asset Register')],
    ]"
>
    <x-admin.page-header
        :title="__('Asset Register')"
        :description="__('All company assets.')"
    >
        @if ($can_create ?? false)
            <x-slot name="actions">
                <a href="{{ route('admin.assets.create') }}" class="erp-btn-primary" data-erp-modal-open>{{ __('Register Asset') }}</a>
            </x-slot>
        @endif
    </x-admin.page-header>

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
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assets as $asset)
                        <tr>
                            @if ($hasBulk)
                                <td><input type="checkbox" name="asset_ids[]" value="{{ $asset->id }}" class="asset-row-checkbox" form="asset-bulk-form"></td>
                            @endif
                            <td><a href="{{ route('admin.assets.show', $asset) }}" class="erp-link font-medium">{{ $asset->asset_number }}</a></td>
                            <td>{{ $asset->asset_name }}</td>
                            <td>{{ $asset->category?->name }}</td>
                            <td>{{ $asset->branch?->name ?? '—' }}</td>
                            <td>{{ $asset->assignedUser?->name ?? '—' }}</td>
                            <td class="text-right">{{ number_format($asset->acquisition_cost, 2) }}</td>
                            <td class="text-right">{{ number_format($asset->netBookValue(), 2) }}</td>
                            <td><x-admin.status-badge :variant="$asset->status->badgeVariant()">{{ $asset->status->label() }}</x-admin.status-badge></td>
                            <td>{{ $asset->created_at?->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $hasBulk ? 10 : 9 }}" class="py-8 text-center text-slate-500">{{ __('No assets found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($assets->hasPages())
            <div class="mt-4 border-t border-erp-border pt-3">{{ $assets->links() }}</div>
        @endif
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
