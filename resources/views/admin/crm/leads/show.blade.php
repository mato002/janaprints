<x-admin-layout :title="$lead->lead_name" :breadcrumbs="[['label' => __('Leads'), 'url' => route('admin.crm.leads.index')], ['label' => $lead->lead_name]]">
    <x-admin.page-header :title="$lead->lead_name" :description="__('Lead details and follow-ups.')">
        <x-slot name="actions">
            @if ($lead->customer_id && $lead->customer)
                <a href="{{ route('admin.crm.customers.show', $lead->customer) }}" class="erp-btn-secondary" data-turbo-frame="erp-main">{{ __('Open Customer') }}</a>
            @elseif(auth()->user()?->can('convert', $lead))
                <form method="POST" action="{{ route('admin.crm.leads.convert', $lead) }}" class="inline">@csrf
                    <button type="submit" class="erp-btn-primary">{{ __('Convert to Customer') }}</button>
                </form>
            @endif
            @can('update', $lead)
                <a href="{{ route('admin.crm.leads.edit', $lead) }}" class="erp-btn-secondary" data-turbo-frame="erp-main">{{ __('Edit Lead') }}</a>
                @if ($lead->status !== App\Enums\LeadStatus::Lost)
                    <form method="POST" action="{{ route('admin.crm.leads.mark-lost', $lead) }}" class="inline">@csrf
                        <button type="submit" class="erp-btn-secondary">{{ __('Mark Lost') }}</button>
                    </form>
                @endif
            @endcan
            @can('delete', $lead)
                <form method="POST" action="{{ route('admin.crm.leads.destroy', $lead) }}" class="inline" onsubmit="return confirm(@js(__('Delete this lead?')))">@csrf @method('DELETE')
                    <button type="submit" class="text-sm text-red-600">{{ __('Delete') }}</button>
                </form>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-admin.card>
            <div class="space-y-2 p-4 text-sm">
                <p><strong>{{ __('Stage') }}:</strong> {{ $lead->stage?->name ?? '—' }}</p>
                <p><strong>{{ __('Source') }}:</strong> {{ $lead->leadSource?->name ?? '—' }}</p>
                <p><strong>{{ __('Value') }}:</strong> {{ number_format($lead->estimated_value, 2) }}</p>
                <p><strong>{{ __('Status') }}:</strong> {{ $lead->status->value }}</p>
                @if ($lead->customer)
                    <p><strong>{{ __('Customer') }}:</strong> <a href="{{ route('admin.crm.customers.show', $lead->customer) }}" class="text-erp-accent hover:underline">{{ $lead->customer->company_name }}</a></p>
                @endif
            </div>
        </x-admin.card>
        <x-admin.card>
            <div class="p-4">
                <h3 class="mb-3 font-medium">{{ __('Follow-ups') }}</h3>
                @foreach ($lead->followUps as $fu)
                    <div class="flex items-center justify-between border-b py-2 text-sm">
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
        </x-admin.card>
    </div>
</x-admin-layout>
