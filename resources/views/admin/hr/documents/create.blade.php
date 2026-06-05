<x-admin-layout :title="__('Upload Document')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Documents'), 'url' => route('admin.hr.documents.dashboard')], ['label' => __('Upload')]]">
    <x-admin.page-header :title="__('Upload Document')" :description="__('Store contracts, IDs, statutory records, and HR files.')" />

    <form method="POST" action="{{ route('admin.hr.documents.store') }}" enctype="multipart/form-data" class="erp-card max-w-3xl">
        @csrf
        <div class="grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="erp-label" for="employee_id">{{ __('Employee') }}</label>
                <select id="employee_id" name="employee_id" class="erp-input w-full" required>
                    <option value="">{{ __('Select employee') }}</option>
                    @foreach ($formData['employees'] as $employee)
                        <option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>{{ $employee->full_name }} ({{ $employee->employee_number }})</option>
                    @endforeach
                </select>
                @error('employee_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
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
        <div class="mt-6 flex gap-2">
            <button type="submit" class="erp-btn-primary">{{ __('Upload') }}</button>
            <a href="{{ route('admin.hr.documents.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin-layout>
