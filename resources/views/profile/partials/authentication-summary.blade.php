@php
    use App\Enums\UserSessionStatus;
@endphp

<section>
    <x-admin.form-section
        :title="__('Authentication')"
        :description="__('How this account is identified and signed in.')"
    >
        <div class="md:col-span-2 grid gap-3 sm:grid-cols-2">
            <div class="rounded-lg border border-erp-border bg-slate-50 px-4 py-3">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Account status') }}</p>
                <p class="mt-1 text-sm font-semibold text-erp-primary">
                    {{ $user->is_active ? __('Active') : __('Inactive') }}
                </p>
            </div>
            <div class="rounded-lg border border-erp-border bg-slate-50 px-4 py-3">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Email verification') }}</p>
                <p class="mt-1 text-sm font-semibold text-erp-primary">
                    @if ($user->email_verified_at)
                        {{ __('Verified') }}
                        <span class="block text-xs font-normal text-slate-500">{{ $user->email_verified_at->format('M j, Y g:i A') }}</span>
                    @else
                        {{ __('Not verified') }}
                    @endif
                </p>
            </div>
            <div class="rounded-lg border border-erp-border bg-slate-50 px-4 py-3">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Company') }}</p>
                <p class="mt-1 text-sm font-semibold text-erp-primary">{{ $user->company?->name ?? '—' }}</p>
            </div>
            <div class="rounded-lg border border-erp-border bg-slate-50 px-4 py-3">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Default branch') }}</p>
                <p class="mt-1 text-sm font-semibold text-erp-primary">{{ $user->defaultBranch?->name ?? '—' }}</p>
            </div>
            <div class="rounded-lg border border-erp-border bg-slate-50 px-4 py-3 sm:col-span-2">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Linked employee') }}</p>
                <p class="mt-1 text-sm font-semibold text-erp-primary">
                    @if ($user->employee)
                        {{ $user->employee->full_name }}
                        <span class="font-normal text-slate-500">({{ $user->employee->employee_number }})</span>
                    @else
                        —
                    @endif
                </p>
            </div>
        </div>
    </x-admin.form-section>
</section>
