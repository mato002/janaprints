<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ResolvesSettingsScope;
use App\Http\Controllers\Controller;
use App\Models\Platform\SettingsGovernance;
use App\Support\Platform\FormSettingsManager;
use App\Support\Platform\FormsControlCenterPresenter;
use App\Support\Platform\SettingsRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FormSettingsController extends Controller
{
    use ResolvesSettingsScope;

    public function __construct(
        protected FormSettingsManager $manager,
        protected SettingsRegistry $registry,
        protected FormsControlCenterPresenter $controlCenter,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SettingsGovernance::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);

        $forms = $this->manager->rows($companyId, $branchId);

        return view('admin.settings.forms.index', [
            'sections' => $this->registry->sections(),
            'forms' => $forms,
            'controlCenter' => $this->controlCenter->hub($companyId, $branchId, $forms),
            'companyId' => $companyId,
            'branchId' => $branchId,
            'companies' => $this->companiesForSettingsUser(),
            'branches' => $this->branchesForSettingsCompany($companyId),
            'canManage' => auth()->user()->can('settings.manage')
                || auth()->user()->can('update', new SettingsGovernance()),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorize('update', new SettingsGovernance());

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);

        $validated = $request->validate([
            'forms' => ['required', 'array'],
            'forms.*.is_active' => ['nullable', 'boolean'],
            'forms.*.fields' => ['nullable', 'array'],
            'forms.*.fields.*.visibility' => ['nullable', 'in:visible,hidden'],
            'forms.*.fields.*.requirement' => ['nullable', 'in:required,optional'],
            'forms.*.fields.*.read_only' => ['nullable', 'boolean'],
            'forms.*.fields.*.default_value' => ['nullable', 'string', 'max:500'],
            'forms.*.fields.*.label' => ['nullable', 'string', 'max:120'],
            'forms.*.fields.*.type' => ['nullable', 'in:text,email,number,date,textarea,select,checkbox'],
            'forms.*.add_field' => ['nullable', 'array'],
            'forms.*.add_field.field_key' => ['nullable', 'string', 'max:64', 'regex:/^[a-z][a-z0-9_]*$/'],
            'forms.*.add_field.label' => ['nullable', 'string', 'max:120'],
            'forms.*.add_field.type' => ['nullable', 'in:text,email,number,date,textarea,select,checkbox'],
            'forms.*.add_field.visibility' => ['nullable', 'in:visible,hidden'],
            'forms.*.add_field.requirement' => ['nullable', 'in:required,optional'],
            'forms.*.remove_fields' => ['nullable', 'array'],
            'forms.*.remove_fields.*' => ['nullable', 'string', 'max:64'],
        ]);

        $this->manager->save($companyId, $branchId, $validated['forms']);

        $redirectParams = array_filter([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'form' => $request->input('return_form'),
        ]);

        return redirect()
            ->route('admin.settings.forms.index', $redirectParams)
            ->with('status', __('Form settings updated.'));
    }
}
