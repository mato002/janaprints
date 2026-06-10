<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
        <x-input-label for="branch_id" :value="__('Branch')" />
        <select name="branch_id" class="erp-select mt-1 w-full">
            <option value="">{{ __('All branches') }}</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <x-input-label for="period_start" :value="__('Period start')" />
        <x-text-input id="period_start" name="period_start" type="date" class="block mt-1 w-full" :value="old('period_start', now()->startOfMonth()->toDateString())" required />
    </div>
    <div>
        <x-input-label for="period_end" :value="__('Period end')" />
        <x-text-input id="period_end" name="period_end" type="date" class="block mt-1 w-full" :value="old('period_end', now()->endOfMonth()->toDateString())" required />
    </div>
    <div>
        <x-input-label for="pay_date" :value="__('Pay date')" />
        <x-text-input id="pay_date" name="pay_date" type="date" class="block mt-1 w-full" :value="old('pay_date', now()->endOfMonth()->toDateString())" required />
    </div>
    <div class="md:col-span-2">
        <x-input-label for="notes" :value="__('Notes')" />
        <textarea name="notes" rows="2" class="erp-input mt-1 w-full">{{ old('notes') }}</textarea>
    </div>
</div>
