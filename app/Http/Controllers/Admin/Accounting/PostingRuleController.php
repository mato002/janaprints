<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Admin\Accounting\Concerns\ResolvesAccountingTenant;
use App\Http\Controllers\Controller;
use App\Models\Accounting\PostingRule;
use App\Support\Accounting\Posting\PostingRuleWorkspacePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostingRuleController extends Controller
{
    use ResolvesAccountingTenant;

    public function __construct(
        protected PostingRuleWorkspacePresenter $presenter,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', PostingRule::class);

        $workspace = $this->presenter->buildIndex($request);

        return view('admin.accounting.posting.rules.index', [
            'rules' => $workspace['rules'],
            'validations' => $workspace['validations'],
            'summary' => $workspace['summary'],
            'moduleSummary' => $workspace['moduleSummary'],
            'activeFilters' => $workspace['filters'],
            'filterOptions' => $workspace['filterOptions'],
            'canAudit' => $request->user()?->can('audit', PostingRule::class) ?? false,
        ]);
    }

    public function show(Request $request, PostingRule $rule): JsonResponse
    {
        $this->authorize('view', $rule);

        $includeAudit = $request->user()?->can('audit', PostingRule::class) ?? false;

        return response()->json([
            'rule' => $this->presenter->buildRuleDetail($rule, $includeAudit),
        ]);
    }
}
