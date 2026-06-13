<table class="jp-doc__parties" cellpadding="0" cellspacing="0">
    <tr>
        <td class="jp-doc__parties-bill">
            <p class="jp-doc__section-title">{{ $customerLabel ?? __('Bill To') }}</p>
            @if (! empty($customer['company']))
                <p class="jp-doc__party-name">{{ $customer['company'] }}</p>
            @endif
            @if (! empty($customer['name']))
                <p class="jp-doc__party-line">{{ $customer['name'] }}</p>
            @endif
            @if (! empty($customer['code']))
                <p class="jp-doc__party-line">{{ __('Code') }}: {{ $customer['code'] }}</p>
            @endif
            @if (! empty($customer['phone']))
                <p class="jp-doc__party-line">{{ $customer['phone'] }}</p>
            @endif
            @if (! empty($customer['email']))
                <p class="jp-doc__party-line">{{ $customer['email'] }}</p>
            @endif
            @if (! empty($customer['address']))
                <p class="jp-doc__party-line">{{ $customer['address'] }}</p>
            @endif
            @if (empty($customer['company']) && empty($customer['name']) && empty($customer['phone']) && empty($customer['email']) && empty($customer['address']))
                <p class="jp-doc__party-line">—</p>
            @endif
        </td>
        <td class="jp-doc__parties-dates">
            @if (! empty($dates))
                <table class="jp-doc__dates-table" cellpadding="0" cellspacing="0">
                    @foreach ($dates as $row)
                        <tr>
                            <td class="jp-doc__dates-label">{{ $row['label'] }}</td>
                            <td class="jp-doc__dates-value">{{ $row['value'] }}</td>
                        </tr>
                    @endforeach
                </table>
            @endif
        </td>
    </tr>
</table>
