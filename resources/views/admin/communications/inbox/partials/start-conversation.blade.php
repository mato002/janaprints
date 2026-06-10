@can('viewAny', App\Models\Communications\Inbox\CommunicationConversation::class)
    <div class="space-y-1.5">
        <form method="GET" class="flex gap-1" data-turbo-frame="{{ $inboxTurboFrame }}">
            @foreach (request()->except(['pick_q', 'page']) as $key => $value)
                @if (is_scalar($value) && $value !== '')
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            <input type="search" name="pick_q" value="{{ $pickQ ?? '' }}" class="erp-input min-w-0 flex-1 text-xs"
                   placeholder="{{ __('Customer name, code, phone…') }}">
            <button type="submit" class="erp-btn erp-btn--secondary erp-btn--xs shrink-0">{{ __('Find') }}</button>
        </form>

        <form method="POST" action="{{ route('admin.communications.inbox.start') }}" class="flex gap-1" data-turbo-frame="{{ $inboxTurboFrame }}">
            @csrf
            <select name="customer_id" class="erp-input min-w-0 flex-1 text-xs" required>
                <option value="">{{ __('Select customer…') }}</option>
                @forelse ($pickCustomers as $customer)
                    <option value="{{ $customer->id }}">
                        {{ $customer->company_name }}
                        @if ($customer->customer_code) ({{ $customer->customer_code }}) @endif
                    </option>
                @empty
                    <option value="" disabled>{{ __('Search to find customers') }}</option>
                @endforelse
            </select>
            <button type="submit" class="erp-btn erp-btn--primary erp-btn--xs shrink-0" @disabled($pickCustomers->isEmpty())>
                {{ __('Open') }}
            </button>
        </form>

        @can('crm.customers.view')
            <x-admin.crm-btn variant="ghost" size="xs" :href="route('admin.crm.customers.index')" class="w-full justify-center" data-turbo-frame="erp-main">
                {{ __('All customers') }}
            </x-admin.crm-btn>
        @endcan
    </div>
@endcan
