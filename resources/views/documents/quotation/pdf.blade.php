<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $document['title'] }} {{ $document['documentNumber'] }}</title>
    @include('documents.partials.styles')
    @include('documents.partials.pdf-styles')
</head>
<body class="jp-doc jp-doc--pdf">
    <div class="jp-doc__pdf-body">
        @include('documents.quotation.content', ['document' => $document])
    </div>
</body>
</html>
