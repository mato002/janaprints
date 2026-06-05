<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ __('Session :number', ['number' => $session->session_number]) }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111; margin: 24px; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        h2 { font-size: 14px; margin: 16px 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        td, th { padding: 4px 0; border-bottom: 1px solid #e5e7eb; text-align: left; }
        .right { text-align: right; }
        .muted { color: #6b7280; }
    </style>
</head>
<body>
    <h1>{{ __('POS Session Summary') }}</h1>
    <p class="muted">{{ $session->session_number }} · {{ $session->branch?->name }}</p>

    <table>
        <tr><td>{{ __('Cashier') }}</td><td class="right">{{ $session->cashier?->name }}</td></tr>
        <tr><td>{{ __('Terminal') }}</td><td class="right">{{ $session->terminal ?? '—' }}</td></tr>
        <tr><td>{{ __('Opened') }}</td><td class="right">{{ $session->opened_at?->format('Y-m-d H:i') }}</td></tr>
        <tr><td>{{ __('Closed') }}</td><td class="right">{{ $session->closed_at?->format('Y-m-d H:i') ?? '—' }}</td></tr>
        <tr><td>{{ __('Opening float') }}</td><td class="right">{{ number_format($session->opening_float, 2) }}</td></tr>
        <tr><td>{{ __('Status') }}</td><td class="right">{{ ucfirst(str_replace('_', ' ', $session->status->value)) }}</td></tr>
    </table>

    <h2>{{ __('Sales summary') }}</h2>
    <table>
        <tr><td>{{ __('Paid sales') }}</td><td class="right">{{ $metrics['sales_count'] }}</td></tr>
        <tr><td>{{ __('Transactions') }}</td><td class="right">{{ $metrics['transactions_count'] }}</td></tr>
        <tr><td>{{ __('Total sales value') }}</td><td class="right">{{ number_format($metrics['total_sales_value'], 2) }}</td></tr>
        <tr><td>{{ __('Refunds') }}</td><td class="right">{{ $metrics['refunds'] }}</td></tr>
    </table>

    <h2>{{ __('Payment summary') }}</h2>
    <table>
        <tr><td>{{ __('Cash') }}</td><td class="right">{{ number_format($metrics['cash_sales'], 2) }}</td></tr>
        <tr><td>{{ __('M-Pesa') }}</td><td class="right">{{ number_format($metrics['mpesa_sales'], 2) }}</td></tr>
        <tr><td>{{ __('Card') }}</td><td class="right">{{ number_format($metrics['card_sales'], 2) }}</td></tr>
        <tr><td>{{ __('Bank') }}</td><td class="right">{{ number_format($metrics['bank_sales'], 2) }}</td></tr>
        <tr><td><strong>{{ __('Expected total') }}</strong></td><td class="right"><strong>{{ number_format($metrics['expected_total'], 2) }}</strong></td></tr>
    </table>

    <h2>{{ __('Variance') }}</h2>
    <table>
        <tr><td>{{ __('Expected cash') }}</td><td class="right">{{ number_format($session->expected_cash ?? $metrics['expected_closing_cash'], 2) }}</td></tr>
        <tr><td>{{ __('Actual cash') }}</td><td class="right">{{ $session->actual_cash !== null ? number_format($session->actual_cash, 2) : '—' }}</td></tr>
        <tr><td>{{ __('Variance') }}</td><td class="right">{{ $session->variance !== null ? number_format($session->variance, 2) : '—' }}</td></tr>
        <tr><td>{{ __('Tolerance') }}</td><td class="right">{{ number_format($varianceTolerance, 2) }}</td></tr>
    </table>

    @if ($session->varianceApprover)
        <p>{{ __('Approved by :name on :date', ['name' => $session->varianceApprover->name, 'date' => $session->variance_approved_at?->format('Y-m-d H:i')]) }}</p>
    @endif
</body>
</html>
