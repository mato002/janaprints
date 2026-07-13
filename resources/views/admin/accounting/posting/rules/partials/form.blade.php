<x-admin.card>
    <div class="grid gap-3 sm:grid-cols-2">
        <label class="block text-sm">
            <span class="mb-1 block text-slate-600">{{ __('Event') }}</span>
            <select name="event_code" class="erp-input w-full" required @disabled($rule?->is_system)>
                @foreach ($events as $event)
                    <option value="{{ $event->value }}" @selected(old('event_code', $rule?->event_code) === $event->value)>
                        {{ $event->label() }} ({{ $event->value }})
                    </option>
                @endforeach
            </select>
            @if ($rule?->is_system)
                <input type="hidden" name="event_code" value="{{ $rule->event_code }}">
            @endif
        </label>
        <label class="block text-sm">
            <span class="mb-1 block text-slate-600">{{ __('Template') }}</span>
            <select name="posting_template_id" class="erp-input w-full" required>
                @foreach ($templates as $template)
                    <option value="{{ $template->id }}" @selected((int) old('posting_template_id', $rule?->posting_template_id) === $template->id)>
                        {{ $template->code }} — {{ $template->name }}
                    </option>
                @endforeach
            </select>
        </label>
        <label class="block text-sm">
            <span class="mb-1 block text-slate-600">{{ __('Name') }}</span>
            <input name="name" value="{{ old('name', $rule?->name) }}" class="erp-input w-full">
        </label>
        <label class="block text-sm">
            <span class="mb-1 block text-slate-600">{{ __('Priority') }}</span>
            <input type="number" name="priority" value="{{ old('priority', $rule?->priority ?? 100) }}" class="erp-input w-full" min="1" max="9999">
        </label>
        <label class="block text-sm">
            <span class="mb-1 block text-slate-600">{{ __('Active') }}</span>
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="erp-checkbox" @checked(old('is_active', $rule?->is_active ?? true))>
        </label>
        <label class="block text-sm">
            <span class="mb-1 block text-slate-600">{{ __('Auto post') }}</span>
            <input type="hidden" name="auto_post" value="0">
            <input type="checkbox" name="auto_post" value="1" class="erp-checkbox" @checked(old('auto_post', $rule?->auto_post ?? true))>
        </label>
        <label class="block text-sm sm:col-span-2">
            <span class="mb-1 block text-slate-600">{{ __('Description') }}</span>
            <textarea name="description" rows="2" class="erp-input w-full">{{ old('description', $rule?->description) }}</textarea>
        </label>
    </div>
</x-admin.card>
