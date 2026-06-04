<x-admin-layout :title="$activity->subject" :breadcrumbs="[['label' => __('Activities'), 'url' => route('admin.commercial.activities.index')], ['label' => $activity->subject]]">
    <x-admin.page-header :title="$activity->subject" :description="$activity->activity_at->format('Y-m-d H:i')">
        <x-slot name="actions">
            @can('update', $activity)
                <a href="{{ route('admin.commercial.activities.edit', $activity) }}" class="erp-btn-secondary">{{ __('Edit') }}</a>
            @endcan
            @if ($activity->customer)
                <x-admin.customer-360-action :customer="$activity->customer" />
            @endif
        </x-slot>
    </x-admin.page-header>

    <x-admin.card class="max-w-3xl">
        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 text-sm">
            <div><dt class="text-slate-500">{{ __('Type') }}</dt><dd class="font-medium">{{ ucfirst(str_replace('_', ' ', $activity->activity_type->value)) }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Status') }}</dt><dd><x-admin.enum-status-badge :status="$activity->status->value" /></dd></div>
            <div><dt class="text-slate-500">{{ __('Assigned to') }}</dt><dd>{{ $activity->user?->name ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Customer') }}</dt><dd>
                @if ($activity->customer)
                    <a href="{{ route('admin.crm.customers.show', $activity->customer) }}" class="text-erp-accent">{{ $activity->customer->company_name }}</a>
                @else — @endif
            </dd></div>
            <div><dt class="text-slate-500">{{ __('Lead') }}</dt><dd>
                @if ($activity->lead)
                    <a href="{{ route('admin.crm.leads.show', $activity->lead) }}" class="text-erp-accent">{{ $activity->lead->lead_name }}</a>
                @else — @endif
            </dd></div>
            <div class="sm:col-span-2"><dt class="text-slate-500">{{ __('Description') }}</dt><dd class="mt-1 whitespace-pre-wrap">{{ $activity->description ?: '—' }}</dd></div>
        </dl>
        @can('delete', $activity)
            <form method="POST" action="{{ route('admin.commercial.activities.destroy', $activity) }}" class="mt-6" onsubmit="return confirm(@js(__('Delete this activity?')))">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm text-red-600">{{ __('Delete activity') }}</button>
            </form>
        @endcan
    </x-admin.card>
</x-admin-layout>
