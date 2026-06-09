@props(['rows', 'canAction' => false])

<div class="overflow-x-auto exec-table-scroll">
    <table class="exec-table w-full">
        <thead>
            <tr>
                <th>{{ __('Document') }}</th>
                <th>{{ __('Module') }}</th>
                <th>{{ __('Requested By') }}</th>
                <th>{{ __('Branch') }}</th>
                <th>{{ __('Value') }}</th>
                <th>{{ __('Age') }}</th>
                <th>{{ __('Priority') }}</th>
                <th>{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                @php
                    $priorityClass = match ($row['priority'] ?? 'normal') {
                        'critical' => 'exec-approval-priority--critical',
                        'high' => 'exec-approval-priority--high',
                        default => 'exec-approval-priority--normal',
                    };
                @endphp
                <tr>
                    <td>
                        <div class="font-medium">{{ $row['document'] }}</div>
                        <div class="text-xs text-slate-500">{{ $row['document_label'] }}</div>
                    </td>
                    <td>{{ $row['module'] }}</td>
                    <td>{{ $row['requested_by'] }}</td>
                    <td>{{ $row['branch'] }}</td>
                    <td>{{ $row['value_display'] }}</td>
                    <td>{{ $row['age_label'] }}</td>
                    <td>
                        <span class="exec-approval-priority {{ $priorityClass }}">
                            {{ ucfirst($row['priority']) }}
                        </span>
                    </td>
                    <td class="erp-table-actions-col">
                        <div class="flex flex-wrap gap-1">
                            @if (! empty($row['show_url']))
                                <a href="{{ $row['show_url'] }}" data-turbo-frame="erp-main" class="erp-btn-secondary text-xs">{{ __('View') }}</a>
                            @endif
                            @if ($canAction && ($row['can_approve'] ?? false))
                                <form method="POST" action="{{ route('admin.executive.approvals.approve', ['kind' => $row['kind'], 'subjectId' => $row['subject_id']]) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="erp-btn-primary text-xs">{{ __('Approve') }}</button>
                                </form>
                            @endif
                            @if ($canAction && ($row['can_reject'] ?? false))
                                <form method="POST" action="{{ route('admin.executive.approvals.reject', ['kind' => $row['kind'], 'subjectId' => $row['subject_id']]) }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="reason" value="{{ __('Rejected from executive approval queue.') }}">
                                    <button type="submit" class="erp-btn-secondary text-xs text-red-600">{{ __('Reject') }}</button>
                                </form>
                            @endif
                            @if ($canAction && ($row['can_escalate'] ?? false))
                                <form method="POST" action="{{ route('admin.executive.approvals.escalate', ['kind' => $row['kind'], 'subjectId' => $row['subject_id']]) }}" class="inline">
                                    @csrf
                                    @if (! empty($row['chain_run_id']))
                                        <input type="hidden" name="chain_run_id" value="{{ $row['chain_run_id'] }}">
                                    @endif
                                    <button type="submit" class="erp-btn-secondary text-xs">{{ __('Escalate') }}</button>
                                </form>
                            @endif
                            @if ($canAction && ($row['can_delegate'] ?? false))
                                <a href="{{ route('admin.executive.approvals.delegate', ['kind' => $row['kind'], 'subjectId' => $row['subject_id']]) }}" class="erp-btn-secondary text-xs">{{ __('Delegate') }}</a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="py-6 text-center text-slate-500">{{ __('No approvals waiting.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
