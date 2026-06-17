<x-admin.card>
    <h3 class="mb-4 text-sm font-semibold text-erp-primary">{{ __('Payroll scope certification') }}</h3>
    <p class="mb-4 text-sm text-slate-600">{{ __('Read-only certification of who is included in this payroll run and why others are excluded.') }}</p>

    <dl class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Payroll group') }}</dt>
            <dd class="mt-1 text-sm font-medium text-slate-900">{{ $scope['certification']['payroll_group_label'] ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Included') }}</dt>
            <dd class="mt-1 text-sm font-medium text-slate-900">{{ $scope['certification']['included_count'] ?? 0 }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Excluded') }}</dt>
            <dd class="mt-1 text-sm font-medium text-slate-900">{{ $scope['certification']['excluded_count'] ?? 0 }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Frozen integrity') }}</dt>
            <dd class="mt-1 text-sm font-medium {{ ($scope['frozen_intact'] ?? true) ? 'text-emerald-700' : 'text-amber-700' }}">
                @if ($run->frozen_snapshot)
                    {{ ($scope['frozen_intact'] ?? true) ? __('Intact') : __('Changed since approval freeze') }}
                @else
                    {{ __('Not frozen yet') }}
                @endif
            </dd>
        </div>
    </dl>

    @if (($scope['integrity']['warnings'] ?? []) !== [])
        <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <p class="font-medium">{{ __('Setup warnings before generation') }}</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($scope['integrity']['warnings'] as $warning)
                    <li>{{ $warning['message'] ?? '' }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <div>
            <h4 class="mb-3 text-sm font-semibold text-slate-800">{{ __('Employees included') }}</h4>
            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="erp-table erp-table--compact min-w-full text-sm">
                    <thead>
                        <tr>
                            <th>{{ __('Employee') }}</th>
                            <th>{{ __('Group') }}</th>
                            <th class="text-right">{{ __('Basic') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($scope['certification']['included'] ?? [] as $row)
                            <tr>
                                <td>{{ $row['employee_number'] }} — {{ $row['employee_name'] }}</td>
                                <td>{{ $row['payroll_group_label'] ?? '—' }}</td>
                                <td class="text-right tabular-nums">{{ $row['basic_salary'] !== null ? number_format($row['basic_salary'], 2) : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-slate-500">{{ __('No eligible employees for this payroll group.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            <h4 class="mb-3 text-sm font-semibold text-slate-800">{{ __('Employees excluded') }}</h4>
            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="erp-table erp-table--compact min-w-full text-sm">
                    <thead>
                        <tr>
                            <th>{{ __('Employee') }}</th>
                            <th>{{ __('Reason') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($scope['certification']['excluded'] ?? [] as $row)
                            <tr>
                                <td>{{ $row['employee_number'] }} — {{ $row['employee_name'] }}</td>
                                <td>{{ $row['exclusion_label'] ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-slate-500">{{ __('No excluded employees in this branch scope.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin.card>
