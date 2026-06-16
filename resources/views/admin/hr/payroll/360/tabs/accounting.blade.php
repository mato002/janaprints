<x-admin.card>
    @if ($accounting['posted'])
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ __('Posted to accounting on :date by :user.', [
                'date' => $accounting['posted_at']?->format('M j, Y H:i'),
                'user' => $accounting['posted_by'] ?? __('System'),
            ]) }}
        </div>

        <dl class="grid gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-slate-500">{{ __('Journal reference') }}</dt>
                <dd class="font-medium">{{ $accounting['journal']?->reference ?? $accounting['journal']?->id }}</dd>
            </div>
            <div>
                <dt class="text-xs text-slate-500">{{ __('Net salary payable') }}</dt>
                <dd class="font-medium tabular-nums">{{ number_format($accounting['net_total'], 2) }}</dd>
            </div>
        </dl>
    @else
        <x-admin.empty-state
            :title="__('Not posted yet')"
            :description="__('Approve the payroll run, then post it to create the accounting journal entry.')"
        />

        @if ($run->status === App\Enums\PayrollRunStatus::Approved)
            @can('approve', $run)
                <form method="POST" action="{{ route('admin.hr.payroll.post', $run) }}" class="mt-4">
                    @csrf
                    <button type="submit" class="erp-btn-primary">{{ __('Post to accounting') }}</button>
                </form>
            @endcan
        @endif
    @endif
</x-admin.card>

<x-admin.card class="mt-4">
    <h3 class="mb-3 text-sm font-semibold text-erp-primary">{{ __('Posting breakdown') }}</h3>
    <dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ([
            [__('Gross expense'), $accounting['gross_total']],
            [__('Employer NSSF expense'), $accounting['employer_nssf_total'] ?? 0],
            [__('Employer SHIF expense'), $accounting['employer_shif_total'] ?? 0],
            [__('Employer housing expense'), $accounting['employer_housing_levy_total'] ?? 0],
            [__('PAYE payable'), $accounting['paye_total']],
            [__('SHIF payable'), $accounting['shif_total']],
            [__('NSSF payable'), $accounting['nssf_total']],
            [__('Housing levy payable'), $accounting['housing_levy_total']],
            [__('Net payable'), $accounting['net_total']],
        ] as [$label, $value])
            <div>
                <dt class="text-xs text-slate-500">{{ $label }}</dt>
                <dd class="font-medium tabular-nums">{{ number_format($value, 2) }}</dd>
            </div>
        @endforeach
    </dl>
</x-admin.card>
