<div class="mb-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
    <x-admin.card>
        <div class="mb-2 flex items-center justify-between gap-2">
            <h3 class="text-sm font-semibold">{{ __('Overdue') }} ({{ $overdue->count() }})</h3>
            <a href="{{ $hubUrl }}?tab=calendar&view=overdue" class="text-xs text-erp-accent hover:underline" data-turbo-frame="module-workspace-content">{{ __('Calendar') }}</a>
        </div>
        <ul class="space-y-1 text-sm">
            @forelse ($overdue as $plan)
                <li>{{ $plan->plan_name }} — {{ $plan->asset?->asset_name }} ({{ $plan->next_due_date?->format('Y-m-d') }})</li>
            @empty
                <li class="text-slate-500">{{ __('None') }}</li>
            @endforelse
        </ul>
    </x-admin.card>
    <x-admin.card>
        <h3 class="mb-2 text-sm font-semibold">{{ __('Upcoming (30 days)') }}</h3>
        <ul class="space-y-1 text-sm">
            @forelse ($upcoming->take(8) as $entry)
                <li>{{ $entry['label'] ?? $entry['plan']->plan_name }} — {{ $entry['asset_name'] }} ({{ $entry['due_date'] }})</li>
            @empty
                <li class="text-slate-500">{{ __('None') }}</li>
            @endforelse
        </ul>
    </x-admin.card>
</div>

<x-admin.data-table
    :search-placeholder="__('Search plans…')"
    export-filename="maintenance-plans"
>
    <x-slot name="head">
        <tr>
            <th scope="col">{{ __('Plan') }}</th>
            <th scope="col">{{ __('Asset') }}</th>
            <th scope="col">{{ __('Frequency') }}</th>
            <th scope="col">{{ __('Next due') }}</th>
            <th scope="col">{{ __('Active') }}</th>
        </tr>
    </x-slot>
    <x-slot name="body">
        @forelse ($plans as $plan)
            @php
                $search = strtolower(($plan->plan_name ?? '').' '.($plan->asset?->asset_name ?? '').' '.($plan->frequency_type->value ?? ''));
            @endphp
            <tr x-show="rowVisible(@js($search))">
                <td class="font-medium">{{ $plan->plan_name }}</td>
                <td>{{ $plan->asset?->asset_name }}</td>
                <td>{{ $plan->frequency_type->label() }}</td>
                <td class="whitespace-nowrap">{{ $plan->next_due_date?->format('Y-m-d') ?? '—' }}</td>
                <td>{{ $plan->is_active ? __('Yes') : __('No') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5">
                    <x-admin.empty-state icon="calendar" :title="__('No plans yet')" :description="__('Create a maintenance plan to schedule preventive work.')">
                        @can('create', \App\Models\Assets\MaintenancePlan::class)
                            <x-slot name="action">
                                <a href="{{ route('admin.assets.maintenance.plans.create') }}" class="erp-btn-primary" data-erp-modal-open>{{ __('New plan') }}</a>
                            </x-slot>
                        @endcan
                    </x-admin.empty-state>
                </td>
            </tr>
        @endforelse
    </x-slot>
    <x-slot name="footer"><x-admin.table-pagination :paginator="$plans" /></x-slot>
</x-admin.data-table>
