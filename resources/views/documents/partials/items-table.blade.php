<table class="jp-doc__items" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            @foreach ($columns as $column)
                <th style="text-align: {{ $column['align'] ?? 'left' }}; @if (! empty($column['width'])) width: {{ $column['width'] }}; @endif">
                    {{ $column['label'] }}
                </th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse ($items as $item)
            <tr>
                @foreach ($columns as $column)
                    @php
                        $alignClass = ($column['align'] ?? 'left') === 'right' ? 'is-right' : '';
                    @endphp
                    <td class="{{ $alignClass }}">{{ $item[$column['key']] ?? '' }}</td>
                @endforeach
            </tr>
        @empty
            <tr>
                <td colspan="{{ count($columns) }}" class="jp-doc__empty">{{ __('No line items on this document.') }}</td>
            </tr>
        @endforelse
    </tbody>
</table>
