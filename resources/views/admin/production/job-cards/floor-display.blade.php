<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
  @if ($refreshSeconds ?? null)
    <meta http-equiv="refresh" content="{{ $refreshSeconds }}">
  @endif
    <title>{{ $sheet['job_number'] }} — {{ __('Job sheet') }}</title>
    @include('admin.production.job-cards.partials.job-sheet-styles')
</head>
<body>
    <div class="no-print job-sheet-toolbar">
        <button type="button" onclick="window.print()">{{ __('Print') }}</button>
    </div>

    @include('admin.production.job-cards.partials.job-sheet-body', ['sheet' => $sheet])
</body>
</html>
