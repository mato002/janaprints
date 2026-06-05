<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Http\Controllers\Controller;
use App\Operations\SystemHealthCenter;
use App\Services\Operations\SystemHealthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemHealthController extends Controller
{
    public function __construct(
        protected SystemHealthService $healthService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SystemHealthCenter::class);

        $snapshot = $this->healthService->snapshot();

        return view('admin.operations.health.index', [
            'snapshot' => $snapshot,
            'canManage' => $request->user()->can('manage', SystemHealthCenter::class),
        ]);
    }

    public function snapshot(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SystemHealthCenter::class);

        return response()->json($this->healthService->snapshot());
    }

    public function refresh(Request $request): RedirectResponse
    {
        $this->authorize('manage', SystemHealthCenter::class);

        $this->healthService->refreshOperationalCaches();

        return redirect()
            ->route('admin.operations.health.index')
            ->with('success', __('System health caches refreshed.'));
    }
}
