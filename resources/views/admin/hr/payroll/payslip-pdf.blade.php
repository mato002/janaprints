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

    <p>{{ __('Period') }}: {{ $payslip->payrollRun?->period_start?->format('M j, Y') }} – {{ $payslip->payrollRun?->period_end?->format('M j, Y') }}</p>
    <p>{{ __('Days worked') }}: {{ $payslip->days_worked }} · {{ __('Leave days') }}: {{ $payslip->leave_days }} · {{ __('Absent') }}: {{ $payslip->absent_days }}</p>

    <h3>{{ __('Earnings') }}</h3>
    <table>
        <tbody>
            @foreach ($payslip->items->where('item_type', App\Enums\PayrollItemType::Allowance) as $item)
                <tr><td>{{ $item->name }}</td><td style="text-align:right">{{ number_format($item->amount, 2) }}</td></tr>
            @endforeach
            <tr><th>{{ __('Gross Pay') }}</th><th style="text-align:right">{{ number_format($payslip->gross_pay, 2) }}</th></tr>
        </tbody>
    </table>

    <h3>{{ __('Deductions') }}</h3>
    <table>
        <tbody>
            @foreach ($payslip->items->where('item_type', App\Enums\PayrollItemType::Deduction) as $item)
                <tr><td>{{ $item->name }}</td><td style="text-align:right">{{ number_format($item->amount, 2) }}</td></tr>
            @endforeach
            <tr><th>{{ __('Total Deductions') }}</th><th style="text-align:right">{{ number_format($payslip->total_deductions, 2) }}</th></tr>
        </tbody>
    </table>

    <p class="totals net">{{ __('Net Pay') }}: KES {{ number_format($payslip->net_pay, 2) }}</p>
</body>
</html>
