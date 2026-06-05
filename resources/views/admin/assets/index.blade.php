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
                <a href="{{ route('admin.assets.create') }}" class="erp-btn-primary">{{ __('Register Asset') }}</a>
            </x-slot>
        @endif
    </x-admin.page-header>

    <x-admin.card class="mb-4">
        <form method="GET" action="{{ route('admin.assets.index') }}" class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-6">
            <div class="xl:col-span-2">
                <label class="text-xs text-slate-600" for="search">{{ __('Search') }}</label>
                <input id="search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" class="erp-input mt-1 w-full" placeholder="{{ __('Asset number, name, serial…') }}">
            </div>
            <div>
                <label class="text-xs text-slate-600" for="category_id">{{ __('Category') }}</label>
                <select id="category_id" name="category_id" class="erp-select mt-1 w-full">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($filterOptions['categories'] ?? [] as $category)
                        <option value="{{ $category->id }}" @selected((string) ($filters['category_id'] ?? '') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-slate-600" for="status">{{ __('Status') }}</label>
                <select id="status" name="status" class="erp-select mt-1 w-full">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($filterOptions['statuses'] ?? [] as $status)
                        <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-slate-600" for="branch_id">{{ __('Branch') }}</label>
                <select id="branch_id" name="branch_id" class="erp-select mt-1 w-full">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($filterOptions['branches'] ?? [] as $branch)
                        <option value="{{ $branch->id }}" @selected((string) ($filters['branch_id'] ?? '') === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-slate-600" for="assigned_to_user_id">{{ __('Assigned To') }}</label>
                <select id="assigned_to_user_id" name="assigned_to_user_id" class="erp-select mt-1 w-full">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($filterOptions['users'] ?? [] as $user)
                        <option value="{{ $user->id }}" @selected((string) ($filters['assigned_to_user_id'] ?? '') === (string) $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-slate-600" for="sort">{{ __('Sort') }}</label>
                <select id="sort" name="sort" class="erp-select mt-1 w-full">
                    <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>{{ __('Newest') }}</option>
                    <option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>{{ __('Oldest') }}</option>
                    <option value="cost_high" @selected(($filters['sort'] ?? '') === 'cost_high')>{{ __('Cost High-Low') }}</option>
                    <option value="cost_low" @selected(($filters['sort'] ?? '') === 'cost_low')>{{ __('Cost Low-High') }}</option>
                </select>
            </div>
            <div class="flex flex-wrap items-end gap-2 xl:col-span-6">
                <button type="submit" class="erp-btn-primary">{{ __('Apply') }}</button>
                <a href="{{ route('admin.assets.index') }}" class="erp-btn-secondary">{{ __('Reset') }}</a>
                @if ($can_export ?? false)
                    <a href="{{ route('admin.assets.export', array_merge(['format' => 'csv'], request()->query())) }}" class="erp-btn-secondary">{{ __('Export CSV') }}</a>
                    <a href="{{ route('admin.assets.export', array_merge(['format' => 'excel'], request()->query())) }}" class="erp-btn-secondary">{{ __('Export Excel') }}</a>
                @endif
            </div>
        </form>
    </x-admin.card>

    @if ($hasBulk)
        <form method="POST" action="{{ route('admin.assets.bulk') }}" id="asset-bulk-form">
            @csrf
    @endif

    <x-admin.card>
        @if ($hasBulk)
            <div class="mb-3 flex flex-wrap items-center gap-2 border-b border-erp-border pb-3">
                <select name="action" class="erp-select" required>
                    <option value="">{{ __('Mass action…') }}</option>
                    @foreach ($bulkActions as $action)
                        <option value="{{ $action['key'] }}">{{ $action['label'] }}</option>
                    @endforeach
                </select>
                <select name="assigned_to_user_id" class="erp-select">
                    <option value="">{{ __('Assign to user…') }}</option>
                    @foreach ($filterOptions['users'] ?? [] as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="erp-select">
                    <option value="">{{ __('Change status…') }}</option>
                    @foreach ($filterOptions['statuses'] ?? [] as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </select>
                <button type="submit" class="erp-btn-secondary">{{ __('Apply to selected') }}</button>
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        @if ($hasBulk)
                            <th class="w-8"><input type="checkbox" id="select-all-assets"></th>
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
            document.getElementById('select-all-assets')?.addEventListener('change', (e) => {
                document.querySelectorAll('.asset-row-checkbox').forEach((cb) => cb.checked = e.target.checked);
            });
        </script>
    @endif
</x-admin-layout>
