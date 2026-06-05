@php($book = $priceBook ?? null)
<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <label class="erp-label">{{ __('Name') }}</label>
        <input type="text" name="name" class="erp-input w-full" value="{{ old('name', $book?->name) }}" required>
    </div>
    <div>
        <label class="erp-label">{{ __('Code') }}</label>
        <input type="text" name="code" class="erp-input w-full" value="{{ old('code', $book?->code) }}" required>
    </div>
    <div class="md:col-span-2">
        <label class="erp-label">{{ __('Description') }}</label>
        <textarea name="description" class="erp-input w-full" rows="3">{{ old('description', $book?->description) }}</textarea>
    </div>
    <div>
        <label class="erp-label">{{ __('Currency') }}</label>
        <input type="text" name="currency" class="erp-input w-full" value="{{ old('currency', $book?->currency ?? 'KES') }}" required>
    </div>
    <div>
        <label class="erp-label">{{ __('Branch') }}</label>
        <select name="branch_id" class="erp-input w-full">
            <option value="">{{ __('Company-wide') }}</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" @selected(old('branch_id', $book?->branch_id) == $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="erp-label">{{ __('Status') }}</label>
        <select name="status" class="erp-input w-full" required>
            @foreach (App\Enums\CommercialPriceBookStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(old('status', $book?->status?->value ?? 'active') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="erp-label">{{ __('Starts at') }}</label>
        <input type="date" name="starts_at" class="erp-input w-full" value="{{ old('starts_at', $book?->starts_at?->toDateString()) }}">
    </div>
    <div>
        <label class="erp-label">{{ __('Ends at') }}</label>
        <input type="date" name="ends_at" class="erp-input w-full" value="{{ old('ends_at', $book?->ends_at?->toDateString()) }}">
    </div>
    <div class="md:col-span-2">
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $book?->is_default))>
            {{ __('Set as default price book for this scope') }}
        </label>
    </div>
</div>
