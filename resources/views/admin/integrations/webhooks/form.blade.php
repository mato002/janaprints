@props(['webhook' => null, 'events', 'statuses'])

<div class="space-y-4">
    <div><label class="erp-label">{{ __('Name') }}</label><input type="text" name="name" value="{{ old('name', $webhook?->name) }}" class="erp-input w-full" required></div>
    <div><label class="erp-label">{{ __('Endpoint URL') }}</label><input type="url" name="endpoint_url" value="{{ old('endpoint_url', $webhook?->endpoint_url) }}" class="erp-input w-full" required></div>
    <div>
        <label class="erp-label">{{ __('Secret') }}</label>
        <input type="password" name="secret" class="erp-input w-full" placeholder="{{ $webhook?->secret ? __('Leave blank to keep current') : __('Auto-generated if blank') }}" autocomplete="off">
    </div>
    <div>
        <label class="erp-label">{{ __('Status') }}</label>
        <select name="status" class="erp-select w-full">
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(old('status', $webhook?->status?->value ?? 'active') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
    </div>
    <div><label class="erp-label">{{ __('Retry count') }}</label><input type="number" name="retry_count" value="{{ old('retry_count', $webhook?->retry_count ?? 3) }}" class="erp-input w-full" min="0" max="10"></div>
    <div>
        <label class="erp-label">{{ __('Event types') }}</label>
        <div class="mt-2 grid gap-2 sm:grid-cols-2">
            @foreach ($events as $event)
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="event_types[]" value="{{ $event->value }}" @checked(in_array($event->value, old('event_types', $webhook?->event_types ?? []), true))>
                    {{ $event->label() }}
                </label>
            @endforeach
        </div>
    </div>
</div>
