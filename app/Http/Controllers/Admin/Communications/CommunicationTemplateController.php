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
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CommunicationTemplate::class);

        $templates = CommunicationTemplate::query()
            ->forTenant()
            ->with(['creator', 'updater'])
            ->orderBy('name')
            ->get();

        $bootstrap = [
            'routes' => [
                'show' => route('admin.communications.templates.show', ['template' => '__ID__'], absolute: false),
                'versions' => route('admin.communications.templates.versions', ['template' => '__ID__'], absolute: false),
                'compare' => route('admin.communications.templates.compare', ['template' => '__ID__'], absolute: false),
                'restore' => route('admin.communications.templates.restore', ['template' => '__ID__'], absolute: false),
                'store' => route('admin.communications.templates.store', absolute: false),
                'update' => route('admin.communications.templates.update', ['template' => '__ID__'], absolute: false),
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
            'categoryGroups' => collect(config('communication_template_registry.groups', []))
                ->map(fn (array $group, string $key) => [
                    'key' => $key,
                    'label' => $group['label'],
                    'categories' => $group['categories'] ?? [],
                ])
                ->values()
                ->all(),
            'templates' => $templates->map(fn (CommunicationTemplate $t) => $this->templates->templatePayload($t))->values(),
            'activeFilters' => [
                'channel' => $request->string('channel')->toString(),
                'status' => $request->string('status')->toString(),
                'group' => $request->string('group')->toString(),
                'view' => $request->string('view')->toString(),
            ],
        ];

        return view('admin.communications.templates.index', compact('bootstrap'));
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

    /**
     * @return array<string, mixed>
     */
    protected function validateTemplate(Request $request, ?CommunicationTemplate $existing = null): array
    {
        $channel = CommunicationChannel::from($request->input('channel', $existing?->channel->value ?? CommunicationChannel::Sms->value));

        $rules = [
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
            'status' => ['nullable', Rule::enum(CommunicationTemplateStatus::class)],
            'change_notes' => ['nullable', 'string', 'max:500'],
        ];

        $validated = $request->validate($rules);

        if ($existing === null) {
            $validated['status'] ??= CommunicationTemplateStatus::Active->value;
        }

        return $validated;
    }
}
