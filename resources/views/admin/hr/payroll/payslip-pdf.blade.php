@php
    use App\Enums\PayrollItemType;

    $allowances = $payslip->items->where('item_type', PayrollItemType::Allowance);
    $statutory = $payslip->items
        ->where('item_type', PayrollItemType::Deduction)
        ->filter(fn ($item) => in_array($item->code, ['PAYE', 'SHIF', 'NSSF', 'HOUSING'], true));
    $otherDeductions = $payslip->items
        ->where('item_type', PayrollItemType::Deduction)
        ->reject(fn ($item) => in_array($item->code, ['PAYE', 'SHIF', 'NSSF', 'HOUSING'], true));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Payslip') }} — {{ $payslip->reference }}</title>
    <style>
        @page {
            margin: 14mm 20mm;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
        }

        body {
            color: #1e293b;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.45;
        }

        .pslip__page {
            margin: 0 auto;
            max-width: 170mm;
            padding: 0 2mm;
            width: 100%;
        }

        .pslip__header {
            width: 100%;
            border-bottom: 2px solid #1e3a6e;
            margin-bottom: 7mm;
            padding-bottom: 5mm;
        }

        .pslip__header td {
            vertical-align: middle;
        }

        .pslip__logo-cell {
            width: 45%;
        }

        .pslip__logo {
            display: block;
            width: 48mm;
            max-width: 48mm;
            height: auto;
            max-height: 20mm;
        }

        .pslip__title-cell {
            width: 55%;
            text-align: right;
        }

        .pslip__title {
            color: #1e3a6e;
            font-size: 20pt;
            font-weight: bold;
            letter-spacing: 0.05em;
            margin: 0;
            text-transform: uppercase;
        }

        .pslip__ref {
            color: #475569;
            font-size: 9.5pt;
            margin: 2mm 0 0;
        }

        .pslip__generated {
            color: #64748b;
            font-size: 8.5pt;
            margin: 1.5mm 0 0;
        }

        .pslip__company-line {
            color: #64748b;
            font-size: 8.5pt;
            margin: 0 0 7mm;
            text-align: center;
        }

        .pslip__meta {
            width: 100%;
            margin-bottom: 7mm;
            border-collapse: collapse;
        }

        .pslip__meta td {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 5mm 4.5mm;
            vertical-align: top;
            width: 50%;
        }

        .pslip__meta-label {
            color: #64748b;
            display: block;
            font-size: 8pt;
            letter-spacing: 0.05em;
            margin-bottom: 2mm;
            text-transform: uppercase;
        }

        .pslip__meta-value {
            color: #0f172a;
            font-size: 10.5pt;
            font-weight: bold;
            margin: 0;
        }

        .pslip__meta-sub {
            color: #475569;
            font-size: 9pt;
            margin: 2mm 0 0;
        }

        .pslip__columns {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10mm;
        }

        .pslip__columns > tbody > tr > td {
            vertical-align: top;
            width: 50%;
            padding: 0;
        }

        .pslip__columns > tbody > tr > td:first-child {
            padding-right: 3mm;
        }

        .pslip__columns > tbody > tr > td:last-child {
            padding-left: 3mm;
        }

        .pslip__section-title {
            background: #1e3a6e;
            color: #fff;
            font-size: 8.5pt;
            font-weight: bold;
            letter-spacing: 0.05em;
            margin: 0 0 3mm;
            padding: 3mm 3.5mm;
            text-transform: uppercase;
        }

        .pslip__table {
            border-collapse: collapse;
            width: 100%;
        }

        .pslip__table th,
        .pslip__table td {
            border: 1px solid #e2e8f0;
            padding: 3.5mm 3mm;
        }

        .pslip__table th {
            background: #f1f5f9;
            color: #334155;
            font-size: 8pt;
            font-weight: bold;
            text-align: left;
            text-transform: uppercase;
        }

        .pslip__table td {
            font-size: 9.5pt;
        }

        .pslip__table td.amount,
        .pslip__table th.amount {
            text-align: right;
            white-space: nowrap;
        }

        .pslip__table tr.total th,
        .pslip__table tr.total td {
            background: #eef2ff;
            font-size: 9.5pt;
            font-weight: bold;
        }

        .pslip__empty {
            color: #94a3b8;
            font-size: 9pt;
            font-style: italic;
            margin: 0;
            padding: 3mm 0;
        }

        .pslip__net {
            background: #1e3a6e;
            border-radius: 2mm;
            color: #fff;
            margin: 8mm auto 0;
            max-width: 100%;
            padding: 6mm 8mm;
            text-align: center;
        }

        .pslip__net-label {
            display: block;
            font-size: 8.5pt;
            letter-spacing: 0.08em;
            margin-bottom: 2mm;
            text-transform: uppercase;
        }

        .pslip__net-value {
            font-size: 14pt;
            font-weight: bold;
            margin: 0;
        }

        .pslip__footer {
            border-top: 1px solid #e2e8f0;
            color: #94a3b8;
            font-size: 7.5pt;
            line-height: 1.5;
            margin-top: 10mm;
            padding-top: 4mm;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="pslip__page">
        <table class="pslip__header" cellpadding="0" cellspacing="0" data-pdf-branding-header>
            <tr>
                <td class="pslip__logo-cell">
                    @if (! empty($logoDataUri))
                        <img src="{{ $logoDataUri }}" alt="{{ $company['name'] ?? $pdfCompanyName ?? config('app.name') }}" class="pslip__logo">
                    @else
                        <strong>{{ $company['name'] ?? $pdfCompanyName ?? config('app.name') }}</strong>
                    @endif
                </td>
                <td class="pslip__title-cell">
                    <h1 class="pslip__title">{{ __('Payslip') }}</h1>
                    <p class="pslip__ref">{{ $payslip->reference }}</p>
                    <p class="pslip__generated">{{ __('Generated') }}: {{ $generatedAt->format('M j, Y') }}</p>
                </td>
            </tr>
        </table>

        @if (! empty($company['address']) || ! empty($company['email']))
            <p class="pslip__company-line">
                {{ collect([$company['address'] ?? null, $company['phone'] ?? null, $company['email'] ?? null])->filter()->implode(' · ') }}
            </p>
        @endif

        <table class="pslip__meta" cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    <span class="pslip__meta-label">{{ __('Employee') }}</span>
                    <p class="pslip__meta-value">{{ $payslip->employee?->full_name }}</p>
                    <p class="pslip__meta-sub">
                        {{ $payslip->employee?->employee_number }}
                        @if ($payslip->employee?->department?->name)
                            · {{ $payslip->employee->department->name }}
                        @endif
                    </p>
                </td>
                <td>
                    <span class="pslip__meta-label">{{ __('Payroll period') }}</span>
                    <p class="pslip__meta-value">
                        {{ $payslip->payrollRun?->period_start?->format('M j, Y') }}
                        –
                        {{ $payslip->payrollRun?->period_end?->format('M j, Y') }}
                    </p>
                    <p class="pslip__meta-sub">
                        {{ __('Basic salary') }}: KES {{ number_format((float) $payslip->basic_salary, 2) }}
                    </p>
                </td>
            </tr>
        </table>

        <table class="pslip__columns" cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    <div class="pslip__section">
                        <p class="pslip__section-title">{{ __('Earnings') }}</p>
                        <table class="pslip__table">
                            <thead>
                                <tr>
                                    <th>{{ __('Description') }}</th>
                                    <th class="amount">{{ __('Amount (KES)') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($allowances as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td class="amount">{{ number_format((float) $item->amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2"><p class="pslip__empty">{{ __('No allowance lines recorded.') }}</p></td>
                                    </tr>
                                @endforelse
                                <tr class="total">
                                    <th>{{ __('Gross pay') }}</th>
                                    <th class="amount">{{ number_format((float) $payslip->gross_pay, 2) }}</th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </td>
                <td>
                    <div class="pslip__section">
                        <p class="pslip__section-title">{{ __('Deductions') }}</p>
                        <table class="pslip__table">
                            <thead>
                                <tr>
                                    <th>{{ __('Description') }}</th>
                                    <th class="amount">{{ __('Amount (KES)') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($statutory as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td class="amount">{{ number_format((float) $item->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                                @foreach ($otherDeductions as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td class="amount">{{ number_format((float) $item->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                                @if ($statutory->isEmpty() && $otherDeductions->isEmpty())
                                    <tr>
                                        <td colspan="2"><p class="pslip__empty">{{ __('No deduction lines recorded.') }}</p></td>
                                    </tr>
                                @endif
                                <tr class="total">
                                    <th>{{ __('Total deductions') }}</th>
                                    <th class="amount">{{ number_format((float) $payslip->total_deductions, 2) }}</th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <div class="pslip__net">
            <span class="pslip__net-label">{{ __('Net pay') }}</span>
            <p class="pslip__net-value">KES {{ number_format((float) $payslip->net_pay, 2) }}</p>
        </div>

        <p class="pslip__footer">
            {{ __('This payslip is computer-generated and is intended for the named employee only. Please retain for your records.') }}
        </p>
    </div>
</body>
</html>
