<?php

/**
 * Probe workspace embedded turbo-frame content URLs for HTTP 500s.
 *
 * Usage: php scripts/probe-workspace-frame.php
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

$company = Company::query()->where('code', 'JANA')->firstOrFail();
$branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
$user = User::query()->where('company_id', $company->id)->firstOrFail();

auth()->login($user);
session()->put('active_branch_id', $branch->id);
session()->put('active_company_id', $company->id);
session()->save();

echo "User: {$user->email} (id={$user->id})\n";
echo "Company: {$company->code} / Branch: {$branch->code} (branch_id={$branch->id})\n\n";

$workspaces = [
    'production' => array_keys(config('production_workspaces.sections', [])),
    'commercial' => array_keys(config('commercial_workspaces.sections', [])),
    'supply-chain' => array_keys(config('supply_chain_workspaces.sections', [])),
    'administration' => array_keys(config('administration_workspaces.sections', [])),
];

$shellUris = [];
foreach ($workspaces as $workspace => $sections) {
    $shellUris[] = "/admin/workspaces/{$workspace}";
    foreach ($sections as $section) {
        $shellUris[] = "/admin/workspaces/{$workspace}/{$section}";
    }
}


function requestGet(Kernel $kernel, string $uri, array $headers = []): array
{
    $request = Request::create($uri, 'GET');
    foreach ($headers as $key => $value) {
        $request->headers->set($key, $value);
    }

    // Carry session / auth from login
    $request->setLaravelSession(app('session.store'));
    $request->setUserResolver(fn () => auth()->user());

    try {
        $response = $kernel->handle($request);
        $body = (string) $response->getContent();
        $status = $response->getStatusCode();
        $location = $response->headers->get('Location');
        $kernel->terminate($request, $response);

        return [
            'status' => $status,
            'body' => $body,
            'location' => $location,
            'exception' => null,
        ];
    } catch (\Throwable $e) {
        return [
            'status' => 500,
            'body' => '',
            'location' => null,
            'exception' => $e->getMessage(),
            'exception_class' => get_class($e),
        ];
    }
}

function extractEmbeddedUrls(string $html): array
{
    $urls = [];

    // turbo-frame#module-workspace-content src=...
    if (preg_match_all(
        '/<turbo-frame[^>]*\bid=["\']module-workspace-content["\'][^>]*>/i',
        $html,
        $tags
    )) {
        foreach ($tags[0] as $tag) {
            if (preg_match('/\bsrc=["\']([^"\']+)["\']/i', $tag, $m)) {
                $urls[] = html_entity_decode($m[1], ENT_QUOTES);
            }
        }
    }

    // Also catch src before id or reverse attribute order already covered by tag match;
    // Secondary tabs targeting the module frame
    if (preg_match_all(
        '/<a[^>]*\bdata-turbo-frame=["\']module-workspace-content["\'][^>]*>/i',
        $html,
        $anchors
    )) {
        foreach ($anchors[0] as $tag) {
            if (preg_match('/\bhref=["\']([^"\']+)["\']/i', $tag, $m)) {
                $urls[] = html_entity_decode($m[1], ENT_QUOTES);
            }
        }
    }

    // href before data-turbo-frame
    if (preg_match_all(
        '/<a[^>]*\bhref=["\']([^"\']+)["\'][^>]*\bdata-turbo-frame=["\']module-workspace-content["\'][^>]*>/i',
        $html,
        $m
    )) {
        foreach ($m[1] as $href) {
            $urls[] = html_entity_decode($href, ENT_QUOTES);
        }
    }

    $normalized = [];
    foreach ($urls as $url) {
        if ($url === '' || str_starts_with($url, '#')) {
            continue;
        }
        // Absolute path only for in-app probes
        if (preg_match('#^https?://#i', $url)) {
            $parts = parse_url($url);
            $url = ($parts['path'] ?? '/').(isset($parts['query']) ? '?'.$parts['query'] : '');
        }
        $normalized[$url] = true;
    }

    return array_keys($normalized);
}

function extractErrorMessage(array $result): string
{
    if (! empty($result['exception'])) {
        $class = $result['exception_class'] ?? 'Exception';

        return "{$class}: {$result['exception']}";
    }

    $body = $result['body'] ?? '';

    // Missing required parameter (Laravel / Symfony)
    if (preg_match('/Missing required parameter[:\s]+([^\n<"\\\\]{1,200})/i', $body, $m)) {
        return 'Missing required parameter: '.trim(html_entity_decode($m[1]));
    }

    // Ignition / JSON payload
    if (preg_match('/"message"\s*:\s*"((?:\\\\.|[^"\\\\]){1,400})"/', $body, $m)) {
        return stripcslashes($m[1]);
    }

    if (preg_match('/\\\\u0022message\\\\u0022:\\\\u0022([^\\\\]{1,400})/', $body, $m)) {
        return $m[1];
    }

    // Title
    if (preg_match('/<title>(.*?)<\/title>/is', $body, $m)) {
        $title = trim(html_entity_decode(strip_tags($m[1])));
        if ($title !== '' && ! preg_match('/^(Server Error|Error)$/i', $title)) {
            return $title;
        }
    }

    // Common exception class lines
    if (preg_match('/(Illuminate\\\\[A-Za-z0-9_\\\\]+|Symfony\\\\[A-Za-z0-9_\\\\]+|ErrorException|TypeError|ParseError|InvalidArgumentException)[^<\n]{0,300}/', $body, $m)) {
        return trim(html_entity_decode(strip_tags($m[0])));
    }

    if (preg_match('/class="exception-message"[^>]*>(.*?)<\//is', $body, $m)) {
        return trim(html_entity_decode(strip_tags($m[1])));
    }

    return '(no message extracted; body '.strlen($body).' bytes)';
}

$allEmbedded = [];
$shellResults = [];

echo "=== SHELLS ===\n";
foreach ($shellUris as $uri) {
    $result = requestGet($kernel, $uri);
    $status = $result['status'];
    $urls = extractEmbeddedUrls($result['body'] ?? '');
    $shellResults[$uri] = [
        'status' => $status,
        'location' => $result['location'],
        'urls' => $urls,
        'exception' => $result['exception'] ?? null,
    ];

    echo "SHELL {$status} {$uri}";
    if ($result['location']) {
        echo " -> {$result['location']}";
    }
    echo ' | frames/tabs: '.count($urls)."\n";

    if ($status >= 500) {
        echo '  ERROR: '.extractErrorMessage($result)."\n";
    }

    foreach ($urls as $u) {
        $allEmbedded[$u] = true;
    }
}

echo "\nUnique embedded URLs: ".count($allEmbedded)."\n\n";
echo "=== EMBEDDED PROBES (Turbo-Frame: module-workspace-content) ===\n";

$failures = [];
$ok = 0;

foreach (array_keys($allEmbedded) as $uri) {
    $result = requestGet($kernel, $uri, ['Turbo-Frame' => 'module-workspace-content']);
    $status = $result['status'];
    $hasFrame = str_contains($result['body'] ?? '', 'id="module-workspace-content"')
        || str_contains($result['body'] ?? '', "id='module-workspace-content'");
    $msg = ($status >= 400 || ! empty($result['exception'])) ? extractErrorMessage($result) : '';

    $line = sprintf(
        '%s | frame=%s | %s',
        $status,
        $hasFrame ? 'yes' : 'no',
        $uri
    );
    echo $line."\n";

    if ($status >= 500 || ! empty($result['exception'])) {
        echo '  >> '.$msg."\n";
        $failures[] = [
            'uri' => $uri,
            'status' => $status,
            'has_frame' => $hasFrame,
            'message' => $msg,
        ];
    } elseif ($status >= 400) {
        echo '  >> '.$msg."\n";
        $failures[] = [
            'uri' => $uri,
            'status' => $status,
            'has_frame' => $hasFrame,
            'message' => $msg,
        ];
    } else {
        $ok++;
    }
}

echo "\n=== SUMMARY ===\n";
echo 'Shells probed: '.count($shellUris)."\n";
echo 'Embedded URLs: '.count($allEmbedded)."\n";
echo "OK (<400): {$ok}\n";
echo 'Failing (>=400 or exception): '.count($failures)."\n\n";

if ($failures) {
    echo "FAILING URLS:\n";
    foreach ($failures as $f) {
        echo "- [{$f['status']}] {$f['uri']}\n  {$f['message']}\n";
    }
} else {
    echo "No failing embedded URLs.\n";
}

// Also report shell 500s
$shellFails = array_filter($shellResults, fn ($r) => ($r['status'] ?? 0) >= 500);
if ($shellFails) {
    echo "\nFAILING SHELLS:\n";
    foreach ($shellFails as $uri => $r) {
        echo "- [{$r['status']}] {$uri}\n";
    }
}

