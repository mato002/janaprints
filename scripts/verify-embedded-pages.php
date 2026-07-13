<?php

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$company = Company::query()->where('code', 'JANA')->firstOrFail();
$branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
$user = User::query()->where('email', 'admin@janaprints.local')->first()
    ?? User::query()->where('company_id', $company->id)->firstOrFail();
auth()->login($user);

$urls = [
    '/admin/production/floor?embedded=1',
    '/admin/production/command-center?embedded=1',
    '/admin/production/quality?embedded=1',
    '/admin/dispatch?embedded=1',
    '/admin/production/costing?embedded=1',
    '/admin/inventory/valuation?embedded=1',
];

foreach ($urls as $uri) {
    $request = Request::create($uri, 'GET');
    $request->headers->set('Turbo-Frame', 'module-workspace-content');
    $response = $kernel->handle($request);
    $body = (string) $response->getContent();
    $ok = $response->getStatusCode() === 200 && str_contains($body, 'id="module-workspace-content"');
    $msg = '';
    if (! $ok && preg_match('/Missing required parameter[^<\n]+|TypeError:[^<\n]+/', $body, $m)) {
        $msg = ' :: '.trim(html_entity_decode(strip_tags($m[0])));
    }
    echo ($ok ? 'OK ' : 'FAIL '.$response->getStatusCode().' ').$uri.$msg.PHP_EOL;
    $kernel->terminate($request, $response);
}
