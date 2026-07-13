<x-admin-layout :title="$budget->name" :breadcrumbs="[['label' => __('Budgets'), 'url' => route('admin.accounting.budgets.index')], ['label' => $budget->name]]">
    <x-admin.page-header :title="$budget->name" :description="$budget->from_date->format('Y-m-d').' → '.$budget->to_date->format('Y-m-d').' · '.$budget->status->label()">
        <x-slot name="actions">
            <a href="{{ route('admin.accounting.budgets.vs-actual', $budget) }}" class="erp-btn-secondary">{{ __('Budget vs actual') }}</a>
            @can('accounting.budgets.manage')
                @if ($budget->status === App\Enums\BudgetStatus::Draft)
                    <form method="POST" action="{{ route('admin.accounting.budgets.activate', $budget) }}">
                        @csrf
                        <button type="submit" class="erp-btn-primary">{{ __('Activate') }}</button>
                    </form>
                @endif
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.card>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-slate-500 border-b border-erp-border">
                    <th class="py-2">{{ __('Account') }}</th>
                    <th class="py-2">{{ __('Period') }}</th>
                    <th class="py-2 text-right">{{ __('Amount') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($budget->lines as $line)
                    <tr class="border-t border-erp-border">
                        <td class="py-2 font-mono text-xs">{{ $line->glAccount?->code }} — {{ $line->glAccount?->name }}</td>
                        <td class="py-2">{{ $line->period_month ?: __('Full period') }}</td>
                        <td class="py-2 text-right">{{ number_format((float) $line->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="py-4 text-slate-500">{{ __('No budget lines.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-admin.card>
</x-admin-layout>
