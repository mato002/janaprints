@if (! empty($allocations))
    @if (! empty($allocations['rows']))
        <table class="jp-doc__items" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    @foreach ($allocations['columns'] ?? [] as $column)
                        <th style="text-align: {{ $column['align'] ?? 'left' }}; @if (! empty($column['width'])) width: {{ $column['width'] }}; @endif">
                            {{ $column['label'] }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($allocations['rows'] as $row)
                    <tr>
                        @foreach ($allocations['columns'] ?? [] as $column)
                            @php
                                $alignClass = ($column['align'] ?? 'left') === 'right' ? 'is-right' : '';
                            @endphp
                            <td class="{{ $alignClass }}">{{ $row[$column['key']] ?? '' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="jp-doc__party-line" style="font-style: italic; margin-top: 2mm;">
            {{ $allocations['emptyMessage'] ?? __('Payment has not been allocated to a specific invoice.') }}
        </p>
    @endif
@endif
