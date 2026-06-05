<x-admin-layout :title="$exit->reference" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Exit Management'), 'url' => route('admin.hr.exit.dashboard')], ['label' => $exit->reference]]">
    <x-admin.page-header :title="$exit->reference" :description="$exit->employee->full_name.' · '.$exit->exit_type->label()">
        <x-slot name="actions">
            <a href="{{ route('admin.hr.exit.index') }}" class="erp-btn-secondary">{{ __('Back') }}</a>
            @can('update', $exit)
                @if ($exit->status->value === 'clearance_complete')
                    <form method="POST" action="{{ route('admin.hr.exit.settle', $exit) }}">
                        @csrf
                        <button type="submit" class="erp-btn-primary">{{ __('Settle final dues') }}</button>
                    </form>
                @endif
                @if ($exit->status->value === 'settled')
                    <form method="POST" action="{{ route('admin.hr.exit.close', $exit) }}" onsubmit="return confirm(@js(__('Close exit and deactivate employee?')))">
                        @csrf
                        <button type="submit" class="erp-btn-secondary">{{ __('Close exit') }}</button>
                    </form>
                @endif
            @endcan
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    <div class="grid gap-4 lg:grid-cols-3">
        <x-admin.card class="lg:col-span-1" :title="__('Exit Summary')">
            <dl class="space-y-3 text-sm">
                <div><dt class="text-slate-500">{{ __('Employee') }}</dt><dd>{{ $exit->employee->full_name }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Status') }}</dt><dd>{{ $exit->status->label() }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Last Working Date') }}</dt><dd>{{ $exit->last_working_date->format('Y-m-d') }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Exit Date') }}</dt><dd>{{ $exit->exit_date->format('Y-m-d') }}</dd></div>
                @php $progress = $exit->clearanceProgress() @endphp
                <div><dt class="text-slate-500">{{ __('Clearance') }}</dt><dd>{{ $progress['done'] }} / {{ $progress['total'] }}</dd></div>
            </dl>
        </x-admin.card>

        <x-admin.card class="lg:col-span-2" :title="__('Final Dues')">
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ([
                    ['label' => __('Leave Balance (days)'), 'value' => number_format($exit->leave_balance_days, 1)],
                    ['label' => __('Leave Payout'), 'value' => number_format($exit->leave_balance_amount, 2)],
                    ['label' => __('Salary Balance'), 'value' => number_format($exit->salary_balance, 2)],
                    ['label' => __('Deductions'), 'value' => number_format($exit->deductions_total, 2)],
                    ['label' => __('Net Final Dues'), 'value' => number_format($exit->net_final_dues, 2)],
                ] as $item)
                    <div class="rounded-lg border border-slate-200 p-3">
                        <p class="text-xs uppercase tracking-wide text-slate-500">{{ $item['label'] }}</p>
                        <p class="mt-1 text-lg font-semibold">{{ $item['value'] }}</p>
                    </div>
                @endforeach
            </div>
        </x-admin.card>
    </div>

    <x-admin.card class="mt-4" :title="__('Clearance Checklist')">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-left text-slate-500">
                        <th class="py-2 pr-3">{{ __('Department') }}</th>
                        <th class="py-2 pr-3">{{ __('Status') }}</th>
                        <th class="py-2 pr-3">{{ __('Cleared By') }}</th>
                        <th class="py-2 pr-3">{{ __('Date') }}</th>
                        @can('update', $exit)
                            @if (! in_array($exit->status->value, ['settled', 'closed']))
                                <th>{{ __('Action') }}</th>
                            @endif
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @foreach ($exit->clearances as $clearance)
                        <tr class="border-b border-slate-100">
                            <td class="py-2 pr-3 font-medium">{{ $clearance->category->label() }}</td>
                            <td class="py-2 pr-3">{{ $clearance->status->label() }}</td>
                            <td class="py-2 pr-3">{{ $clearance->clearedBy?->name ?? '—' }}</td>
                            <td class="py-2 pr-3">{{ $clearance->cleared_at?->format('Y-m-d') ?? '—' }}</td>
                            @can('update', $exit)
                                @if (! in_array($exit->status->value, ['settled', 'closed']) && $clearance->status->value === 'pending')
                                    <td class="py-2">
                                        <form method="POST" action="{{ route('admin.hr.exit.clearance', [$exit, $clearance]) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="cleared">
                                            <button type="submit" class="erp-btn-secondary text-xs">{{ __('Clear') }}</button>
                                        </form>
                                    </td>
                                @elseif (! in_array($exit->status->value, ['settled', 'closed']))
                                    <td></td>
                                @endif
                            @endcan
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-admin.card>

    @if ($exit->reason || $exit->notes)
        <x-admin.card class="mt-4" :title="__('Notes')">
            @if ($exit->reason)<p class="text-sm"><strong>{{ __('Reason') }}:</strong> {{ $exit->reason }}</p>@endif
            @if ($exit->notes)<p class="mt-2 text-sm"><strong>{{ __('Notes') }}:</strong> {{ $exit->notes }}</p>@endif
        </x-admin.card>
    @endif
</x-admin-layout>
