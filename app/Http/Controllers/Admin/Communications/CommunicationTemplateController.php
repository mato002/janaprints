<?php

namespace App\Http\Controllers\Admin\Communications;

use App\Enums\CommunicationChannel;
use App\Enums\CommunicationTemplateCategory;
use App\Enums\CommunicationTemplateStatus;
use App\Enums\CommunicationTemplateType;
use App\Http\Controllers\Admin\Communications\Concerns\ResolvesCommunicationsTenant;
use App\Http\Controllers\Controller;
use App\Models\Communications\CommunicationTemplate;
use App\Models\Communications\CommunicationTemplateVersion;
use App\Support\Communications\CommunicationTemplateService;
use App\Support\Communications\TemplateRenderer;
use App\Support\Communications\TemplateVariableEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CommunicationTemplateController extends Controller
{
    use ResolvesCommunicationsTenant;

    public function __construct(
        protected CommunicationTemplateService $templates,
        protected TemplateRenderer $renderer,
        protected TemplateVariableEngine $variables,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CommunicationTemplate::class);

        $query = CommunicationTemplate::query()
            ->forTenant()
            ->with(['creator', 'updater']);

        if ($channel = $request->string('channel')->toString()) {
            $query->where('channel', $channel);
        }

        if ($category = $request->string('category')->toString()) {
            $query->where('category', $category);
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($group = $request->string('group')->toString()) {
            $categories = $this->categoriesForGroup($group);

            if ($categories !== []) {
                $query->whereIn('category', $categories);
            }
        }

        $templates = $query->orderBy('name')->get();

        $summary = [
            'total' => CommunicationTemplate::query()->forTenant()->count(),
            'sms' => CommunicationTemplate::query()->forTenant()->where('channel', CommunicationChannel::Sms)->count(),
            'email' => CommunicationTemplate::query()->forTenant()->where('channel', CommunicationChannel::Email)->count(),
            'whatsapp' => CommunicationTemplate::query()->forTenant()->where('channel', CommunicationChannel::WhatsApp)->count(),
            'inactive' => CommunicationTemplate::query()->forTenant()->where('status', CommunicationTemplateStatus::Inactive)->count(),
        ];

        $categoryGroups = $this->categoryGroupSummary($templates);

        $bootstrap = [
            'routes' => [
                'show' => route('admin.communications.templates.show', ['template' => '__ID__']),
                'versions' => route('admin.communications.templates.versions', ['template' => '__ID__']),
                'compare' => route('admin.communications.templates.compare', ['template' => '__ID__']),
                'preview' => route('admin.communications.templates.preview', ['template' => '__ID__']),
                'restore' => route('admin.communications.templates.restore', ['template' => '__ID__']),
                'store' => route('admin.communications.templates.store'),
                'update' => route('admin.communications.templates.update', ['template' => '__ID__']),
            ],
            'can' => [
                'create' => $request->user()->can('communications.templates.create'),
                'edit' => $request->user()->can('communications.templates.edit'),
                'versionView' => $request->user()->can('communications.templates.version_view'),
                'restore' => $request->user()->can('communications.templates.restore'),
            ],
            'options' => [
                'channels' => $this->templates->channelOptions(),
                'types' => $this->templates->typeOptions(),
                'statuses' => $this->templates->statusOptions(),
                'categories' => $this->templates->categoryOptions(),
            ],
            'variables' => $this->variables->definitions(),
            'sampleData' => $this->variables->sampleData(),
            'categoryGroups' => config('communication_template_registry.groups', []),
            'templates' => $templates->map(fn (CommunicationTemplate $t) => $this->templates->templatePayload($t))->values(),
            'activeFilters' => $request->only(['channel', 'category', 'status', 'group', 'view']),
        ];

        return view('admin.communications.templates.index', compact('summary', 'categoryGroups', 'templates', 'bootstrap'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('create', CommunicationTemplate::class);

        $validated = $this->validateTemplate($request);
        $companyId = $this->requireCompanyId();

        $template = $this->templates->create($validated, $request->user(), $companyId);

        if ($request->wantsJson()) {
            return response()->json([
                'template' => $this->templates->templatePayload($template),
            ]);
        }

        return redirect()
            ->route('admin.communications.templates.index')
            ->with('status', __('Template created.'));
    }

    public function show(CommunicationTemplate $template): JsonResponse
    {
        $this->authorize('view', $template);

        $template->load(['creator', 'updater']);

        return response()->json([
            'template' => $this->templates->templatePayload($template),
        ]);
    }

    public function update(Request $request, CommunicationTemplate $template): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $template);

        $validated = $this->validateTemplate($request, $template);

        $template = $this->templates->update($template, $validated, $request->user());

        if ($request->wantsJson()) {
            return response()->json([
                'template' => $this->templates->templatePayload($template),
            ]);
        }

        return redirect()
            ->route('admin.communications.templates.index')
            ->with('status', __('Template updated.'));
    }

    public function versions(CommunicationTemplate $template): JsonResponse
    {
        $this->authorize('viewVersions', $template);

        $versions = $template->versions()
            ->with('creator')
            ->orderByDesc('version_number')
            ->get()
            ->map(fn (CommunicationTemplateVersion $v) => $this->templates->versionPayload($v));

        return response()->json(['versions' => $versions]);
    }

    public function compare(Request $request, CommunicationTemplate $template): JsonResponse
    {
        $this->authorize('viewVersions', $template);

        $request->validate([
            'left_id' => ['required', 'integer'],
            'right_id' => ['required', 'integer'],
        ]);

        $left = $template->versions()->whereKey($request->integer('left_id'))->firstOrFail();
        $right = $template->versions()->whereKey($request->integer('right_id'))->firstOrFail();

        return response()->json($this->templates->compare($left, $right));
    }

    public function restore(Request $request, CommunicationTemplate $template): JsonResponse
    {
        $this->authorize('restore', $template);

        $request->validate([
            'version_id' => ['required', 'integer'],
            'change_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $version = $template->versions()->whereKey($request->integer('version_id'))->firstOrFail();

        $template = $this->templates->restore(
            $template,
            $version,
            $request->user(),
            $request->input('change_notes'),
        );

        return response()->json([
            'template' => $this->templates->templatePayload($template),
        ]);
    }

    public function preview(Request $request, CommunicationTemplate $template): JsonResponse
    {
        $this->authorize('view', $template);

        $request->validate([
            'data' => ['nullable', 'array'],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
        ]);

        $data = array_merge(
            $this->variables->sampleData(),
            $request->input('data', []),
        );

        $subject = $request->input('subject', $template->subject);
        $body = $request->input('body', $template->body);

        return response()->json(
            $this->renderer->render($subject, $body, $data),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateTemplate(Request $request, ?CommunicationTemplate $existing = null): array
    {
        $companyId = $this->requireCompanyId();

        $channel = CommunicationChannel::from($request->input('channel', $existing?->channel->value ?? CommunicationChannel::Sms->value));

        $rules = [
            'code' => [
                'required',
                'string',
                'max:80',
                'alpha_dash',
                Rule::unique('communication_templates', 'code')
                    ->where('company_id', $companyId)
                    ->ignore($existing?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(CommunicationTemplateCategory::class)],
            'channel' => ['required', Rule::enum(CommunicationChannel::class)],
            'template_type' => ['required', Rule::enum(CommunicationTemplateType::class)],
            'subject' => [
                $this->templates->requiresSubject($channel) ? 'required' : 'nullable',
                'string',
                'max:255',
            ],
            'body' => ['required', 'string'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::enum(CommunicationTemplateStatus::class)],
            'change_notes' => ['nullable', 'string', 'max:500'],
        ];

        if ($existing !== null) {
            unset($rules['code']);
        }

        return $request->validate($rules);
    }

    /**
     * @return list<string>
     */
    protected function categoriesForGroup(string $group): array
    {
        $groups = config('communication_template_registry.groups', []);

        return $groups[$group]['categories'] ?? [];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, CommunicationTemplate>  $templates
     * @return list<array{key: string, label: string, count: int}>
     */
    protected function categoryGroupSummary($templates): array
    {
        $groups = config('communication_template_registry.groups', []);
        $summary = [];

        foreach ($groups as $key => $group) {
            $count = $templates->filter(
                fn (CommunicationTemplate $t) => in_array($t->category->value, $group['categories'], true),
            )->count();

            $summary[] = [
                'key' => $key,
                'label' => $group['label'],
                'count' => $count,
            ];
        }

        return $summary;
    }
}
