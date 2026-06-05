@php($record = $complaint ?? null)
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
        <label class="erp-label">{{ __('Source') }}</label>
        <select name="source" class="erp-input w-full" required>
            @foreach (App\Enums\CommercialComplaintSource::cases() as $source)
                <option value="{{ $source->value }}" @selected(old('source', $record?->source?->value ?? 'other') === $source->value)>{{ $source->label() }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="erp-label">{{ __('Priority') }}</label>
        <select name="priority" class="erp-input w-full" required>
            @foreach (App\Enums\CommercialComplaintPriority::cases() as $priority)
                <option value="{{ $priority->value }}" @selected(old('priority', $record?->priority?->value ?? 'medium') === $priority->value)>{{ $priority->label() }}</option>
            @endforeach
        </select>
    </div>
</div>
