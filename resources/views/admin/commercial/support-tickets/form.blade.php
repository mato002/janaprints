@php($record = $ticket ?? null)
<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
        <label class="erp-label">{{ __('Customer') }}</label>
        <select name="customer_id" class="erp-input w-full">
            <option value="">{{ __('No customer linked') }}</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}" @selected(old('customer_id', $record?->customer_id) == $customer->id)>{{ $customer->company_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="erp-label">{{ __('Subject') }}</label>
        <input type="text" name="subject" class="erp-input w-full" value="{{ old('subject', $record?->subject) }}" required>
    </div>
    <div class="md:col-span-2">
        <label class="erp-label">{{ __('Description') }}</label>
        <textarea name="description" class="erp-input w-full" rows="5" required>{{ old('description', $record?->description) }}</textarea>
    </div>
    <div>
        <label class="erp-label">{{ __('Channel') }}</label>
        <select name="channel" class="erp-input w-full" required>
            @foreach (App\Enums\CommercialTicketChannel::cases() as $channel)
                <option value="{{ $channel->value }}" @selected(old('channel', $record?->channel?->value ?? 'phone') === $channel->value)>{{ $channel->label() }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="erp-label">{{ __('Priority') }}</label>
        <select name="priority" class="erp-input w-full" required>
            @foreach (App\Enums\CommercialTicketPriority::cases() as $priority)
                <option value="{{ $priority->value }}" @selected(old('priority', $record?->priority?->value ?? 'medium') === $priority->value)>{{ $priority->label() }}</option>
            @endforeach
        </select>
    </div>
</div>
