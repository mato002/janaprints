<x-admin-layout :title="$lead->lead_name" :breadcrumbs="[['label' => __('Leads'), 'url' => route('admin.crm.leads.index')], ['label' => $lead->lead_name]]">
    <x-slot name="header">
        <h2 class="text-xl font-semibold">{{ $lead->lead_name }}</h2>
        @can('update', $lead)<a href="{{ route('admin.crm.leads.edit', $lead) }}" class="text-indigo-600 text-sm">{{ __('Edit') }}</a>@endcan
    </x-slot>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white shadow rounded-lg p-6 text-sm space-y-2">
            <p><strong>{{ __('Stage') }}:</strong> {{ $lead->stage?->name }}</p>
            <p><strong>{{ __('Source') }}:</strong> {{ $lead->leadSource?->name }}</p>
            <p><strong>{{ __('Value') }}:</strong> {{ number_format($lead->estimated_value, 2) }}</p>
            <p><strong>{{ __('Status') }}:</strong> {{ $lead->status->value }}</p>
        </div>
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="font-medium mb-3">{{ __('Follow-ups') }}</h3>
            @foreach ($lead->followUps as $fu)
                <div class="text-sm border-b py-2 flex justify-between items-center">
                    <span>{{ $fu->scheduled_at->format('Y-m-d H:i') }} — {{ $fu->status->value }}</span>
                    @can('update', $lead)
                        <form method="POST" action="{{ route('admin.crm.leads.follow-ups.update', [$lead, $fu]) }}">@csrf @method('PATCH')
                            <input type="hidden" name="status" value="completed">
                            <button class="text-xs text-green-600">{{ __('Complete') }}</button>
                        </form>
                    @endcan
                </div>
            @endforeach
            @can('update', $lead)
                <form method="POST" action="{{ route('admin.crm.leads.follow-ups.store', $lead) }}" class="mt-4 space-y-2">@csrf
                    <x-text-input name="scheduled_at" type="datetime-local" class="w-full" required />
                    <textarea name="notes" class="w-full rounded-md border-gray-300 text-sm" rows="2"></textarea>
                    <x-primary-button class="text-xs">{{ __('Schedule follow-up') }}</x-primary-button>
                </form>
            @endcan
        </div>
    </div>
</x-admin-layout>
