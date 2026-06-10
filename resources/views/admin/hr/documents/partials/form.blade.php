<p class="text-sm text-slate-600 mb-4">{{ __('Store contracts, IDs, statutory records, and HR files.') }}</p>
<div class="grid gap-4 md:grid-cols-2">
    @include('admin.hr.partials.employee-lookup-select', [
        'employees' => $formData['employees'],
        'selectClass' => 'erp-input w-full',
        'class' => 'md:col-span-2',
    ])
    @error('employee_id')<p class="mt-1 text-sm text-rose-600 md:col-span-2">{{ $message }}</p>@enderror
    <div>
        <label class="erp-label" for="category">{{ __('Category') }}</label>
        <select id="category" name="category" class="erp-input w-full" required>
            <option value="">{{ __('Select category') }}</option>
            @foreach ($formData['categories'] as $category)
                <option value="{{ $category->value }}" @selected(old('category') === $category->value)>{{ $category->label() }}</option>
            @endforeach
        </select>
        @error('category')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="erp-label" for="title">{{ __('Title') }}</label>
        <input id="title" type="text" name="title" value="{{ old('title') }}" class="erp-input w-full" required>
        @error('title')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="erp-label" for="expires_at">{{ __('Expiry Date') }}</label>
        <input id="expires_at" type="date" name="expires_at" value="{{ old('expires_at') }}" class="erp-input w-full">
        @error('expires_at')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="erp-label" for="renewal_reminder_days">{{ __('Renewal Alert (days before)') }}</label>
        <input id="renewal_reminder_days" type="number" name="renewal_reminder_days" value="{{ old('renewal_reminder_days', 30) }}" min="1" max="365" class="erp-input w-full">
        @error('renewal_reminder_days')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div class="md:col-span-2">
        <label class="erp-label" for="description">{{ __('Description') }}</label>
        <textarea id="description" name="description" rows="3" class="erp-input w-full">{{ old('description') }}</textarea>
        @error('description')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div class="md:col-span-2">
        <label class="erp-label" for="file">{{ __('File') }}</label>
        <input id="file" type="file" name="file" class="erp-input w-full" required>
        @error('file')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div class="md:col-span-2">
        <label class="erp-label" for="notes">{{ __('Version Notes') }}</label>
        <input id="notes" type="text" name="notes" value="{{ old('notes') }}" class="erp-input w-full" placeholder="{{ __('Optional notes for this version') }}">
        @error('notes')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
</div>
