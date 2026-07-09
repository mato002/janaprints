<div class="erp-card overflow-x-auto">
    <table class="erp-table w-full text-sm">
        <thead>
            <tr>
                <th>{{ __('Job') }}</th>
                <th>{{ __('Customer') }}</th>
                <th>{{ __('Product') }}</th>
                <th>{{ __('Stage') }}</th>
                <th>{{ __('Machine') }}</th>
                <th>{{ __('Vendor') }}</th>
                <th>{{ __('Due') }}</th>
                <th>{{ __('Priority') }}</th>
                <th class="erp-table-actions-col">{{ __('Next step') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr
                    class="cursor-pointer hover:bg-slate-50 {{ $row['is_overdue'] ? 'bg-amber-50/60' : '' }}"
                    @click="openPanel(@js($row['public_id']))"
                >
                    <td class="font-mono text-xs whitespace-nowrap">
                        <button type="button" class="text-erp-accent hover:underline" @click.stop="openPanel(@js($row['public_id']))">
                            {{ $row['job_number'] }}
                        </button>
                    </td>
                    <td>{{ $row['customer'] ?? '—' }}</td>
                    <td>
                        <span>{{ $row['product'] ?? '—' }}</span>
                        @if ($row['sku'])
                            <span class="block text-[11px] text-slate-500">{{ $row['sku'] }}</span>
                        @endif
                    </td>
                    <td>
                        <span class="rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide
                            @if ($row['stage'] === 'at_vendor') bg-violet-100 text-violet-800
                            @elseif ($row['is_overdue']) bg-amber-100 text-amber-900
                            @else bg-slate-100 text-slate-700 @endif">
                            {{ $row['stage_label'] }}
                        </span>
                    </td>
                    <td @click.stop>
                        @can('machines.assign')
                            <form method="POST" action="{{ route('admin.production.floor.assign-machine', $row['public_id']) }}" class="min-w-[9rem]">
                                @csrf
                                @foreach ($filters as $key => $value)
                                    @if ($value !== '' && $value !== null)
                                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                    @endif
                                @endforeach
                                <select
                                    name="assigned_machine_asset_id"
                                    class="erp-select w-full text-xs"
                                    onchange="this.form.submit()"
                                >
                                    <option value="">{{ __('Unassigned') }}</option>
                                    @foreach ($filter_options['machines'] as $machine)
                                        <option value="{{ $machine['value'] }}" @selected((string) $row['machine_id'] === $machine['value'])>
                                            {{ $machine['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        @else
                            {{ $row['machine'] ?? '—' }}
                        @endcan
                    </td>
                    <td class="text-xs">
                        @if ($row['vendor'])
                            <span class="font-medium">{{ $row['vendor'] }}</span>
                            @if ($row['vendor_expected_return'])
                                <span class="block text-slate-500">{{ __('Return') }}: {{ $row['vendor_expected_return'] }}</span>
                            @endif
                        @else
                            —
                        @endif
                    </td>
                    <td class="whitespace-nowrap text-xs {{ $row['is_overdue'] ? 'font-semibold text-amber-800' : '' }}">
                        {{ $row['required_date'] ?? '—' }}
                    </td>
                    <td class="text-xs capitalize">{{ $row['priority_label'] }}</td>
                    <td class="erp-table-actions-col" @click.stop>
                        @if ($row['primary_action'])
                            @php $action = $row['primary_action']; @endphp
                            @if ($action['type'] === 'post')
                                <form method="POST" action="{{ $action['url'] }}">
                                    @csrf
                                    <button type="submit" class="erp-btn-primary text-xs py-1 px-2">{{ $action['label'] }}</button>
                                </form>
                            @elseif ($action['type'] === 'panel')
                                <button type="button" class="erp-btn-primary text-xs py-1 px-2" @click="openPanel(@js($row['public_id']))">
                                    {{ $action['label'] }}
                                </button>
                            @else
                                <a href="{{ $action['url'] }}" class="erp-btn-secondary text-xs py-1 px-2" data-turbo-frame="erp-main">{{ $action['label'] }}</a>
                            @endif
                        @else
                            <button type="button" class="erp-btn-secondary text-xs py-1 px-2" @click="openPanel(@js($row['public_id']))">{{ __('Open') }}</button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="py-10 text-center text-slate-500">
                        {{ __('No jobs match the current filters.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
