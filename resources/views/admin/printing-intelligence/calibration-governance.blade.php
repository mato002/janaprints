<x-admin-layout :title="__('Cost Accuracy Governance')" :breadcrumbs="[
    ['label' => __('Printing Intelligence'), 'url' => route('admin.printing-intelligence.overview')],
    ['label' => __('Cost Accuracy Governance')],
]">
    <x-admin.page-header
        :title="__('Cost Accuracy Governance')"
        :description="__('Controlled calibration governance — recommendations require human review and approval. No automatic formula changes.')"
    >
        @can('printing.calibration.manage')
            <form method="POST" action="{{ route('admin.printing-intelligence.calibration.generate') }}">@csrf
                <button type="submit" class="erp-btn-secondary">{{ __('Generate recommendations') }}</button>
            </form>
        @endcan
    </x-admin.page-header>

    @include('admin.printing-intelligence.partials.nav')

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-4">{{ session('status') }}</x-admin.alert>
    @endif

    @php
        $tabs = [
            'overview' => __('Overview'),
            'recommendations' => __('Recommendations'),
            'pending' => __('Pending Approvals'),
            'active' => __('Active Rules'),
            'history' => __('Rule History'),
            'simulation' => __('Impact Simulation'),
        ];
    @endphp

    <x-admin.card class="mb-4">
        <nav class="flex flex-wrap gap-2">
            @foreach ($tabs as $key => $label)
                <a href="{{ route('admin.printing-intelligence.calibration-governance', ['tab' => $key]) }}"
                   @class([
                       'rounded-md px-3 py-1.5 text-xs font-medium',
                       'bg-slate-900 text-white' => ($tab ?? 'overview') === $key,
                       'bg-slate-100 text-slate-700 hover:bg-slate-200' => ($tab ?? 'overview') !== $key,
                   ])>{{ $label }}</a>
            @endforeach
        </nav>
    </x-admin.card>

    @if (($tab ?? 'overview') === 'overview')
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 mb-6">
            <x-admin.kpi-widget :label="__('Average accuracy')" :value="$analytics['average_accuracy_score'] !== null ? number_format($analytics['average_accuracy_score'], 1).'%' : '—'" icon="chart-bar" />
            <x-admin.kpi-widget :label="__('Awaiting review')" :value="$pending->count()" icon="clock" />
            <x-admin.kpi-widget :label="__('Active rules')" :value="$active->count()" icon="check-circle" />
            <x-admin.kpi-widget :label="__('Top variance driver')" :value="$analytics['most_underestimated_category'] ? ucfirst($analytics['most_underestimated_category']) : '—'" icon="exclamation" />
        </div>
        <x-admin.card>
            <p class="text-sm text-slate-600">{{ __('Governance only — approved rules update the active costing profile without rewriting historical estimates or mutating inventory, accounting, or quotations.') }}</p>
            <dl class="mt-4 grid grid-cols-2 gap-3 text-sm md:grid-cols-5">
                @foreach ($formulaVersions as $prefix => $version)
                    <div><dt class="text-xs text-slate-500">{{ $prefix }}</dt><dd class="font-medium">{{ $version }}</dd></div>
                @endforeach
            </dl>
        </x-admin.card>
    @endif

    @if (($tab ?? 'overview') === 'recommendations')
        <x-admin.card>
            <table class="erp-table text-sm">
                <thead><tr><th>{{ __('Type') }}</th><th>{{ __('Key') }}</th><th>{{ __('Current') }}</th><th>{{ __('Proposed') }}</th><th>{{ __('Status') }}</th><th>{{ __('Reason') }}</th><th></th></tr></thead>
                <tbody>
                    @forelse ($recommendations as $rule)
                        <tr>
                            <td>{{ $rule->rule_type?->label() }}</td>
                            <td>{{ $rule->rule_key }}</td>
                            <td>{{ $rule->current_value }}</td>
                            <td>{{ $rule->proposed_value }}</td>
                            <td><span class="erp-badge">{{ $rule->status?->label() }}</span></td>
                            <td class="max-w-xs truncate">{{ $rule->reason }}</td>
                            <td>
                                @can('printing.calibration.manage')
                                    @if ($rule->status?->value === 'draft')
                                        <form method="POST" action="{{ route('admin.printing-intelligence.calibration.submit', $rule) }}">@csrf
                                            <button class="text-sky-700 hover:underline">{{ __('Submit') }}</button>
                                        </form>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-slate-500 py-6">{{ __('No recommendations yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-admin.card>
    @endif

    @if (($tab ?? 'overview') === 'pending')
        <x-admin.card>
            @forelse ($pending as $rule)
                <div class="mb-4 rounded-md border border-slate-200 p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h4 class="font-medium">{{ $rule->rule_type?->label() }} — {{ $rule->rule_key }}</h4>
                            <p class="text-sm text-slate-600 mt-1">{{ $rule->reason }}</p>
                            <p class="text-xs text-slate-500 mt-2">{{ __('Current') }}: {{ $rule->current_value }} → {{ __('Proposed') }}: {{ $rule->proposed_value }} ({{ $rule->rule_version }})</p>
                        </div>
                        <div class="flex gap-2">
                            @can('printing.calibration.approve')
                                <form method="POST" action="{{ route('admin.printing-intelligence.calibration.approve', $rule) }}">@csrf
                                    <button class="erp-btn-primary">{{ __('Approve') }}</button>
                                </form>
                            @endcan
                            @can('printing.calibration.review')
                                <form method="POST" action="{{ route('admin.printing-intelligence.calibration.reject', $rule) }}">@csrf
                                    <button class="erp-btn-secondary">{{ __('Reject') }}</button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-slate-500">{{ __('No rules pending approval.') }}</p>
            @endforelse
        </x-admin.card>
    @endif

    @if (($tab ?? 'overview') === 'active')
        <x-admin.card>
            <table class="erp-table text-sm">
                <thead><tr><th>{{ __('Type') }}</th><th>{{ __('Key') }}</th><th>{{ __('Value') }}</th><th>{{ __('Version') }}</th><th>{{ __('Effective from') }}</th></tr></thead>
                <tbody>
                    @forelse ($active as $rule)
                        <tr>
                            <td>{{ $rule->rule_type?->label() }}</td>
                            <td>{{ $rule->rule_key }}</td>
                            <td>{{ $rule->proposed_value }}</td>
                            <td>{{ $rule->rule_version }}</td>
                            <td>{{ $rule->effective_from?->format('Y-m-d H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-slate-500 py-6">{{ __('No active calibration rules.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-admin.card>
    @endif

    @if (($tab ?? 'overview') === 'history')
        <x-admin.card>
            <table class="erp-table text-sm">
                <thead><tr><th>{{ __('Recorded') }}</th><th>{{ __('Before') }}</th><th>{{ __('After') }}</th><th>{{ __('Version') }}</th><th>{{ __('Reason') }}</th></tr></thead>
                <tbody>
                    @forelse ($history as $entry)
                        <tr>
                            <td>{{ $entry->recorded_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $entry->before_value }}</td>
                            <td>{{ $entry->after_value }}</td>
                            <td>{{ $entry->rule_version }}</td>
                            <td class="max-w-sm truncate">{{ $entry->reason }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-slate-500 py-6">{{ __('No calibration history yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-admin.card>
    @endif

    @if (($tab ?? 'overview') === 'simulation')
        <x-admin.card>
            @if ($simulation)
                <h3 class="font-medium mb-3">{{ __('Impact simulation') }} @if($simulationRule) — {{ $simulationRule->rule_key }} @endif</h3>
                <dl class="grid grid-cols-2 gap-3 text-sm md:grid-cols-4">
                    <div><dt class="text-xs text-slate-500">{{ __('Sample size') }}</dt><dd>{{ $simulation['sample_size'] }}</dd></div>
                    <div><dt class="text-xs text-slate-500">{{ __('Accuracy before') }}</dt><dd>{{ $simulation['average_accuracy_before'] !== null ? number_format($simulation['average_accuracy_before'], 1).'%' : '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">{{ __('Accuracy after') }}</dt><dd>{{ $simulation['average_accuracy_after'] !== null ? number_format($simulation['average_accuracy_after'], 1).'%' : '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">{{ __('Expected improvement') }}</dt><dd>{{ $simulation['expected_improvement'] !== null ? number_format($simulation['expected_improvement'], 1).'%' : '—' }}</dd></div>
                </dl>
                <p class="mt-3 text-xs text-amber-700">{{ __('Advisory simulation only — does not modify estimates or activate rules.') }}</p>
            @else
                <p class="text-slate-500">{{ __('Select or generate a recommendation to simulate impact.') }}</p>
            @endif
        </x-admin.card>
    @endif
</x-admin-layout>
