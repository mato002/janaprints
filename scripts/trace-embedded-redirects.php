<?php

/**
 * Trace redirect chains for embedded workspace URLs.
 *
 * Usage: php scripts/trace-embedded-redirects.php
 */

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$company = Company::query()->where('code', 'JANA')->first();
$user = User::query()->where('company_id', $company?->id)->first();

if (! $user) {
    fwrite(STDERR, "No user.\n");
    exit(1);
}

auth()->login($user);

function traceUri(Kernel $kernel, string $uri, array $headers = []): void
{
    $chain = [];
    $current = $uri;
    $maxHops = 8;

    for ($hop = 0; $hop < $maxHops; $hop++) {
        $request = Request::create($current, 'GET');
        $request->headers->replace($headers);

        $response = $kernel->handle($request);
        $status = $response->getStatusCode();
        $location = $response->headers->get('Location');
        $bodyLen = strlen($response->getContent() ?: '');
        $hasFrame = str_contains($response->getContent() ?: '', 'turbo-frame id="module-workspace-content"');

        $chain[] = sprintf('  %3d  %6d B  frame=%s  %s', $status, $bodyLen, $hasFrame ? 'yes' : 'no ', $current);

        $kernel->terminate($request, $response);

        if ($status < 300 || $status >= 400 || ! $location) {
            break;
        }

        $current = str_starts_with($location, 'http')
            ? parse_url($location, PHP_URL_PATH).(parse_url($location, PHP_URL_QUERY) ? '?'.parse_url($location, PHP_URL_QUERY) : '')
            : $location;
    }

    echo str_repeat('-', 88)."\n";
    echo "URI: {$uri}\n";
    echo "Headers: ".json_encode($headers)."\n";
    foreach ($chain as $line) {
        echo $line."\n";
    }
    echo 'Hops: '.count($chain)." | Redirects: ".max(0, count($chain) - 1)."\n\n";
}

$headers = ['Turbo-Frame' => 'module-workspace-content'];

$uris = [
    '/admin/dispatch?embedded=1',
    '/admin/production/scheduling?embedded=1',
    '/admin/production/quality?embedded=1',
    '/admin/crm/customers?embedded=1',
    '/admin/settings/forms?embedded=1',
    '/admin/workspaces/dispatch?embedded=1',
    '/admin/settings/hub?embedded=1',
];

echo "Embedded redirect trace (with Turbo-Frame header)\n";

foreach ($uris as $uri) {
    traceUri($kernel, $uri, $headers);
}

echo "Embedded redirect trace (embedded=1 only, NO Turbo-Frame header)\n";

foreach ($uris as $uri) {
    traceUri($kernel, $uri, []);
}
