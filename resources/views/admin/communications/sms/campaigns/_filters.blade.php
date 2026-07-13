@php
    $filters = old('recipient_filters', $campaign?->recipient_filters ?? []);
    $enumLabel = fn ($case) => str_replace('_', ' ', ucfirst($case->value));
@endphp

<div
    class="rounded-lg border border-erp-border p-3 space-y-2"
    x-show="['customers', 'dynamic', 'leads', 'employees', 'suppliers'].includes(recipientSource)"
    x-cloak
>
    <div class="flex flex-wrap items-start justify-between gap-2">
        <div>
            <p class="text-xs font-semibold text-slate-500">{{ __('Dynamic filters') }}</p>
            <p class="text-xs text-slate-500" x-show="recipientSource === 'dynamic'">
                {{ __('Audience is built only from these filters (hand-picked names are ignored).') }}
            </p>
            <p class="text-xs text-slate-500" x-show="recipientSource === 'customers'">
                {{ __('Applied when no people are hand-picked on the left.') }}
            </p>
            <p class="text-xs text-slate-500" x-show="recipientSource === 'leads'">
                {{ __('Narrow which leads receive this campaign.') }}
            </p>
            <p class="text-xs text-slate-500" x-show="recipientSource === 'employees'">
                {{ __('Narrow which employees receive this campaign.') }}
            </p>
            <p class="text-xs text-slate-500" x-show="recipientSource === 'suppliers'">
                {{ __('Narrow which suppliers receive this campaign.') }}
            </p>
        </div>
        <button type="button" class="erp-btn erp-btn--ghost erp-btn--sm" @click="estimateAudience()" :disabled="estimatingAudience">
            <span x-show="!estimatingAudience">{{ __('Estimate audience') }}</span>
            <span x-show="estimatingAudience" x-cloak>{{ __('Counting…') }}</span>
        </button>
    </div>

    <p class="rounded bg-slate-50 px-2 py-1.5 text-xs text-slate-600" x-show="audienceEstimate !== null" x-cloak>
        <span class="font-semibold text-erp-primary" x-text="audienceEstimate"></span>
        {{ __('people match the current source and filters.') }}
    </p>

    <div class="grid grid-cols-2 gap-2">
        <div x-show="['customers', 'dynamic', 'leads'].includes(recipientSource)" x-cloak>
            <label class="erp-label text-xs">{{ __('Branch') }}</label>
            <select
                class="erp-input w-full"
                x-model="filters.branch_id"
                :name="['customers', 'dynamic', 'leads'].includes(recipientSource) ? 'recipient_filters[branch_id]' : null"
                @change="onFiltersChanged()"
            >
                <option value="">{{ __('All') }}</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>

        <div x-show="['customers', 'dynamic'].includes(recipientSource)" x-cloak>
            <label class="erp-label text-xs">{{ __('Customer type') }}</label>
            <select
                class="erp-input w-full"
                x-model="filters.customer_type"
                :name="['customers', 'dynamic'].includes(recipientSource) ? 'recipient_filters[customer_type]' : null"
                @change="onFiltersChanged()"
            >
                <option value="">{{ __('All') }}</option>
                @foreach (\App\Enums\CustomerType::cases() as $type)
                    <option value="{{ $type->value }}">{{ $enumLabel($type) }}</option>
                @endforeach
            </select>
        </div>
        <div x-show="['customers', 'dynamic'].includes(recipientSource)" x-cloak>
            <label class="erp-label text-xs">{{ __('Customer status') }}</label>
            <select
                class="erp-input w-full"
                x-model="filters.status"
                :name="['customers', 'dynamic'].includes(recipientSource) ? 'recipient_filters[status]' : null"
                @change="onFiltersChanged()"
            >
                <option value="">{{ __('All') }}</option>
                @foreach (\App\Enums\CustomerStatus::cases() as $status)
                    <option value="{{ $status->value }}">{{ $enumLabel($status) }}</option>
                @endforeach
            </select>
        </div>
        <div x-show="['customers', 'dynamic'].includes(recipientSource)" x-cloak>
            <label class="erp-label text-xs">{{ __('Outstanding balance') }}</label>
            <select
                class="erp-input w-full"
                x-model="filters.has_outstanding"
                :name="['customers', 'dynamic'].includes(recipientSource) ? 'recipient_filters[has_outstanding]' : null"
                @change="onFiltersChanged()"
            >
                <option value="">{{ __('Any') }}</option>
                <option value="1">{{ __('Has outstanding') }}</option>
            </select>
        </div>

        <div x-show="recipientSource === 'leads'" x-cloak>
            <label class="erp-label text-xs">{{ __('Lead status') }}</label>
            <select
                class="erp-input w-full"
                x-model="filters.status"
                :name="recipientSource === 'leads' ? 'recipient_filters[status]' : null"
                @change="onFiltersChanged()"
            >
                <option value="">{{ __('All') }}</option>
                @foreach (\App\Enums\LeadStatus::cases() as $status)
                    <option value="{{ $status->value }}">{{ $enumLabel($status) }}</option>
                @endforeach
            </select>
        </div>

        <div x-show="recipientSource === 'employees'" x-cloak>
            <label class="erp-label text-xs">{{ __('Department') }}</label>
            <select
                class="erp-input w-full"
                x-model="filters.department_id"
                :name="recipientSource === 'employees' ? 'recipient_filters[department_id]' : null"
                @change="onFiltersChanged()"
            >
                <option value="">{{ __('All') }}</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div x-show="recipientSource === 'employees'" x-cloak>
            <label class="erp-label text-xs">{{ __('Employment status') }}</label>
            <select
                class="erp-input w-full"
                x-model="filters.employment_status"
                :name="recipientSource === 'employees' ? 'recipient_filters[employment_status]' : null"
                @change="onFiltersChanged()"
            >
                <option value="">{{ __('All') }}</option>
                @foreach (\App\Enums\EmploymentStatus::cases() as $status)
                    <option value="{{ $status->value }}">{{ $enumLabel($status) }}</option>
                @endforeach
            </select>
        </div>

        <div x-show="recipientSource === 'suppliers'" x-cloak>
            <label class="erp-label text-xs">{{ __('Supplier type') }}</label>
            <select
                class="erp-input w-full"
                x-model="filters.vendor_type"
                :name="recipientSource === 'suppliers' ? 'recipient_filters[vendor_type]' : null"
                @change="onFiltersChanged()"
            >
                <option value="">{{ __('All') }}</option>
                @foreach (\App\Enums\VendorType::cases() as $type)
                    <option value="{{ $type->value }}">{{ $enumLabel($type) }}</option>
                @endforeach
            </select>
        </div>
        <div x-show="recipientSource === 'suppliers'" x-cloak>
            <label class="erp-label text-xs">{{ __('Supplier status') }}</label>
            <select
                class="erp-input w-full"
                x-model="filters.status"
                :name="recipientSource === 'suppliers' ? 'recipient_filters[status]' : null"
                @change="onFiltersChanged()"
            >
                <option value="">{{ __('All') }}</option>
                @foreach (\App\Enums\VendorStatus::cases() as $status)
                    <option value="{{ $status->value }}">{{ $enumLabel($status) }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
