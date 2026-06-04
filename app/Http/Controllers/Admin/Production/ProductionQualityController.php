<?php

namespace App\Http\Controllers\Admin\Production;

use App\Http\Controllers\Controller;
use App\Models\Production\QualityCheck;
use App\Services\Production\ProductionQualityWorkspaceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductionQualityController extends Controller
{
    public function index(Request $request, ProductionQualityWorkspaceService $workspace): View
    {
        $this->authorize('viewWorkspace', QualityCheck::class);

        $register = $workspace->paginatedRegister($request);
        $showingPending = $request->query('status') === 'pending';

        return view('admin.production.quality.index', [
            'kpis' => $workspace->kpiCounts(),
            'analytics' => $workspace->analytics(),
            'widgets' => $workspace->intelligenceWidgets(),
            'register' => $register,
            'showingPending' => $showingPending,
            'workspace' => $workspace,
            'inspectors' => $workspace->inspectorOptions(),
            'filters' => [
                'status' => $request->query('status'),
                'date' => $request->query('date'),
                'inspector' => $request->query('inspector'),
                'search' => $request->query('search'),
            ],
        ]);
    }
}
