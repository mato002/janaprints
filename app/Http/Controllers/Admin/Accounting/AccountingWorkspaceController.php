<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Admin\Accounting\Concerns\ResolvesAccountingTenant;
use App\Http\Controllers\Controller;
use App\Support\Accounting\Workspace\AccountingSectionMetricsService;
use App\Support\Navigation\AccountingWorkspacePresenter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountingWorkspaceController extends Controller
{
    use ResolvesAccountingTenant;

    public function __construct(
        protected AccountingWorkspacePresenter $presenter,
        protected AccountingSectionMetricsService $metrics,
    ) {}

    public function hub(Request $request): View
    {
        $payload = $this->presenter->presentHub();

        abort_if($payload === null, 403);

        return view('admin.accounting.workspaces.hub', [
            'workspace' => $payload,
            'cards' => collect($payload['items'])->map(fn (array $item) => array_merge($item, [
                'group_label' => __('Workspaces'),
                'search_text' => strtolower(implode(' ', array_filter([
                    __('Workspaces'),
                    $item['label'],
                    $item['description'],
                ]))),
            ]))->all(),
        ]);
    }

    public function section(Request $request, string $section): View
    {
        abort_unless($this->presenter->sectionExists($section), 404);

        $payload = $this->presenter->presentSection($section);

        abort_if($payload === null, 403);

        $tenant = $this->tenantIds();
        $insights = $this->metrics->forSection($section, [
            'company_id' => $request->integer('company_id') ?: $tenant['companyId'],
            'branch_id' => $request->has('branch_id')
                ? ($request->input('branch_id') !== '' ? $request->integer('branch_id') : null)
                : $tenant['branchId'],
            'period_id' => $request->integer('period_id') ?: null,
        ]);

        $cards = collect($payload['groups'])
            ->flatMap(fn (array $group) => collect($group['items'])->map(fn (array $item) => array_merge($item, [
                'group_label' => $group['label'],
                'search_text' => strtolower(implode(' ', array_filter([
                    $group['label'],
                    $item['label'],
                    $item['description'],
                ]))),
            ])))
            ->values()
            ->all();

        return view('admin.accounting.workspaces.section', [
            'workspace' => $payload,
            'section' => $section,
            'insights' => $insights,
            'cards' => $cards,
        ]);
    }
}
