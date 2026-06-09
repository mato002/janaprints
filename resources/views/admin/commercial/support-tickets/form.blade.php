@php
    $record = $ticket ?? null;
    $fields = $formFields ?? [];
@endphp
<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    @if (($fields['customer_id']['visible'] ?? true))
        <div class="md:col-span-2">
            <label class="erp-label">{{ $fields['customer_id']['label'] ?? __('Customer') }}</label>
            <select name="customer_id" class="erp-input w-full" @required($fields['customer_id']['required'] ?? false) @disabled($fields['customer_id']['read_only'] ?? false)>
                <option value="">{{ __('No customer linked') }}</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" @selected(old('customer_id', $record?->customer_id) == $customer->id)>{{ $customer->company_name }}</option>
                @endforeach
            </select>
        </div>
    @endif
    @if (($fields['subject']['visible'] ?? true))
        <div class="md:col-span-2">
            <label class="erp-label">{{ $fields['subject']['label'] ?? __('Subject') }}</label>
            <input type="text" name="subject" class="erp-input w-full" value="{{ old('subject', $record?->subject) }}" @required($fields['subject']['required'] ?? true) @readonly($fields['subject']['read_only'] ?? false)>
        </div>
    @endif
    @if (($fields['description']['visible'] ?? true))
        <div class="md:col-span-2">
            <label class="erp-label">{{ $fields['description']['label'] ?? __('Description') }}</label>
            <textarea name="description" class="erp-input w-full" rows="5" @required($fields['description']['required'] ?? true) @readonly($fields['description']['read_only'] ?? false)>{{ old('description', $record?->description) }}</textarea>
        </div>
    @endif
    @if (($fields['channel']['visible'] ?? true))
        <div>
            <label class="erp-label">{{ $fields['channel']['label'] ?? __('Channel') }}</label>
            <select name="channel" class="erp-input w-full" @required($fields['channel']['required'] ?? true) @disabled($fields['channel']['read_only'] ?? false)>
                @foreach (App\Enums\CommercialTicketChannel::cases() as $channel)
                    <option value="{{ $channel->value }}" @selected(old('channel', $record?->channel?->value ?? 'phone') === $channel->value)>{{ $channel->label() }}</option>
                @endforeach
            </select>
        </div>
    @endif
    @if (($fields['priority']['visible'] ?? true))
        <div>
            <label class="erp-label">{{ $fields['priority']['label'] ?? __('Priority') }}</label>
            <select name="priority" class="erp-input w-full" @required($fields['priority']['required'] ?? true) @disabled($fields['priority']['read_only'] ?? false)>
                @foreach (App\Enums\CommercialTicketPriority::cases() as $priority)
                    <option value="{{ $priority->value }}" @selected(old('priority', $record?->priority?->value ?? 'medium') === $priority->value)>{{ $priority->label() }}</option>
                @endforeach
            </select>
        </div>
    @endif
</div>
@include('admin.partials.form-custom-fields', ['fields' => $fields, 'model' => $record ?? null])
