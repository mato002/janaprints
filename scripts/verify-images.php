<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$samples = [
    config('facility.pipeline.1.image'),
    config('why-us.features.0.image'),
    config('capabilities.items.0.image'),
    config('portfolio.featured.2.image'),
];

foreach ($samples as $url) {
    $ok = is_string($url) && str_starts_with($url, 'https://');
    echo ($ok ? 'OK' : 'BAD') . " {$url}\n";
}
