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
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
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

    public function update(Request $request): RedirectResponse|Response
    {
        $this->authorize('update', new SettingsGovernance());

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);

        $returnForm = $this->resolveReturnFormKey($request);
        $redirectParams = $this->redirectParams($companyId, $branchId, $returnForm);

        try {
            $validated = Validator::make($request->all(), [
                'forms' => ['required', 'array'],
                'forms.*.is_active' => ['nullable', 'in:0,1,true,false'],
                'forms.*.fields' => ['nullable', 'array'],
                'forms.*.fields.*.visibility' => ['nullable', 'in:visible,hidden'],
                'forms.*.fields.*.requirement' => ['nullable', 'in:required,optional'],
                'forms.*.fields.*.read_only' => ['nullable', 'in:0,1,true,false'],
                'forms.*.fields.*.default_value' => ['nullable', 'string', 'max:500'],
                'forms.*.fields.*.label' => ['nullable', 'string', 'max:120'],
                'forms.*.fields.*.type' => ['nullable', 'in:text,email,number,date,textarea,select,checkbox'],
                'forms.*.add_field' => ['nullable', 'array'],
                'forms.*.add_field.field_key' => ['nullable', 'string', 'max:64'],
                'forms.*.add_field.label' => ['nullable', 'string', 'max:120'],
                'forms.*.add_field.type' => ['nullable', 'in:text,email,number,date,textarea,select,checkbox'],
                'forms.*.add_field.visibility' => ['nullable', 'in:visible,hidden'],
                'forms.*.add_field.requirement' => ['nullable', 'in:required,optional'],
                'forms.*.remove_fields' => ['nullable', 'array'],
                'forms.*.remove_fields.*' => ['nullable', 'string', 'max:64'],
            ])->validate();
        } catch (ValidationException $exception) {
            throw $exception->redirectTo(route('admin.settings.forms.index', $redirectParams));
        }

        $returnForm = $this->resolveReturnFormKey($request, $validated['forms']);

        $this->manager->save($companyId, $branchId, $validated['forms']);

        $statusMessage = __('Form settings updated.');
        $redirectParams = $this->redirectParams($companyId, $branchId, $returnForm);

        if ($request->header('Turbo-Frame') === 'erp-main' && $returnForm) {
            return $this->frameResponse($companyId, $branchId, $returnForm, $statusMessage, $redirectParams);
        }

        return redirect()
            ->route('admin.settings.forms.index', $redirectParams)
            ->with('status', $statusMessage);
    }

    /**
     * @param  array<string, mixed>|null  $validatedForms
     */
    protected function resolveReturnFormKey(Request $request, ?array $validatedForms = null): ?string
    {
        $returnForm = trim((string) $request->input('return_form', ''));

        if ($returnForm !== '') {
            return $returnForm;
        }

        $forms = $validatedForms ?? $request->input('forms', []);

        if (! is_array($forms) || $forms === []) {
            return null;
        }

        $formKey = array_key_first($forms);

        return is_string($formKey) && $formKey !== '' ? $formKey : null;
    }

    /**
     * @return array<string, int|string>
     */
    protected function redirectParams(int $companyId, ?int $branchId, ?string $returnForm): array
    {
        return array_filter([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'form' => $returnForm,
        ], fn ($value) => $value !== null && $value !== '');
    }

    protected function frameResponse(
        int $companyId,
        ?int $branchId,
        string $returnForm,
        string $statusMessage,
        array $redirectParams = [],
    ): Response {
        $forms = $this->manager->rows($companyId, $branchId);
        $activeForm = $forms->first(fn (array $form) => $form['form_key'] === $returnForm);

        abort_unless($activeForm, 404);

        $scopeQuery = array_filter([
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        $redirectParams = $redirectParams !== []
            ? $redirectParams
            : $this->redirectParams($companyId, $branchId, $returnForm);

        return response()
            ->view('admin.settings.forms.frame', [
                'title' => $activeForm['label'],
                'activeForm' => $activeForm,
                'activeFormKey' => $returnForm,
                'companyId' => $companyId,
                'branchId' => $branchId,
                'companies' => $this->companiesForSettingsUser(),
                'branches' => $this->branchesForSettingsCompany($companyId),
                'canManage' => auth()->user()->can('settings.manage')
                    || auth()->user()->can('update', new SettingsGovernance()),
                'scopeQuery' => $scopeQuery,
                'hubBackUrl' => route('admin.settings.show', ['section' => 'hub'] + $scopeQuery),
                'statusMessage' => $statusMessage,
            ])
            ->header('Turbo-Location', route('admin.settings.forms.index', $redirectParams));
    }
}
