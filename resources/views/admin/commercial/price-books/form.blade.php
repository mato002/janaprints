@php
    $book = $priceBook ?? null;
    $fields = $formFields ?? [];
@endphp
<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    @if (($fields['name']['visible'] ?? true))
        <div>
            <label class="erp-label">{{ $fields['name']['label'] ?? __('Name') }}</label>
            <input type="text" name="name" class="erp-input w-full" value="{{ old('name', $book?->name) }}" @required($fields['name']['required'] ?? true) @readonly($fields['name']['read_only'] ?? false)>
        </div>
    @endif
    @if (($fields['code']['visible'] ?? true))
        <div>
            <label class="erp-label">{{ $fields['code']['label'] ?? __('Code') }}</label>
            <input type="text" name="code" class="erp-input w-full" value="{{ old('code', $book?->code) }}" @required($fields['code']['required'] ?? true) @readonly($fields['code']['read_only'] ?? false)>
        </div>
    @endif
    @if (($fields['description']['visible'] ?? true))
        <div class="md:col-span-2">
            <label class="erp-label">{{ $fields['description']['label'] ?? __('Description') }}</label>
            <textarea name="description" class="erp-input w-full" rows="3" @required($fields['description']['required'] ?? false) @readonly($fields['description']['read_only'] ?? false)>{{ old('description', $book?->description) }}</textarea>
        </div>
    @endif
    @if (($fields['currency']['visible'] ?? true))
        <div>
            <label class="erp-label">{{ $fields['currency']['label'] ?? __('Currency') }}</label>
            <input type="text" name="currency" class="erp-input w-full" value="{{ old('currency', $book?->currency ?? ($fields['currency']['default'] ?? 'KES')) }}" @required($fields['currency']['required'] ?? true) @readonly($fields['currency']['read_only'] ?? false)>
        </div>
    @endif
    @if (($fields['branch_id']['visible'] ?? true))
        <div>
            <label class="erp-label">{{ $fields['branch_id']['label'] ?? __('Branch') }}</label>
            <select name="branch_id" class="erp-input w-full" @required($fields['branch_id']['required'] ?? false) @disabled($fields['branch_id']['read_only'] ?? false)>
                <option value="">{{ __('Company-wide') }}</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected(old('branch_id', $book?->branch_id) == $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
    @endif
    @if (($fields['status']['visible'] ?? true))
        <div>
            <label class="erp-label">{{ $fields['status']['label'] ?? __('Status') }}</label>
            <select name="status" class="erp-input w-full" @required($fields['status']['required'] ?? true) @disabled($fields['status']['read_only'] ?? false)>
                @foreach (App\Enums\CommercialPriceBookStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $book?->status?->value ?? 'active') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
    @endif
    @if (($fields['starts_at']['visible'] ?? true))
        <div>
            <label class="erp-label">{{ $fields['starts_at']['label'] ?? __('Starts at') }}</label>
            <input type="date" name="starts_at" class="erp-input w-full" value="{{ old('starts_at', $book?->starts_at?->toDateString()) }}" @required($fields['starts_at']['required'] ?? false) @readonly($fields['starts_at']['read_only'] ?? false)>
        </div>
    @endif
    @if (($fields['ends_at']['visible'] ?? true))
        <div>
            <label class="erp-label">{{ $fields['ends_at']['label'] ?? __('Ends at') }}</label>
            <input type="date" name="ends_at" class="erp-input w-full" value="{{ old('ends_at', $book?->ends_at?->toDateString()) }}" @required($fields['ends_at']['required'] ?? false) @readonly($fields['ends_at']['read_only'] ?? false)>
        </div>
    @endif
    @if (($fields['is_default']['visible'] ?? true))
        <div class="md:col-span-2">
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $book?->is_default)) @disabled($fields['is_default']['read_only'] ?? false)>
                {{ $fields['is_default']['label'] ?? __('Set as default price book for this scope') }}
            </label>
        </div>
    @endif
</div>
@include('admin.partials.form-custom-fields', ['fields' => $fields, 'model' => $book ?? null])
