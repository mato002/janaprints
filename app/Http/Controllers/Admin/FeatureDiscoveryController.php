<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Discovery\FeatureRegistry;
use App\Support\Platform\PlatformCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeatureDiscoveryController extends Controller
{
    public function search(Request $request, FeatureRegistry $registry, PlatformCacheService $cache): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));
        $moduleKey = $request->query('module');
        $moduleKey = is_string($moduleKey) && $moduleKey !== '' ? $moduleKey : null;

        if ($query === '') {
            return response()->json(['results' => []]);
        }

        $user = auth()->user();
        $companyId = $user?->company_id ?? 'none';
        $branchId = $user?->default_branch_id ?? 'none';
        $roleKey = $user?->roles->pluck('name')->sort()->implode('|') ?? 'guest';
        $cacheKey = "search:{$query}:{$moduleKey}:{$user?->id}:{$companyId}:{$branchId}:{$roleKey}";

        $results = $cache->remember(
            'feature_discovery',
            $cacheKey,
            fn () => $registry->searchForClient($query, $moduleKey),
        );

        return response()->json(['results' => $results]);
    }
}
