<?php



/**

 * Analyze workspace shell HTML payload composition.

 *

 * Usage: php scripts/analyze-shell-payload.php

 */



use App\Models\Branch;

use App\Models\Company;

use App\Models\User;

use App\Support\Discovery\FeatureRegistry;

use Illuminate\Contracts\Http\Kernel;

use Illuminate\Http\Request;



require __DIR__.'/../vendor/autoload.php';



$app = require __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();



$company = Company::query()->where('code', 'JANA')->first();

$branch = Branch::query()->where('company_id', $company?->id)->where('code', 'HQ')->first();

$user = User::query()->where('company_id', $company->id)->first();



if (! $company || ! $branch || ! $user) {

    fwrite(STDERR, "Seed data missing.\n");

    exit(1);

}



auth()->login($user);



function fetchHtml(Kernel $kernel, string $uri): string

{

    $request = Request::create($uri, 'GET');

    $response = $kernel->handle($request);

    $body = $response->getContent() ?: '';

    $kernel->terminate($request, $response);



    return $body;

}



function countDomNodes(string $html): int

{

    libxml_use_internal_errors(true);

    $dom = new DOMDocument;

    $dom->loadHTML($html);

    libxml_clear_errors();



    return $dom->getElementsByTagName('*')->length;

}



function inlinePayloadBytes(string $html): int

{

    $bytes = 0;



    if (preg_match('/window\.__erpRoutes\s*=\s*(\{.*?\});/s', $html, $routes)) {

        $bytes += strlen($routes[1]);

    }



    if (preg_match('/window\.__erpFeatureDiscovery\s*=\s*(\{.*?\});/s', $html, $discovery)) {

        $bytes += strlen($discovery[1]);

    }



    $bytes += substr_count($html, '"search_text"') > 0 ? 999999 : 0;

    $bytes += substr_count($html, '"path_segments"') > 0 ? 999999 : 0;

    $bytes += substr_count($html, '"keywords"') > 0 ? 999999 : 0;



    return $bytes;

}



$shells = [

    'commercial' => '/admin/workspaces/commercial/crm',

    'production' => '/admin/workspaces/production/operations',

    'supply-chain' => '/admin/workspaces/supply-chain/catalogue',

    'administration' => '/admin/workspaces/administration/configuration',

];



$registry = app(FeatureRegistry::class);



echo "Removed from shell HTML (now lazy-loaded via API)\n";

echo str_repeat('-', 60)."\n";

printf("%-35s %8d bytes (%5.1f KB)\n", 'featureDiscoveryIndex (was inline)', strlen(json_encode($registry->index())), strlen(json_encode($registry->index())) / 1024);

printf("%-35s %8d bytes (%5.1f KB)\n", 'feature_index per module (was inline)', strlen(json_encode($registry->index('commercial'))), strlen(json_encode($registry->index('commercial'))) / 1024);

echo str_repeat('-', 60)."\n\n";



echo "Workspace shell measurements\n";

echo str_repeat('-', 88)."\n";

printf("%-16s %10s %10s %12s %s\n", 'Module', 'HTML', 'DOM nodes', 'Inline reg.', 'Flags');

echo str_repeat('-', 88)."\n";



foreach ($shells as $module => $uri) {

    $html = fetchHtml($kernel, $uri);

    $bytes = strlen($html);

    $nodes = countDomNodes($html);

    $inline = inlinePayloadBytes($html);

    $flags = [];



    if (str_contains($html, 'module-workspace-search')) {

        $flags[] = 'search';

    }



    if (str_contains($html, 'module-workspace-content')) {

        $flags[] = 'frame';

    }



    if (str_contains($html, 'workspace-pill-tabs')) {

        $flags[] = 'tabs';

    }



    if (substr_count($html, '"search_text"') > 0) {

        $flags[] = 'BLOAT:search_text';

    }



    printf(

        "%-16s %8d B %10d %8d B est %s\n",

        $module,

        $bytes,

        $nodes,

        $inline > 999999 ? 0 : $inline,

        implode(', ', $flags),

    );

}



echo str_repeat('-', 88)."\n";

echo "Lazy endpoint: ".route('admin.feature-discovery.search')."\n";


