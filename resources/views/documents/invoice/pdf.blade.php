<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $document['title'] }} {{ $document['documentNumber'] }}</title>
    @include('documents.partials.styles')
</head>
<body class="jp-doc" style="margin: 15mm 12mm 18mm 12mm;">
    @include('documents.invoice.content', ['document' => $document])
</body>
</html>
