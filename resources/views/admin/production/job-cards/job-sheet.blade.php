<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $sheet['job_number'] }} — {{ __('Job sheet') }}</title>
    <style>
        @page { margin: 10mm; size: A4 portrait; }
        * { box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #0f172a;
            margin: 0;
            padding: 16px;
            font-size: 11px;
            line-height: 1.35;
        }
        .sheet { max-width: 210mm; margin: 0 auto; border: 2px solid #1e3a8a; }
        .sheet__header {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            padding: 12px 14px 8px;
            border-bottom: 2px solid #1e3a8a;
        }
        .brand { font-size: 22px; font-weight: 800; color: #db2777; letter-spacing: 0.02em; }
        .brand small { display: block; font-size: 10px; font-weight: 700; color: #1e3a8a; letter-spacing: 0.08em; }
        .title { font-size: 28px; font-weight: 800; color: #db2777; text-align: right; align-self: center; }
        .contact { grid-column: 1 / -1; font-size: 9px; color: #334155; border-top: 1px solid #cbd5e1; padding-top: 6px; }
        .meta {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            padding: 10px 14px;
            border-bottom: 2px solid #1e3a8a;
        }
        .meta__label { font-weight: 700; color: #1e3a8a; font-size: 10px; text-transform: uppercase; }
        .meta__value { border-bottom: 1px solid #64748b; min-height: 18px; margin-top: 2px; font-size: 12px; }
        .section-title {
            background: #db2777;
            color: #fff;
            font-weight: 700;
            text-align: center;
            padding: 5px 8px;
            font-size: 11px;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #1e3a8a; padding: 5px 6px; vertical-align: top; }
        th { background: #eff6ff; color: #1e3a8a; font-size: 9px; text-transform: uppercase; }
        .ncr-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; }
        .ncr-grid span { display: block; border-top: 1px solid #1e3a8a; padding: 4px 6px; min-height: 22px; }
        .ncr-grid small { display: block; font-size: 8px; font-weight: 700; color: #1e3a8a; }
        .notes {
            min-height: 56px;
            padding: 8px 14px;
            border-bottom: 2px solid #1e3a8a;
            white-space: pre-wrap;
        }
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            padding: 12px 14px;
            border-bottom: 2px solid #1e3a8a;
        }
        .sign-line { border-bottom: 1px solid #64748b; min-height: 22px; margin-top: 18px; }
        .checks {
            display: flex;
            justify-content: space-around;
            padding: 10px 14px 14px;
            font-size: 11px;
            font-weight: 700;
        }
        .check { display: inline-flex; align-items: center; gap: 6px; }
        .box {
            width: 14px;
            height: 14px;
            border: 2px solid #1e3a8a;
            display: inline-block;
            text-align: center;
            line-height: 10px;
            font-size: 10px;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body onload="window.print()">
    <p class="no-print" style="text-align:center;margin-bottom:12px;">
        <button type="button" onclick="window.print()" style="padding:8px 16px;cursor:pointer;">{{ __('Print') }}</button>
    </p>

    <div class="sheet">
        <div class="sheet__header">
            <div>
                <div class="brand">
                    {{ $sheet['company_name'] }}
                    <small>{{ __('Printing & Branding') }}</small>
                </div>
            </div>
            <div class="title">{{ __('Job Sheet') }}</div>
            <div class="contact">
                @if ($sheet['company_address']){{ $sheet['company_address'] }} · @endif
                @if ($sheet['company_phone']){{ __('Tel') }}: {{ $sheet['company_phone'] }} · @endif
                @if ($sheet['company_email']){{ $sheet['company_email'] }}@endif
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
            <div>
                <div class="meta__label">{{ __('Ms') }}</div>
                <div class="meta__value">{{ $sheet['customer_name'] }}</div>
            </div>
        </div>

        <div class="section-title">{{ __('Printing specifications') }}</div>
        <table>
            <thead>
                <tr>
                    <th style="width:8%">{{ __('Qty') }}</th>
                    <th style="width:22%">{{ __('Description') }}</th>
                    <th style="width:24%">{{ __('Paper colour') }}</th>
                    <th style="width:18%">{{ __('Paper stock') }}</th>
                    <th style="width:12%">{{ __('Ink') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sheet['printing_rows'] as $row)
                    <tr>
                        <td>{{ $row['quantity'] }}</td>
                        <td>{{ $row['description'] }}</td>
                        <td>
                            <table style="width:100%;border-collapse:collapse;">
                                <tr>
                                    <td style="border:1px solid #1e3a8a;padding:3px 4px;"><small>{{ __('ORIG') }}</small><br>{{ $row['orig'] }}</td>
                                    <td style="border:1px solid #1e3a8a;padding:3px 4px;"><small>{{ __('DUP') }}</small><br>{{ $row['dup'] }}</td>
                                    <td style="border:1px solid #1e3a8a;padding:3px 4px;"><small>{{ __('TRI') }}</small><br>{{ $row['tri'] }}</td>
                                    <td style="border:1px solid #1e3a8a;padding:3px 4px;"><small>{{ __('QUAD') }}</small><br>{{ $row['quad'] }}</td>
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

        <div class="section-title">{{ __('Note') }}</div>
        <div class="notes">{{ $sheet['notes'] }}</div>

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
                <strong>{{ __('Prepared by') }}</strong>
                <div class="sign-line">{{ $sheet['prepared_by'] }}</div>
                <small>{{ __('Sign') }}</small>
            </div>
            <div>
                <strong>{{ __('Store') }}</strong>
                <div class="sign-line"></div>
                <small>{{ __('Sign') }}</small>
            </div>
        </div>

        <div class="checks">
            <span class="check"><span class="box">@if ($sheet['status']['printed'])✓@endif</span> {{ __('Printed') }}</span>
            <span class="check"><span class="box">@if ($sheet['status']['complete'])✓@endif</span> {{ __('Complete') }}</span>
            <span class="check"><span class="box">@if ($sheet['status']['collected'])✓@endif</span> {{ __('Collected') }}</span>
        </div>
    </div>
</body>
</html>
