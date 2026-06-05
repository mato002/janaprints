<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Leave Requests') }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h1>{{ __('Leave Requests') }}</h1>
    <p>{{ __('Generated') }}: {{ $generatedAt->format('Y-m-d H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>{{ __('Reference') }}</th>
                <th>{{ __('Employee') }}</th>
                <th>{{ __('Type') }}</th>
                <th>{{ __('Start') }}</th>
                <th>{{ __('End') }}</th>
                <th>{{ __('Days') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($requests as $request)
                <tr>
                    <td>{{ $request->reference }}</td>
                    <td>{{ $request->employee?->full_name }}</td>
                    <td>{{ $request->leaveType?->name }}</td>
                    <td>{{ $request->start_date?->format('Y-m-d') }}</td>
                    <td>{{ $request->end_date?->format('Y-m-d') }}</td>
                    <td>{{ $request->days_requested }}</td>
                    <td>{{ $request->status?->label() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
