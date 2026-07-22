@php
    $operatorMode = (bool) ($operatorMode ?? false);
@endphp

<x-admin.card :padding="false">
    <div class="border-b border-erp-border px-4 py-3">
        <h2 class="text-sm font-semibold text-erp-primary">{{ __('My Work Queue') }}</h2>
        <p class="text-xs text-slate-500">{{ __('Assigned to you — select a job to work inline.') }}</p>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table w-full text-sm">
            <thead>
                <tr>
                    <th>{{ __('Request #') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Priority') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Due Date') }}</th>
                    <th>{{ __('Version') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr
                        @class([
                            'cursor-pointer transition-colors hover:bg-violet-50/60',
                            'bg-violet-50 ring-1 ring-inset ring-violet-200' => false,
                            'bg-amber-50/70' => $row['is_late'],
                            'bg-blue-50/50' => $row['is_due_today'] && ! $row['is_late'],
                        ])
                        :class="{ 'bg-violet-50 ring-1 ring-inset ring-violet-200': selectedKey === @js($row['key']) }"
                        @click="selectRequest(@js($row['key']))"
                        data-urgency-due-today="{{ $row['is_due_today'] ? '1' : '0' }}"
                        data-urgency-overdue="{{ $row['is_late'] ? '1' : '0' }}"
                        data-urgency-waiting="{{ $row['is_waiting'] ? '1' : '0' }}"
                        data-urgency-new="{{ $row['status'] === 'requested' ? '1' : '0' }}"
                        x-show="rowVisible($el)"
                    >
                        <td class="font-mono text-xs font-semibold text-erp-accent">{{ $row['request_number'] }}</td>
                        <td>{{ $row['customer'] ?? '—' }}</td>
                        <td class="font-medium">{{ $row['title'] }}</td>
                        <td>
                            @php
                                $priorityColors = [
                                    'low' => 'bg-slate-100 text-slate-700',
                                    'normal' => 'bg-blue-100 text-blue-700',
                                    'high' => 'bg-amber-100 text-amber-700',
                                    'urgent' => 'bg-rose-100 text-rose-700',
                                ];
                            @endphp
                            <span @class([
                                'inline-flex rounded-full px-2 py-0.5 text-xs font-medium',
                                $priorityColors[$row['priority']] ?? 'bg-slate-100 text-slate-700',
                            ])>{{ $row['priority_label'] }}</span>
                        </td>
                        <td>
                            @php
                                $statusColors = [
                                    'requested' => 'bg-slate-100 text-slate-700',
                                    'in_design' => 'bg-blue-100 text-blue-700',
                                    'submitted' => 'bg-indigo-100 text-indigo-700',
                                    'approved' => 'bg-emerald-100 text-emerald-700',
                                    'revision_requested' => 'bg-amber-100 text-amber-700',
                                    'rejected' => 'bg-rose-100 text-rose-700',
                                ];
                            @endphp
                            <span @class([
                                'inline-flex rounded-full px-2 py-0.5 text-xs font-medium',
                                $statusColors[$row['status']] ?? 'bg-slate-100 text-slate-700',
                            ])>{{ $row['status_label'] }}</span>
                        </td>
                        <td @class(['text-xs', 'font-semibold text-amber-800' => $row['is_late']])>{{ $row['due_date'] ?? '—' }}</td>
                        <td class="text-center">{{ $row['version'] }}</td>
                        <td @click.stop>
                            <div class="flex flex-wrap items-center gap-1">
                                <button
                                    type="button"
                                    class="erp-btn-primary px-2 py-1 text-xs"
                                    @click="selectRequest(@js($row['key']))"
                                >
                                    {{ $row['is_editable'] ? __('Work') : __('Open') }}
                                </button>
                                @if ($row['is_editable'])
                                    <button
                                        type="button"
                                        class="erp-btn-secondary px-2 py-1 text-xs"
                                        @click="selectRequest(@js($row['key']), 'designer-desk-files')"
                                    >
                                        {{ __('Upload') }}
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-6 text-center text-sm text-slate-500">{{ __('Queue empty — see activity below.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin.card>
