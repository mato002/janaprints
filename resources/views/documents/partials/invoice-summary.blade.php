@if (! empty($summary))
    <div class="jp-doc__summary">
        <p class="jp-doc__summary-title">{{ $summary['title'] ?? __('Invoice Summary') }}</p>

        @if (! empty($summary['overdueDays']))
            <p class="jp-doc__summary-overdue">
                @include('documents.partials.status-badge', [
                    'status' => [
                        'label' => __(':days days overdue', ['days' => $summary['overdueDays']]),
                        'variant' => 'danger',
                    ],
                ])
            </p>
        @endif

        <table class="jp-doc__summary-table" cellpadding="0" cellspacing="0">
            @foreach ($summary['rows'] ?? [] as $row)
                <tr class="{{ ! empty($row['emphasis']) ? 'is-emphasis' : '' }}">
                    <td class="label">{{ $row['label'] }}</td>
                    <td class="value">
                        @if (! empty($row['badge']))
                            @include('documents.partials.status-badge', ['status' => $row['badge']])
                        @else
                            {{ $row['value'] }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
@endif
