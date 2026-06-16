<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Payslip') }} — {{ $payslip->reference }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111; max-width: 720px; margin: 0 auto; padding: 24px; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        .meta { color: #555; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f5f5f5; }
        .totals { margin-top: 16px; }
        .net { font-size: 16px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>{{ config('app.name', 'Jana Prints') }}</h1>
    <p class="meta">{{ __('Payslip') }} · {{ $payslip->reference }} · {{ $generatedAt->format('Y-m-d') }}</p>

    <p><strong>{{ $payslip->employee?->full_name }}</strong><br>
    {{ $payslip->employee?->employee_number }} · {{ $payslip->employee?->department?->name }}</p>

    <p>{{ __('Payroll period') }}: {{ $payslip->payrollRun?->period_start?->format('M j, Y') }} – {{ $payslip->payrollRun?->period_end?->format('M j, Y') }}</p>
    <p>{{ __('Basic salary') }}: KES {{ number_format($payslip->basic_salary, 2) }}</p>

    <h3>{{ __('Earnings') }}</h3>
    <table>
        <tbody>
            @foreach ($payslip->items->where('item_type', App\Enums\PayrollItemType::Allowance) as $item)
                <tr><td>{{ $item->name }}</td><td style="text-align:right">{{ number_format($item->amount, 2) }}</td></tr>
            @endforeach
            <tr><th>{{ __('Gross pay') }}</th><th style="text-align:right">{{ number_format($payslip->gross_pay, 2) }}</th></tr>
        </tbody>
    </table>

    <h3>{{ __('Statutories') }}</h3>
    <table>
        <tbody>
            @foreach ($payslip->items->where('item_type', App\Enums\PayrollItemType::Deduction)->filter(fn ($i) => in_array($i->code, ['PAYE', 'SHIF', 'NSSF', 'HOUSING'], true)) as $item)
                <tr><td>{{ $item->name }}</td><td style="text-align:right">{{ number_format($item->amount, 2) }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <h3>{{ __('Other deductions') }}</h3>
    <table>
        <tbody>
            @foreach ($payslip->items->where('item_type', App\Enums\PayrollItemType::Deduction)->reject(fn ($i) => in_array($i->code, ['PAYE', 'SHIF', 'NSSF', 'HOUSING'], true)) as $item)
                <tr><td>{{ $item->name }}</td><td style="text-align:right">{{ number_format($item->amount, 2) }}</td></tr>
            @endforeach
            <tr><th>{{ __('Total deductions') }}</th><th style="text-align:right">{{ number_format($payslip->total_deductions, 2) }}</th></tr>
        </tbody>
    </table>

    <p class="totals net">{{ __('Net pay') }}: KES {{ number_format($payslip->net_pay, 2) }}</p>
</body>
</html>
