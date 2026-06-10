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
use Illuminate\Support\Facades\Log;
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

    public function index(Request $request): View|Response
    {
        $this->authorize('viewAny', SettingsGovernance::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);

        $forms = $this->manager->rows($companyId, $branchId);
        $canManage = auth()->user()->can('settings.manage')
            || auth()->user()->can('update', new SettingsGovernance());

        if ($this->isEmbeddedTurboFrameRequest($request)) {
            return $this->embeddedIndexResponse($request, $companyId, $branchId, $forms, $canManage);
        }

        return view('admin.settings.forms.index', [
            'sections' => $this->registry->sections(),
            'forms' => $forms,
            'controlCenter' => $this->controlCenter->hub($companyId, $branchId, $forms),
            'companyId' => $companyId,
            'branchId' => $branchId,
            'companies' => $this->companiesForSettingsUser(),
            'branches' => $this->branchesForSettingsCompany($companyId),
            'canManage' => $canManage,
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
            $errorMessage = __('Unable to save form settings. Please review the highlighted fields.');

            if ($this->isTurboFrameRequest($request) && $returnForm) {
                return $this->frameResponse(
                    $companyId,
                    $branchId,
                    $returnForm,
                    '',
                    $redirectParams,
                    $exception->validator->errors(),
                    $errorMessage,
                    $request,
                );
            }

            return redirect()
                ->route('admin.settings.forms.index', $redirectParams)
                ->withErrors($exception->validator)
                ->withInput()
                ->with('error', $errorMessage);
        }

        $returnForm = $this->resolveReturnFormKey($request, $validated['forms']);

        try {
            $this->manager->save($companyId, $branchId, $validated['forms']);
        } catch (\Throwable $exception) {
            Log::error('Form settings save failed', [
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'form' => $returnForm,
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            $errorMessage = __('Unable to save form settings. Please try again.');

            if ($this->isTurboFrameRequest($request) && $returnForm) {
                return $this->frameResponse(
                    $companyId,
                    $branchId,
                    $returnForm,
                    '',
                    $redirectParams,
                    null,
                    $errorMessage,
                    $request,
                );
            }

            return redirect()
                ->route('admin.settings.forms.index', $redirectParams)
                ->with('error', $errorMessage);
        }

        $statusMessage = $this->successMessage($returnForm, $companyId, $branchId);
        $redirectParams = $this->redirectParams($companyId, $branchId, $returnForm, $this->isEmbeddedTurboFrameRequest($request));

        if ($returnForm && $this->isTurboFrameRequest($request)) {
            return $this->frameResponse($companyId, $branchId, $returnForm, $statusMessage, $redirectParams, null, null, $request);
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

        if ($returnForm === '') {
            $returnForm = trim((string) $request->input('form', ''));
        }

        if ($returnForm === '') {
            $returnForm = trim((string) $request->query('form', ''));
        }

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
    protected function redirectParams(int $companyId, ?int $branchId, ?string $returnForm, bool $embedded = false): array
    {
        return array_filter([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'form' => $returnForm,
            'embedded' => $embedded ? '1' : null,
        ], fn ($value) => $value !== null && $value !== '');
    }

    protected function isTurboFrameRequest(Request $request): bool
    {
        return in_array($request->header('Turbo-Frame'), ['erp-main', 'module-workspace-content'], true)
            || $request->boolean('_turbo_frame');
    }

    protected function isEmbeddedTurboFrameRequest(Request $request): bool
    {
        return $request->header('Turbo-Frame') === 'module-workspace-content'
            || ($request->boolean('_turbo_frame') && $request->boolean('_embedded_workspace'));
    }

    protected function successMessage(?string $returnForm, int $companyId, ?int $branchId): string
    {
        if ($returnForm === null || $returnForm === '') {
            return __('Form settings saved successfully.');
        }

        $forms = $this->manager->rows($companyId, $branchId);
        $activeForm = $forms->first(fn (array $form) => $form['form_key'] === $returnForm);

        if ($activeForm) {
            return __(':form form settings saved successfully.', ['form' => __($activeForm['label'])]);
        }

        return __('Form settings saved successfully.');
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $forms
     */
    protected function embeddedIndexResponse(
        Request $request,
        int $companyId,
        ?int $branchId,
        $forms,
        bool $canManage,
    ): Response {
        $scopeQuery = array_filter([
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        $activeFormKey = trim((string) $request->query('form', ''));
        $activeForm = $activeFormKey !== ''
            ? $forms->first(fn (array $form) => $form['form_key'] === $activeFormKey)
            : null;

        if ($activeForm) {
            return response()->view('admin.settings.forms.embedded-frame', [
                'title' => $activeForm['label'],
                'activeForm' => $activeForm,
                'activeFormKey' => $activeFormKey,
                'companyId' => $companyId,
                'branchId' => $branchId,
                'companies' => $this->companiesForSettingsUser(),
                'branches' => $this->branchesForSettingsCompany($companyId),
                'canManage' => $canManage,
                'scopeQuery' => $scopeQuery,
                'statusMessage' => session('status'),
                'errorMessage' => session('error'),
                'validationErrors' => session('errors'),
            ]);
        }

        return response()->view('admin.settings.forms.embedded-landing', [
            'controlCenter' => $this->controlCenter->hub($companyId, $branchId, $forms),
            'companyId' => $companyId,
            'branchId' => $branchId,
            'companies' => $this->companiesForSettingsUser(),
            'branches' => $this->branchesForSettingsCompany($companyId),
            'canManage' => $canManage,
            'scopeQuery' => $scopeQuery,
        ]);
    }

    protected function frameResponse(
        int $companyId,
        ?int $branchId,
        string $returnForm,
        string $statusMessage,
        array $redirectParams = [],
        ?\Illuminate\Support\MessageBag $errors = null,
        ?string $errorMessage = null,
        ?Request $request = null,
    ): Response {
        $request ??= request();
        $embedded = $this->isEmbeddedTurboFrameRequest($request);
        $forms = $this->manager->rows($companyId, $branchId);
        $activeForm = $forms->first(fn (array $form) => $form['form_key'] === $returnForm);

        abort_unless($activeForm, 404);

        $scopeQuery = array_filter([
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        $redirectParams = $redirectParams !== []
            ? $redirectParams
            : $this->redirectParams($companyId, $branchId, $returnForm, $embedded);

        $viewData = [
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
            'errorMessage' => $errorMessage,
            'validationErrors' => $errors,
        ];

        $view = $embedded
            ? 'admin.settings.forms.embedded-frame'
            : 'admin.settings.forms.frame';

        return response()
            ->view($view, $viewData)
            ->header('Turbo-Location', route('admin.settings.forms.index', $redirectParams));
    }
}
