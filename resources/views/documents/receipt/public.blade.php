<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $document['title'] }} {{ $document['documentNumber'] }}</title>
    @include('documents.partials.styles')
    <style>
        body { background: #f8fafc; margin: 0; padding: 24px; }
        .jp-doc-public-wrap { background: #fff; border: 1px solid #e2e8f0; margin: 0 auto; max-width: 820px; padding: 24px; }
    </style>
</head>
<body>
    <div class="jp-doc-public-wrap jp-doc">
        @include('documents.receipt.content', ['document' => $document])
    </div>
</body>
</html>
