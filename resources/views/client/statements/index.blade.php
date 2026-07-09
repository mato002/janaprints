<x-layouts.client :title="__('Statements')" :heading="__('Account statements')">
    <div class="client-detail">
        <form method="get" action="{{ route('client.statements.index') }}" class="client-form-grid mb-6">
            <div>
                <label for="from_date" class="client-label">{{ __('From date') }}</label>
                <input type="date" id="from_date" name="from_date" value="{{ $fromDate }}" class="client-input" required>
            </div>
            <div>
                <label for="to_date" class="client-label">{{ __('To date') }}</label>
                <input type="date" id="to_date" name="to_date" value="{{ $toDate }}" class="client-input" required>
            </div>
            <div class="client-form-actions">
                <button type="submit" name="preview" value="1" class="client-button client-button--secondary">{{ __('Preview') }}</button>
                <a href="{{ route('client.statements.download', ['from_date' => $fromDate, 'to_date' => $toDate, 'format' => 'pdf']) }}" class="client-button" data-turbo="false">{{ __('Download PDF') }}</a>
            </div>
        </form>

        @if ($report)
            <div class="client-detail__meta mb-4">
                <p><strong>{{ __('Opening balance') }}:</strong> KES {{ number_format((float) $report['opening_balance'], 2) }}</p>
                <p><strong>{{ __('Closing balance') }}:</strong> KES {{ number_format((float) $report['closing_balance'], 2) }}</p>
            </div>

            <div class="client-table-wrap">
                <table class="client-table">
                    <thead>
                        <tr>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Reference') }}</th>
                            <th>{{ __('Description') }}</th>
                            <th>{{ __('Debit') }}</th>
                            <th>{{ __('Credit') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($report['entries'] as $entry)
                            <tr>
                                <td>{{ $entry->date }}</td>
                                <td>{{ $entry->type }}</td>
                                <td>{{ $entry->reference }}</td>
                                <td>{{ $entry->description }}</td>
                                <td>{{ $entry->debit > 0 ? number_format($entry->debit, 2) : '' }}</td>
                                <td>{{ $entry->credit > 0 ? number_format($entry->credit, 2) : '' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="client-empty">{{ __('No transactions in this period.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            @include('client.partials.empty-state', [
                'icon' => 'document',
                'message' => __('Choose a date range and preview your account statement.'),
            ])
        @endif
    </div>
</x-layouts.client>
