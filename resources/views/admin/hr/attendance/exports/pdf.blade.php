<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Attendance Register') }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .meta { color: #555; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h1>{{ __('Attendance Register') }}</h1>
    <p class="meta">{{ __('Generated') }}: {{ $generatedAt->format('Y-m-d H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Employee') }}</th>
                <th>{{ __('Employee Number') }}</th>
                <th>{{ __('Department') }}</th>
                <th>{{ __('Branch') }}</th>
                <th>{{ __('Shift') }}</th>
                <th>{{ __('Clock In') }}</th>
                <th>{{ __('Clock Out') }}</th>
                <th>{{ __('Hours') }}</th>
                <th>{{ __('Overtime') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $record)
                <tr>
                    <td>{{ $record->attendance_date?->format('Y-m-d') }}</td>
                    <td>{{ $record->employee?->full_name }}</td>
                    <td>{{ $record->employee?->employee_number }}</td>
                    <td>{{ $record->department?->name }}</td>
                    <td>{{ $record->branch?->name }}</td>
                    <td>{{ $record->shift?->name }}</td>
                    <td>{{ $record->clock_in_at?->format('H:i') }}</td>
                    <td>{{ $record->clock_out_at?->format('H:i') }}</td>
                    <td>{{ $record->actual_hours }}</td>
                    <td>{{ $record->overtime_hours }}</td>
                    <td>{{ $record->status?->label() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
