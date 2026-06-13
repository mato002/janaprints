<table class="jp-doc__totals" cellpadding="0" cellspacing="0">
    @foreach ($totals as $row)
        <tr class="{{ ! empty($row['highlight']) ? 'is-highlight' : '' }} {{ ! empty($row['balanceBar']) ? 'is-balance-bar' : '' }}">
            <td class="label">{{ $row['label'] }}</td>
            <td class="value">{{ $row['value'] }}</td>
        </tr>
    @endforeach
</table>
