<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Crm\Concerns\ResolvesCrmTenant;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Support\Commercial\PosCertificationScopeResolver;
use App\Support\Commercial\PosCertificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosCertificationController extends Controller
{
    use ResolvesCrmTenant, ScopesToTenant;

    public function __construct(
        protected PosCertificationService $certification,
        protected PosCertificationScopeResolver $scopeResolver,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('commercial.pos.certification.view'), 403);

        $scope = $this->scopeResolver->resolve($request);
        $result = $this->certification->certify($scope);

        ['companyId' => $companyId] = $this->tenantIds($request);
        $user = $request->user();

        $branches = $user?->can('commercial.pos.sessions.admin')
            ? Branch::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get(['id', 'name'])
            : collect();

        return view('admin.commercial.pos.certification.index', [
            'certification' => $result,
            'filters' => $request->only(['from_date', 'to_date', 'branch_id']),
            'branches' => $branches,
            'canViewAllBranches' => $user?->can('commercial.pos.sessions.admin') ?? false,
        ]);
    }
}
