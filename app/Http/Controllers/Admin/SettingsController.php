<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ResolvesSettingsScope;
use App\Http\Controllers\Controller;
use App\Models\Platform\SettingsGovernance;
use App\Support\Navigation\ModuleShellPresenter;
use App\Support\Navigation\WorkspaceEmbed;
use App\Support\Platform\SettingsControlCenterPresenter;
use App\Support\Platform\SettingsRegistry;
use App\Support\Platform\SystemSettingsManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingsController extends Controller
{
    use ResolvesSettingsScope;

    public function __construct(
        protected SettingsRegistry $registry,
        protected SystemSettingsManager $manager,
        protected SettingsControlCenterPresenter $controlCenter,
    ) {}

    public function index(): RedirectResponse
    {
        $this->authorize('viewAny', SettingsGovernance::class);

        $deskUrl = app(ModuleShellPresenter::class)->deskUrlForFeatureRoute(
            'admin.settings.show',
            ['section' => 'hub'],
        );

        if ($deskUrl !== null) {
            return redirect()->to($deskUrl);
        }

        return redirect()->route('admin.settings.show', $this->registry->sectionSlugs()[0]);
    }

    public function show(Request $request, string $section): View
    {
        $this->authorize('viewAny', SettingsGovernance::class);
        $this->assertSection($section);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);

        return view('admin.settings.show', [
            'section' => $section,
            'sectionMeta' => $this->registry->section($section),
            'sections' => $this->registry->sections(),
            'rows' => $section === 'hub'
                ? collect()
                : $this->manager->rowsForSection($section, $companyId, $branchId),
            'controlCenter' => $section === 'hub'
                ? $this->controlCenter->hub($companyId, $branchId)
                : null,
            'companyId' => $companyId,
            'branchId' => $branchId,
            'companies' => $this->companiesForSettingsUser(),
            'branches' => $this->branchesForSettingsCompany($companyId),
            'canManage' => auth()->user()->can('update', new SettingsGovernance()),
        ]);
    }

    public function update(Request $request, string $section): RedirectResponse
    {
        $this->authorize('update', new SettingsGovernance());
        $this->assertSection($section);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);

        $sectionDefinition = $this->registry->section($section);
        $rules = ['settings' => ['nullable', 'array']];

        foreach ($sectionDefinition['settings'] as $key => $definition) {
            $type = $definition['type'] ?? 'string';

            if (in_array('company', $definition['scopes'] ?? [], true)) {
                $rules["settings.{$key}.company"] = $this->valueRule($type, true);
            }

            if ($branchId && in_array('branch', $definition['scopes'] ?? [], true)) {
                $rules["settings.{$key}.branch"] = $this->valueRule($type, true);
            }
        }

        $validated = $request->validate($rules);

        $this->manager->saveSection(
            $section,
            $validated['settings'] ?? [],
            $companyId,
            $branchId,
        );

        return redirect()
            ->route('admin.settings.show', array_filter([
                'section' => $section,
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'embedded' => WorkspaceEmbed::inWorkspaceContext($request) ? '1' : null,
            ]))
            ->with('status', __('Settings saved.'));
    }

    protected function assertSection(string $section): void
    {
        abort_unless($this->registry->hasSection($section), 404);
    }

    /**
     * @return list<string|Rule>
     */
    protected function valueRule(string $type, bool $nullable): array
    {
        $rules = ['nullable'];

        return match ($type) {
            'boolean' => [...$rules, Rule::in(['0', '1', 'true', 'false', 'inherit', true, false, 0, 1])],
            'integer' => [...$rules, 'integer', 'min:0', 'max:999999'],
            'float' => [...$rules, 'numeric', 'min:0'],
            default => [...$rules, 'string', 'max:255'],
        };
    }
}
