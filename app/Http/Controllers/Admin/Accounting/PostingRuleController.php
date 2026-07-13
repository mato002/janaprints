<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Enums\PostingEventCode;
use App\Http\Controllers\Admin\Accounting\Concerns\ResolvesAccountingTenant;
use App\Http\Controllers\Controller;
use App\Models\Accounting\PostingRule;
use App\Models\Accounting\PostingTemplate;
use App\Support\Accounting\Posting\PostingRuleWorkspacePresenter;
use App\Support\Accounting\Posting\PostingSetupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PostingRuleController extends Controller
{
    use ResolvesAccountingTenant;

    public function __construct(
        protected PostingRuleWorkspacePresenter $presenter,
        protected PostingSetupService $setup,
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
            'canManage' => $request->user()?->can('manage', PostingRule::class) ?? false,
        ]);
    }

    public function create(): View
    {
        $this->authorize('manage', PostingRule::class);

        return view('admin.accounting.posting.rules.create', $this->formMeta());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage', PostingRule::class);

        $data = $this->validateRule($request);
        $rule = $this->setup->createRule($this->tenantIds()['companyId'], $data);

        return redirect()
            ->route('admin.accounting.posting.rules.index')
            ->with('status', __('Posting rule :name created.', ['name' => $rule->name]));
    }

    public function show(Request $request, PostingRule $rule): JsonResponse
    {
        $this->authorize('view', $rule);

        $includeAudit = $request->user()?->can('audit', PostingRule::class) ?? false;

        return response()->json([
            'rule' => $this->presenter->buildRuleDetail($rule, $includeAudit),
        ]);
    }

    public function edit(PostingRule $rule): View
    {
        $this->authorize('manage', PostingRule::class);

        return view('admin.accounting.posting.rules.edit', array_merge(
            $this->formMeta(),
            compact('rule'),
        ));
    }

    public function update(Request $request, PostingRule $rule): RedirectResponse
    {
        $this->authorize('manage', PostingRule::class);

        $this->setup->updateRule($rule, $this->validateRule($request, $rule));

        return redirect()
            ->route('admin.accounting.posting.rules.index')
            ->with('status', __('Posting rule updated.'));
    }

    public function toggle(PostingRule $rule): RedirectResponse
    {
        $this->authorize('manage', PostingRule::class);

        $this->setup->toggleRule($rule);

        return back()->with('status', __('Posting rule status updated.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function formMeta(): array
    {
        $companyId = $this->tenantIds()['companyId'];

        return [
            'events' => PostingEventCode::cases(),
            'templates' => PostingTemplate::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->orderBy('code')
                ->get(['id', 'code', 'name', 'module']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateRule(Request $request, ?PostingRule $rule = null): array
    {
        $companyId = $this->tenantIds()['companyId'];

        return $request->validate([
            'event_code' => ['required', Rule::enum(PostingEventCode::class)],
            'posting_template_id' => [
                'required',
                'integer',
                Rule::exists('posting_templates', 'id')->where(fn ($q) => $q->where('company_id', $companyId)),
            ],
            'name' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:500'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'is_active' => ['sometimes', 'boolean'],
            'auto_post' => ['sometimes', 'boolean'],
        ]);
    }
}
