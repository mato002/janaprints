<x-admin-layout :title="__('Maintenance Plans')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Maintenance Plans')]]">
    <x-admin.page-header :title="__('Maintenance Plans')" :description="__('Preventive maintenance schedules and upcoming due dates.')">
        <x-slot name="actions">@can('create', \App\Models\Assets\MaintenancePlan::class)<a href="{{ route('admin.assets.maintenance.plans.create') }}" class="erp-btn-primary">{{ __('New Plan') }}</a>@endcan</x-slot>
    </x-admin.page-header>
    <div class="mb-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <x-admin.card><h3 class="mb-2 text-sm font-semibold">{{ __('Overdue') }} ({{ $overdue->count() }})</h3><ul class="text-sm space-y-1">@forelse ($overdue as $plan)<li>{{ $plan->plan_name }} — {{ $plan->asset?->asset_name }} ({{ $plan->next_due_date?->format('Y-m-d') }})</li>@empty<li class="text-slate-500">{{ __('None') }}</li>@endforelse</ul></x-admin.card>
        <x-admin.card><h3 class="mb-2 text-sm font-semibold">{{ __('Upcoming (30 days)') }}</h3><ul class="text-sm space-y-1">@forelse ($upcoming->take(8) as $entry)<li>{{ $entry['label'] ?? $entry['plan']->plan_name }} — {{ $entry['asset_name'] }} ({{ $entry['due_date'] }})</li>@empty<li class="text-slate-500">{{ __('None') }}</li>@endforelse</ul></x-admin.card>
    </div>
    <x-admin.card>
        <table class="erp-table w-full text-sm"><thead><tr><th>{{ __('Plan') }}</th><th>{{ __('Asset') }}</th><th>{{ __('Frequency') }}</th><th>{{ __('Next Due') }}</th><th>{{ __('Active') }}</th></tr></thead>
        <tbody>@forelse ($plans as $plan)<tr><td>{{ $plan->plan_name }}</td><td>{{ $plan->asset?->asset_name }}</td><td>{{ $plan->frequency_type->label() }}</td><td>{{ $plan->next_due_date?->format('Y-m-d') ?? '—' }}</td><td>{{ $plan->is_active ? __('Yes') : __('No') }}</td></tr>@empty<tr><td colspan="5" class="py-8 text-center text-slate-500">{{ __('No plans yet.') }}</td></tr>@endforelse</tbody></table>
        @if ($plans->hasPages())<div class="mt-4">{{ $plans->links() }}</div>@endif
    </x-admin.card>
</x-admin-layout>
