<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Enums\PostingAccountResolver;
use App\Enums\PostingAmountSource;
use App\Enums\PostingEventCode;
use App\Enums\PostingLineSide;
use App\Enums\PostingModule;
use App\Http\Controllers\Admin\Accounting\Concerns\ResolvesAccountingTenant;
use App\Http\Controllers\Admin\Concerns\ResolvesEntityCode;
use App\Http\Controllers\Controller;
use App\Models\Accounting\GlAccount;
use App\Models\Accounting\PostingTemplate;
use App\Support\Accounting\Posting\PostingSetupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PostingTemplateController extends Controller
{
    use ResolvesAccountingTenant;
    use ResolvesEntityCode;

    public function __construct(
        protected PostingSetupService $setup,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', PostingTemplate::class);

        $templates = PostingTemplate::query()
            ->forTenant()
            ->withCount('lines')
            ->orderBy('module')
            ->orderBy('code')
            ->paginate(30);

        return view('admin.accounting.posting.templates.index', compact('templates'));
    }

    public function create(): View
    {
        $this->authorize('manage', PostingTemplate::class);

        return view('admin.accounting.posting.templates.create', $this->formMeta());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage', PostingTemplate::class);

        $companyId = $this->tenantIds()['companyId'];
        $data = $this->validateTemplate($request, $companyId);
        $lines = $this->validateLines($request);

        $template = $this->setup->createTemplate($companyId, $data, $lines);

        return redirect()
            ->route('admin.accounting.posting.templates.show', $template)
            ->with('status', __('Posting template created.'));
    }

    public function show(PostingTemplate $template): View
    {
        $this->authorize('view', $template);

        $template->load(['lines.glAccount']);

        return view('admin.accounting.posting.templates.show', compact('template'));
    }

    public function edit(PostingTemplate $template): View
    {
        $this->authorize('manage', PostingTemplate::class);

        if ($template->is_system) {
            abort(403, __('System posting templates cannot be edited.'));
        }

        $template->load('lines');

        return view('admin.accounting.posting.templates.edit', array_merge(
            $this->formMeta(),
            compact('template'),
        ));
    }

    public function update(Request $request, PostingTemplate $template): RedirectResponse
    {
        $this->authorize('manage', PostingTemplate::class);

        $data = $this->validateTemplate($request, $template->company_id, $template);
        $lines = $this->validateLines($request);

        $this->setup->updateTemplate($template, $data, $lines);

        return redirect()
            ->route('admin.accounting.posting.templates.show', $template)
            ->with('status', __('Posting template updated.'));
    }

    public function toggle(PostingTemplate $template): RedirectResponse
    {
        $this->authorize('manage', PostingTemplate::class);

        $this->setup->toggleTemplate($template);

        return back()->with('status', __('Posting template status updated.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function formMeta(): array
    {
        $companyId = $this->tenantIds()['companyId'];

        return [
            'modules' => PostingModule::cases(),
            'sides' => PostingLineSide::cases(),
            'resolvers' => PostingAccountResolver::cases(),
            'amountSources' => PostingAmountSource::cases(),
            'accountKeys' => array_keys(config('posting_account_keys', [])),
            'accounts' => GlAccount::query()
                ->where('company_id', $companyId)
                ->where('is_postable', true)
                ->orderBy('code')
                ->get(['id', 'code', 'name']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateTemplate(Request $request, int $companyId, ?PostingTemplate $template = null): array
    {
        $validated = $request->validate([
            'code' => array_merge(
                $template ? ['sometimes'] : $this->nullableCodeRules(64),
                [
                    'string',
                    'max:64',
                    Rule::unique('posting_templates', 'code')
                        ->where(fn ($q) => $q->where('company_id', $companyId))
                        ->ignore($template?->id),
                ],
            ),
            'name' => ['required', 'string', 'max:160'],
            'module' => ['required', Rule::enum(PostingModule::class)],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['code'] = $this->resolveCompanyScopedCode(
            $request,
            'name',
            PostingTemplate::class,
            $companyId,
            $template?->id,
            64,
        );

        return $validated;
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function validateLines(Request $request): array
    {
        $validated = $request->validate([
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.entry_side' => ['required', Rule::enum(PostingLineSide::class)],
            'lines.*.account_resolver' => ['required', Rule::enum(PostingAccountResolver::class)],
            'lines.*.gl_account_id' => ['nullable', 'integer', 'exists:gl_accounts,id'],
            'lines.*.account_key' => ['nullable', 'string', 'max:64'],
            'lines.*.context_account_field' => ['nullable', 'string', 'max:64'],
            'lines.*.amount_source' => ['required', Rule::enum(PostingAmountSource::class)],
            'lines.*.amount_field' => ['nullable', 'string', 'max:64'],
            'lines.*.line_description' => ['nullable', 'string', 'max:255'],
        ]);

        return array_values($validated['lines']);
    }
}
