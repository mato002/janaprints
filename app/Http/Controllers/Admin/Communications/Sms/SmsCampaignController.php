<?php

namespace App\Http\Controllers\Admin\Communications\Sms;

use App\Enums\CommunicationChannel;
use App\Enums\CommunicationTemplateStatus;
use App\Enums\SmsCampaignSendMode;
use App\Enums\SmsCampaignStatus;
use App\Enums\SmsRecipientSource;
use App\Http\Controllers\Admin\Communications\Sms\Concerns\ResolvesSmsTenant;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Communications\CommunicationTemplate;
use App\Models\Communications\SmsCampaign;
use App\Models\Department;
use App\Support\Communications\Sms\SmsCampaignService;
use App\Support\Communications\Sms\SmsPreviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SmsCampaignController extends Controller
{
    use ResolvesSmsTenant;

    public function __construct(
        protected SmsCampaignService $campaigns,
        protected SmsPreviewService $preview,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', SmsCampaign::class);

        $campaigns = SmsCampaign::query()
            ->forTenant()
            ->with(['creator', 'approver'])
            ->latest()
            ->paginate(15);

        return view('admin.communications.sms.campaigns.index', compact('campaigns'));
    }

    public function create(): View
    {
        $this->authorize('create', SmsCampaign::class);

        return view('admin.communications.sms.campaigns.create', $this->formMeta());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', SmsCampaign::class);

        $campaign = $this->campaigns->create(
            $this->validated($request),
            $request->user(),
            $this->requireCompanyId(),
        );

        return redirect()
            ->route('admin.communications.sms.campaigns.show', $campaign)
            ->with('status', __('Campaign created.'));
    }

    public function show(SmsCampaign $campaign): View
    {
        $this->authorize('view', $campaign);

        $campaign->load(['recipients', 'creator', 'approver', 'sender', 'template', 'branch', 'department']);

        return view('admin.communications.sms.campaigns.show', compact('campaign'));
    }

    public function edit(SmsCampaign $campaign): View
    {
        $this->authorize('update', $campaign);

        return view('admin.communications.sms.campaigns.edit', array_merge(
            ['campaign' => $campaign->load('recipients', 'template')],
            $this->formMeta(),
        ));
    }

    public function update(Request $request, SmsCampaign $campaign): RedirectResponse
    {
        $this->authorize('update', $campaign);

        $this->campaigns->update($campaign, $this->validated($request));

        return redirect()
            ->route('admin.communications.sms.campaigns.show', $campaign)
            ->with('status', __('Campaign updated.'));
    }

    public function preview(Request $request): JsonResponse
    {
        $this->authorize('create', SmsCampaign::class);

        $request->validate([
            'communication_template_id' => ['nullable', 'exists:communication_templates,id'],
            'message_template' => ['required_without:communication_template_id', 'string'],
            'sample_data' => ['nullable', 'array'],
        ]);

        $companyId = $this->requireCompanyId();
        $template = $request->communication_template_id
            ? CommunicationTemplate::query()
                ->where('company_id', $companyId)
                ->where('channel', CommunicationChannel::Sms)
                ->find($request->communication_template_id)
            : null;

        return response()->json(
            $this->preview->preview(
                $template,
                $request->input('message_template', $template?->body ?? ''),
                $request->input('sample_data', []),
            ),
        );
    }

    public function approve(Request $request, SmsCampaign $campaign): RedirectResponse
    {
        $this->authorize('approve', $campaign);

        $this->campaigns->approve($campaign, $request->user());

        return back()->with('status', __('Campaign approved.'));
    }

    public function send(Request $request, SmsCampaign $campaign): RedirectResponse
    {
        $this->authorize('send', $campaign);

        if ($campaign->send_mode === SmsCampaignSendMode::Scheduled && ! $request->user()->can('communications.sms.schedule')) {
            abort(403);
        }

        $this->campaigns->queue($campaign, $request->user());

        return back()->with('status', __('Campaign queued for background sending.'));
    }

    public function cancel(SmsCampaign $campaign): RedirectResponse
    {
        $this->authorize('update', $campaign);

        $this->campaigns->cancel($campaign);

        return back()->with('status', __('Campaign cancelled.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'communication_template_id' => ['nullable', 'exists:communication_templates,id'],
            'message_template' => ['required_without:communication_template_id', 'string'],
            'sample_data' => ['nullable', 'array'],
            'send_mode' => ['required', Rule::enum(SmsCampaignSendMode::class)],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'recipient_source' => ['required', Rule::enum(SmsRecipientSource::class)],
            'recipient_filters' => ['nullable', 'array'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'manual_phones' => ['nullable', 'string'],
        ]);

        if (! empty($validated['manual_phones'])) {
            $validated['manual_recipients'] = collect(preg_split('/[\r\n,;]+/', $validated['manual_phones']))
                ->map(fn ($phone) => ['phone' => trim($phone)])
                ->filter(fn ($r) => $r['phone'] !== '')
                ->values()
                ->all();
        }

        return $validated;
    }

    /**
     * @return array<string, mixed>
     */
    protected function formMeta(): array
    {
        $companyId = $this->requireCompanyId();

        return [
            'templates' => CommunicationTemplate::query()
                ->where('company_id', $companyId)
                ->where('channel', CommunicationChannel::Sms)
                ->where('status', CommunicationTemplateStatus::Active)
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'body']),
            'branches' => Branch::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get(),
            'departments' => Department::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'sources' => SmsRecipientSource::cases(),
            'sendModes' => SmsCampaignSendMode::cases(),
            'statuses' => SmsCampaignStatus::cases(),
        ];
    }
}
