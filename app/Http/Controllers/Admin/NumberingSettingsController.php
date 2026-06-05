<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ResolvesSettingsScope;
use App\Http\Controllers\Controller;
use App\Models\Platform\SettingsGovernance;
use App\Support\Platform\NumberingSequenceManager;
use App\Support\Platform\SettingsRegistry;
use App\Services\Security\SecurityAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NumberingSettingsController extends Controller
{
    use ResolvesSettingsScope;

    public function __construct(
        protected NumberingSequenceManager $manager,
        protected SettingsRegistry $registry,
        protected SecurityAuditService $auditService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SettingsGovernance::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);

        return view('admin.settings.numbering.index', [
            'sections' => $this->registry->sections(),
            'rows' => $this->manager->rows($companyId, $branchId),
            'companyId' => $companyId,
            'branchId' => $branchId,
            'companies' => $this->companiesForSettingsUser(),
            'branches' => $this->branchesForSettingsCompany($companyId),
            'canManage' => auth()->user()->can('update', new SettingsGovernance()),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorize('update', new SettingsGovernance());

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);

        $validated = $request->validate([
            'sequences' => ['required', 'array'],
            'sequences.*.prefix' => ['nullable', 'string', 'max:20'],
            'sequences.*.include_branch' => ['nullable', 'boolean'],
            'sequences.*.include_year' => ['nullable', 'boolean'],
            'sequences.*.include_month' => ['nullable', 'boolean'],
            'sequences.*.padding' => ['nullable', 'integer', 'min:1', 'max:10'],
            'sequences.*.next_number' => ['nullable', 'integer', 'min:1', 'max:999999999'],
            'sequences.*.active' => ['nullable', 'boolean'],
        ]);

        $before = $this->manager->rows($companyId, $branchId)
            ->mapWithKeys(fn (array $row) => [$row['document_type'] => collect($row)->only([
                'prefix', 'include_branch', 'include_year', 'include_month', 'padding', 'next_number', 'active',
            ])->all()])
            ->all();

        $this->manager->save($companyId, $branchId, $validated['sequences']);

        $after = $this->manager->rows($companyId, $branchId)
            ->mapWithKeys(fn (array $row) => [$row['document_type'] => collect($row)->only([
                'prefix', 'include_branch', 'include_year', 'include_month', 'padding', 'next_number', 'active',
            ])->all()])
            ->all();

        $this->auditService->record(
            action: 'number_series_changed',
            before: $before,
            after: $after,
            module: 'configuration',
            entity: 'number_series',
            description: __('Number series changed'),
        );

        return redirect()
            ->route('admin.settings.numbering.index', [
                'company_id' => $companyId,
                'branch_id' => $branchId,
            ])
            ->with('status', __('Numbering sequences updated.'));
    }
}
