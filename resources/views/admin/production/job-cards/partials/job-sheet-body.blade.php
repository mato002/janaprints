<div class="sheet">
    <div class="sheet__header">
        <div class="brand-wrap">
            <img src="{{ asset('images/logo-sidebar.png') }}" alt="" class="brand-logo">
            <div class="brand">
                {{ $sheet['company_name'] }}
                <small>{{ __('Printing & Branding') }}</small>
            </div>
        </div>
        <div class="title">{{ __('Job Sheet') }}</div>
        <div class="contact">
            @if ($sheet['company_address']){{ $sheet['company_address'] }}<br>@endif
            @if ($sheet['company_phone']){{ __('Tel') }}: {{ $sheet['company_phone'] }}@endif
            @if ($sheet['company_phone'] && $sheet['company_email']) · @endif
            @if ($sheet['company_email']){{ __('Email') }}: {{ $sheet['company_email'] }}@endif
        </div>
    </div>

    <div class="meta">
        <div>
            <div class="meta__label">{{ __('No.') }}</div>
            <div class="meta__value">{{ $sheet['job_number'] }}</div>
        </div>
        <div>
            <div class="meta__label">{{ __('Date') }}</div>
            <div class="meta__value">{{ $sheet['date'] }}</div>
        </div>
        <div class="meta__customer">
            <div class="meta__label">{{ __('Ms') }}</div>
            <div class="meta__value">{{ $sheet['customer_name'] }}</div>
        </div>
    </div>

    <div class="section-title">{{ __('Printing specifications') }}</div>
    <table>
        <thead>
            <tr>
                <th style="width:8%">{{ __('Qty') }}</th>
                <th style="width:24%">{{ __('Description') }}</th>
                <th style="width:28%">{{ __('Paper colour') }}</th>
                <th style="width:20%">{{ __('Paper stock') }}</th>
                <th style="width:12%">{{ __('Ink') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sheet['printing_rows'] as $row)
                <tr>
                    <td>{{ $row['quantity'] }}</td>
                    <td>{{ $row['description'] }}</td>
                    <td class="ncr-cell">
                        <table class="ncr-inner">
                            <tr>
                                <td><small>{{ __('ORIG') }}</small>{{ $row['orig'] }}</td>
                                <td><small>{{ __('DUP') }}</small>{{ $row['dup'] }}</td>
                                <td><small>{{ __('TRI') }}</small>{{ $row['tri'] }}</td>
                                <td><small>{{ __('QUAD') }}</small>{{ $row['quad'] }}</td>
                            </tr>
                        </table>
                    </td>
                    <td>{{ $row['paper_stock'] }}</td>
                    <td>{{ $row['ink'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">{{ __('Binding specifications') }}</div>
    <table>
        <thead>
            <tr>
                <th>{{ __('Number') }}</th>
                <th>{{ __('Pages / pad') }}</th>
                <th>{{ __('Size') }}</th>
                <th>{{ __('No. of ups') }}</th>
                <th>{{ __('Binding') }}</th>
                <th>{{ __('Date of collection') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $sheet['binding']['serial_start'] }}</td>
                <td>{{ $sheet['binding']['pages_per_pad'] }}</td>
                <td>{{ $sheet['binding']['size'] }}</td>
                <td>{{ $sheet['binding']['ups'] }}</td>
                <td>{{ $sheet['binding']['binding'] }}</td>
                <td>{{ $sheet['binding']['collection_date'] }}</td>
            </tr>
        </tbody>
    </table>

    <div class="notes">
        <div class="notes__label">{{ __('Note') }}:-</div>
        {{ $sheet['notes'] }}
    </div>

    <div class="section-title">{{ __('Material requisition') }}</div>
    <table>
        <thead>
            <tr>
                <th>{{ __('Paper type') }}</th>
                <th>{{ __('No. of sheets A4 / A3') }}</th>
                <th>{{ __('No. of sheets A1') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sheet['material_rows'] as $row)
                <tr>
                    <td>{{ $row['paper_type'] }}</td>
                    <td>{{ $row['sheets_a4_a3'] }}</td>
                    <td>{{ $row['sheets_a1'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signatures">
        <div>
            <strong>{{ __('Prepared by') }}:</strong>
            <div class="sign-line">{{ $sheet['prepared_by'] }}</div>
            <small>{{ __('Sign') }}:</small>
        </div>
        <div>
            <strong>{{ __('Store') }}:</strong>
            <div class="sign-line"></div>
            <small>{{ __('Sign') }}:</small>
        </div>
    </div>

    <div class="checks">
        <span class="check"><span class="box">@if ($sheet['status']['printed'])✓@endif</span> {{ __('Printed') }}</span>
        <span class="check"><span class="box">@if ($sheet['status']['complete'])✓@endif</span> {{ __('Complete') }}</span>
        <span class="check"><span class="box">@if ($sheet['status']['collected'])✓@endif</span> {{ __('Collected') }}</span>
    </div>
</div>
