<?php

/**
 * Benchmark workspace shell HTML payload sizes across core modules.
 *
 * Usage: php scripts/benchmark-workspace-navigation.php
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

if (! \Illuminate\Support\Facades\Schema::hasTable('users')) {
    fwrite(STDERR, "Database not ready. Run migrations and seeders first.\n");
    exit(1);
}

$company = Company::query()->where('code', 'JANA')->first();
$branch = Branch::query()->where('company_id', $company?->id)->where('code', 'HQ')->first();

if (! $company || ! $branch) {
    fwrite(STDERR, "Organization seed data missing.\n");
    exit(1);
}

$user = User::query()->where('company_id', $company->id)->first();

if (! $user) {
    fwrite(STDERR, "No user found for benchmark.\n");
    exit(1);
}

auth()->login($user);

function benchRequest(Kernel $kernel, string $uri, array $headers = []): array
{
    $request = Request::create($uri, 'GET');
    $request->headers->replace($headers);

    $start = microtime(true);
    $response = $kernel->handle($request);
    $elapsed = (microtime(true) - $start) * 1000;
    $body = $response->getContent() ?: '';
    $kernel->terminate($request, $response);

    return [
        'status' => $response->getStatusCode(),
        'bytes' => strlen($body),
        'ms' => round($elapsed, 2),
        'redirect' => $response->isRedirection() ? $response->headers->get('Location') : null,
    ];
}

function countDomNodes(string $html): int
{
    libxml_use_internal_errors(true);
    $dom = new DOMDocument;
    $dom->loadHTML($html);
    libxml_clear_errors();

    return $dom->getElementsByTagName('*')->length;
}

$moduleShells = [
    'commercial' => '/admin/workspaces/commercial/crm',
    'production' => '/admin/workspaces/production/operations',
    'supply-chain' => '/admin/workspaces/supply-chain/catalogue',
    'administration' => '/admin/workspaces/administration/configuration',
];

$embeddedSamples = [
    'commercial' => '/admin/crm/customers?embedded=1',
    'production' => '/admin/production/job-cards?embedded=1',
    'supply-chain' => '/admin/inventory/items?embedded=1',
    'administration' => '/admin/settings/forms?embedded=1',
];

echo "Workspace shell payload benchmark (P0A)\n";
echo str_repeat('-', 88)."\n";
printf("%-16s %8s %10s %12s %8s\n", 'Module', 'Shell', 'Embedded', 'DOM nodes', 'Status');
echo str_repeat('-', 88)."\n";

$totals = ['shell' => 0, 'embedded' => 0, 'count' => 0];

foreach ($moduleShells as $module => $shellUri) {
    $shell = benchRequest($kernel, $shellUri);
    $embedded = benchRequest(
        $kernel,
        $embeddedSamples[$module],
        ['Turbo-Frame' => 'module-workspace-content'],
    );

    $html = '';
    if ($shell['status'] === 200) {
        $request = Request::create($shellUri, 'GET');
        $response = $kernel->handle($request);
        $html = $response->getContent() ?: '';
        $kernel->terminate($request, $response);
    }

    $nodes = $html !== '' ? countDomNodes($html) : 0;

    printf(
        "%-16s %8d %10d %12d %8d\n",
        $module,
        $shell['bytes'],
        $embedded['bytes'],
        $nodes,
        $shell['status'],
    );

    if ($shell['status'] === 200) {
        $totals['shell'] += $shell['bytes'];
        $totals['embedded'] += $embedded['bytes'];
        $totals['count']++;
    }
}

echo str_repeat('-', 88)."\n";

if ($totals['count'] > 0) {
    $avgShell = (int) round($totals['shell'] / $totals['count']);
    $avgEmbedded = (int) round($totals['embedded'] / $totals['count']);
    $savings = round((1 - $avgEmbedded / $avgShell) * 100, 1);

    echo "Average shell: {$avgShell} bytes | Average embedded content: {$avgEmbedded} bytes ({$savings}% smaller)\n";
}

echo "\nNavigation checks\n";
echo str_repeat('-', 88)."\n";

$checks = [
    'direct_feature_redirect' => benchRequest($kernel, '/admin/crm/customers'),
    'desk_tab_url' => benchRequest($kernel, '/admin/workspaces/commercial/crm?tab=customers'),
];

foreach ($checks as $label => $result) {
    $redirect = $result['redirect'] ? " → {$result['redirect']}" : '';
    echo sprintf(
        "%-28s status=%d  %6d bytes  %6.2f ms%s\n",
        $label,
        $result['status'],
        $result['bytes'],
        $result['ms'],
        $redirect,
    );
}

echo str_repeat('-', 88)."\n";
echo "Target: workspace shell < 100 KB (preferred < 60 KB)\n";
echo "Discovery search: ".route('admin.feature-discovery.search')."\n";
