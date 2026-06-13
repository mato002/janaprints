@if (! empty($meta))
    <table class="jp-doc__meta-table" cellpadding="0" cellspacing="0" style="width: 100%; margin-bottom: 4mm;">
        @foreach ($meta as $row)
            <tr>
                <td class="jp-doc__meta-label">{{ $row['label'] }}</td>
                <td>{{ $row['value'] }}</td>
            </tr>
        @endforeach
    </table>
@endif
