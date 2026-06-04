<div class="erp-form-grid">
    <div>
        <x-input-label for="department_id" :value="__('Department')" />
        <select id="department_id" name="department_id" class="erp-select mt-1 w-full">
            <option value="">{{ __('None') }}</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}" @selected(old('department_id', $purchaseRequest->department_id ?? '') == $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <x-input-label for="required_date" :value="__('Required date')" />
        <x-text-input id="required_date" name="required_date" type="date" class="mt-1 block w-full" :value="old('required_date', optional($purchaseRequest->required_date ?? null)?->format('Y-m-d'))" />
    </div>
    <div class="md:col-span-2">
        <x-input-label for="reason" :value="__('Reason')" />
        <x-text-input id="reason" name="reason" class="mt-1 block w-full" :value="old('reason', $purchaseRequest->reason ?? '')" />
    </div>
    <div class="md:col-span-2">
        <x-input-label for="notes" :value="__('Notes')" />
        <textarea id="notes" name="notes" class="erp-input mt-1 w-full" rows="2">{{ old('notes', $purchaseRequest->notes ?? '') }}</textarea>
    </div>
</div>
