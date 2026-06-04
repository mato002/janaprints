<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $asset->asset_number }}</title>
    <style>
        body { font-family: system-ui, sans-serif; padding: 24px; }
        .label { border: 1px solid #000; padding: 16px; width: 280px; text-align: center; }
        .code { font-size: 28px; font-weight: 700; letter-spacing: 2px; margin: 8px 0; }
        .name { font-size: 14px; }
    </style>
</head>
<body onload="window.print()">
    <div class="label">
        <div class="name">{{ $asset->asset_name }}</div>
        <div class="code">{{ $asset->barcode ?? $asset->asset_number }}</div>
        <div class="name">{{ $asset->category?->name }}</div>
    </div>
</body>
</html>
