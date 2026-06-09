<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    @include('exports.pdf-styles')
</head>
<body>
    @include('exports.partials.pdf-header', [
        'pdfLogoDataUri' => $pdfLogoDataUri ?? null,
        'pdfCompanyName' => $pdfCompanyName ?? null,
    ])
    <h1>{{ $title }}</h1>
    @if (! empty($subtitle))
        <p class="meta">{{ $subtitle }}</p>
    @endif
    <p class="meta">{{ __('Generated') }}: {{ $generatedAt->format('Y-m-d H:i') }}</p>
    <table>
        @if (! empty($headers))
            <thead>
                <tr>
                    @foreach ($headers as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ max(count($headers), 1) }}">{{ __('No rows to export.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
