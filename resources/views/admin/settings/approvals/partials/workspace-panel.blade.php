@props(['row', 'canManage', 'roles', 'permissions'])

<x-admin.card class="!p-0 overflow-hidden" id="approval-panel-{{ $row['rule_type'] }}">
    <div class="border-b border-erp-border px-5 py-4 sm:px-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                    {{ __('Approvals') }}
                    <span class="mx-1 text-slate-300">/</span>
                    <span class="text-erp-accent">{{ $row['label'] }}</span>
                </p>
                <h2 class="mt-1 text-lg font-semibold text-erp-primary">{{ $row['label'] }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $row['description'] }}</p>
                @if ($row['inherits_company'])
                    <p class="mt-2 text-xs text-amber-700">{{ __('No branch override configured — company default applies.') }}</p>
                @endif
            </div>
            @if ($canManage)
                <label class="inline-flex shrink-0 items-center gap-2 rounded-lg border border-erp-border bg-erp-page/60 px-3 py-2 text-sm">
                    <input type="hidden" name="rules[{{ $row['rule_type'] }}][is_enabled]" value="0">
                    <input
                        type="checkbox"
                        name="rules[{{ $row['rule_type'] }}][is_enabled]"
                        value="1"
                        class="rounded border-erp-border text-erp-accent focus:ring-erp-accent"
                        @checked($row['is_enabled'])
                    >
                    <span class="font-medium text-slate-700">{{ __('Active') }}</span>
                </label>
            @else
                <x-admin.status-badge :variant="$row['is_enabled'] ? 'success' : 'danger'">
                    {{ $row['is_enabled'] ? __('Active') : __('Inactive') }}
                </x-admin.status-badge>
            @endif
        </div>
    </div>

    @if ($canManage)
        <div class="border-b border-erp-border bg-erp-page/40 px-5 py-4 sm:px-6">
            <x-input-label :for="'min_'.$row['rule_type']" :value="__('Minimum approvers')" />
            <input
                type="number"
                id="min_{{ $row['rule_type'] }}"
                name="rules[{{ $row['rule_type'] }}][min_approvers]"
                value="{{ $row['min_approvers'] }}"
                min="1"
                max="10"
                class="erp-input mt-1 w-24"
            >
        </div>
    @elseif ($row['min_approvers'] > 1)
        <div class="border-b border-erp-border px-5 py-3 sm:px-6 text-sm text-slate-600">
            {{ __('Minimum approvers') }}: {{ $row['min_approvers'] }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="erp-table">
            <thead>
                <tr>
                    @if ($row['metric'] === 'amount' || $row['metric'] === 'both')
                        <th class="pl-5 sm:pl-6">{{ __('Amount ≥') }}</th>
                    @endif
                    @if ($row['metric'] === 'percent' || $row['metric'] === 'both')
                        <th>{{ __('Percent ≥') }}</th>
                    @endif
                    <th>{{ __('Required role') }}</th>
                    <th class="pr-5 sm:pr-6">{{ __('Required permission') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-erp-border bg-white">
                @php $tiers = $row['tiers'] !== [] ? $row['tiers'] : ($row['company_tiers'] ?? []); @endphp
                @forelse ($tiers as $index => $tier)
                    <tr class="hover:bg-slate-50/50">
                        @if ($row['metric'] === 'amount' || $row['metric'] === 'both')
                            <td class="py-3 pl-5 sm:pl-6">
                                @if ($canManage)
                                    <input type="number" step="0.01" min="0" name="rules[{{ $row['rule_type'] }}][tiers][{{ $index }}][threshold_amount]" value="{{ $tier['threshold_amount'] ?? '' }}" class="erp-input w-32" placeholder="{{ __('Amount') }}">
                                @else
                                    {{ $tier['threshold_amount'] ?? '—' }}
                                @endif
                            </td>
                        @endif
                        @if ($row['metric'] === 'percent' || $row['metric'] === 'both')
                            <td class="py-3">
                                @if ($canManage)
                                    <input type="number" step="0.01" min="0" max="100" name="rules[{{ $row['rule_type'] }}][tiers][{{ $index }}][threshold_percent]" value="{{ $tier['threshold_percent'] ?? '' }}" class="erp-input w-24" placeholder="%">
                                @else
                                    {{ isset($tier['threshold_percent']) ? $tier['threshold_percent'].'%' : '—' }}
                                @endif
                            </td>
                        @endif
                        <td class="py-3">
                            @if ($canManage)
                                <select name="rules[{{ $row['rule_type'] }}][tiers][{{ $index }}][approver_role]" class="erp-select w-full min-w-[10rem]">
                                    <option value="">{{ __('Any') }}</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role }}" @selected(($tier['approver_role'] ?? '') === $role)>{{ $role }}</option>
                                    @endforeach
                                </select>
                            @else
                                {{ $tier['approver_role'] ?? '—' }}
                            @endif
                        </td>
                        <td class="py-3 pr-5 sm:pr-6">
                            @if ($canManage)
                                <select name="rules[{{ $row['rule_type'] }}][tiers][{{ $index }}][approver_permission]" class="erp-select w-full min-w-[12rem]">
                                    <option value="">{{ $row['default_permission'] ?? __('None') }}</option>
                                    @foreach ($permissions as $permission)
                                        <option value="{{ $permission }}" @selected(($tier['approver_permission'] ?? $row['default_permission']) === $permission)>{{ $permission }}</option>
                                    @endforeach
                                </select>
                            @else
                                {{ $tier['approver_permission'] ?? $row['default_permission'] ?? '—' }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-4 pl-5 text-slate-500 sm:pl-6">{{ __('No tiers configured.') }}</td>
                    </tr>
                @endforelse

                @if ($canManage)
                    @for ($i = count($tiers); $i < count($tiers) + 3; $i++)
                        <tr class="bg-erp-page/40">
                            @if ($row['metric'] === 'amount' || $row['metric'] === 'both')
                                <td class="py-3 pl-5 sm:pl-6">
                                    <input type="number" step="0.01" min="0" name="rules[{{ $row['rule_type'] }}][tiers][{{ $i }}][threshold_amount]" class="erp-input w-32" placeholder="{{ collect($row['example_tiers'])->get($i - count($tiers)) }}">
                                </td>
                            @endif
                            @if ($row['metric'] === 'percent' || $row['metric'] === 'both')
                                <td class="py-3">
                                    <input type="number" step="0.01" min="0" max="100" name="rules[{{ $row['rule_type'] }}][tiers][{{ $i }}][threshold_percent]" class="erp-input w-24" placeholder="{{ collect($row['example_tiers'])->get($i - count($tiers)) }}">
                                </td>
                            @endif
                            <td class="py-3">
                                <select name="rules[{{ $row['rule_type'] }}][tiers][{{ $i }}][approver_role]" class="erp-select w-full min-w-[10rem]">
                                    <option value="">{{ __('Any') }}</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role }}">{{ $role }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="py-3 pr-5 sm:pr-6">
                                <select name="rules[{{ $row['rule_type'] }}][tiers][{{ $i }}][approver_permission]" class="erp-select w-full min-w-[12rem]">
                                    <option value="">{{ $row['default_permission'] ?? __('None') }}</option>
                                    @foreach ($permissions as $permission)
                                        <option value="{{ $permission }}">{{ $permission }}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    @endfor
                @endif
            </tbody>
        </table>
    </div>
</x-admin.card>
