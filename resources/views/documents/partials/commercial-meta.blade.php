@if (! empty($meta))
    <table class="jp-doc__commercial-meta" cellpadding="0" cellspacing="0">
        @if (! empty($stacked))
            <tr>
                <td class="jp-doc__commercial-meta-col" style="width: 100%; padding-right: 0;">
                    <table cellpadding="0" cellspacing="0" style="width: 100%;">
                        @foreach ($meta as $row)
                            <tr>
                                <td class="jp-doc__commercial-meta-label">{{ $row['label'] }}</td>
                                <td class="jp-doc__commercial-meta-value">{{ $row['value'] }}</td>
                            </tr>
                        @endforeach
                    </table>
                </td>
            </tr>
        @else
            <tr>
                @foreach (array_chunk($meta, (int) ceil(count($meta) / 2)) as $chunk)
                    <td class="jp-doc__commercial-meta-col">
                        <table cellpadding="0" cellspacing="0" style="width: 100%;">
                            @foreach ($chunk as $row)
                                <tr>
                                    <td class="jp-doc__commercial-meta-label">{{ $row['label'] }}</td>
                                    <td class="jp-doc__commercial-meta-value">{{ $row['value'] }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </td>
                @endforeach
            </tr>
        @endif
    </table>
@endif
