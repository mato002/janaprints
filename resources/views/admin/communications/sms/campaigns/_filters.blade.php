<div class="rounded-lg border border-erp-border p-3 space-y-2">
    <p class="text-xs font-semibold text-slate-500">{{ __('Dynamic filters') }}</p>
    <div class="grid grid-cols-2 gap-2">
        <div>
            <label class="erp-label text-xs">{{ __('Branch') }}</label>
            <select name="recipient_filters[branch_id]" class="erp-input w-full">
                <option value="">{{ __('All') }}</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected(old('recipient_filters.branch_id', $campaign?->recipient_filters['branch_id'] ?? null) == $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="erp-label text-xs">{{ __('Customer type') }}</label>
            <select name="recipient_filters[customer_type]" class="erp-input w-full">
                <option value="">{{ __('All') }}</option>
                @foreach (\App\Enums\CustomerType::cases() as $type)
                    <option value="{{ $type->value }}">{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="erp-label text-xs">{{ __('Customer status') }}</label>
            <select name="recipient_filters[status]" class="erp-input w-full">
                <option value="">{{ __('All') }}</option>
                @foreach (\App\Enums\CustomerStatus::cases() as $status)
                    <option value="{{ $status->value }}">{{ $status->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="erp-label text-xs">{{ __('Outstanding balance') }}</label>
            <select name="recipient_filters[has_outstanding]" class="erp-input w-full">
                <option value="">{{ __('Any') }}</option>
                <option value="1">{{ __('Has outstanding') }}</option>
            </select>
        </div>
        <div>
            <label class="erp-label text-xs">{{ __('Department') }}</label>
            <select name="recipient_filters[department_id]" class="erp-input w-full">
                <option value="">{{ __('All') }}</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="erp-label text-xs">{{ __('Supplier type') }}</label>
            <select name="recipient_filters[vendor_type]" class="erp-input w-full">
                <option value="">{{ __('All') }}</option>
                @foreach (\App\Enums\VendorType::cases() as $type)
                    <option value="{{ $type->value }}">{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
