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
use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Procurement\Vendor;
use App\Models\Sales\CustomerInvoice;
use App\Support\Communications\Sms\SmsCampaignService;
use App\Support\Communications\Sms\SmsPreviewService;
use App\Support\Communications\Sms\SmsRecipientResolver;
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
        protected SmsRecipientResolver $recipients,
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

    public function create(Request $request): View
    {
        $this->authorize('create', SmsCampaign::class);

        if ($request->boolean('advanced')) {
            return view('admin.communications.sms.campaigns.create-advanced', $this->formMeta());
        }

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

        $shouldSend = $request->input('intent', $request->input('action')) === 'send';

        if ($shouldSend) {
            $this->authorize('send', $campaign);

            if ($campaign->send_mode === SmsCampaignSendMode::Scheduled && ! $request->user()->can('communications.sms.schedule')) {
                abort(403);
            }

            $this->campaigns->queue($campaign, $request->user());

            return redirect()
                ->route('admin.communications.sms.campaigns.show', $campaign)
                ->with('status', $campaign->send_mode === SmsCampaignSendMode::Scheduled
                    ? __('Campaign scheduled for sending.')
                    : __('Campaign queued — SMS is being sent to recipients.'));
        }

        return redirect()
            ->route('admin.communications.sms.campaigns.show', $campaign)
            ->with('status', __('Campaign saved as draft. Use Send now when ready.'));
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

        $shouldSend = $request->input('intent', $request->input('action')) === 'send';

        if ($shouldSend) {
            $this->authorize('send', $campaign);

            if ($campaign->send_mode === SmsCampaignSendMode::Scheduled && ! $request->user()->can('communications.sms.schedule')) {
                abort(403);
            }

            $this->campaigns->queue($campaign->fresh(), $request->user());

            return redirect()
                ->route('admin.communications.sms.campaigns.show', $campaign)
                ->with('status', $campaign->send_mode === SmsCampaignSendMode::Scheduled
                    ? __('Campaign scheduled for sending.')
                    : __('Campaign queued — SMS is being sent to recipients.'));
        }

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

    public function estimateRecipients(Request $request): JsonResponse
    {
        $this->authorize('create', SmsCampaign::class);

        $validated = $request->validate([
            'recipient_source' => ['required', Rule::enum(SmsRecipientSource::class)],
            'recipient_filters' => ['nullable', 'array'],
            'recipient_filters.ids' => ['nullable', 'array'],
            'recipient_filters.ids.*' => ['integer'],
            'manual_phones' => ['nullable', 'string'],
        ]);

        $source = SmsRecipientSource::from($validated['recipient_source']);
        $filters = $this->sanitizeFilters($validated['recipient_filters'] ?? []);
        $manual = [];

        if (in_array($source, [SmsRecipientSource::Manual, SmsRecipientSource::Imported], true)
            && ! empty($validated['manual_phones'])) {
            $parsed = $this->recipients->parseImportList($validated['manual_phones']);
            $manual = $parsed['recipients'];
        }

        $rows = $this->recipients->resolve(
            $source,
            $this->requireCompanyId(),
            $filters,
            $manual,
        );

        return response()->json([
            'count' => $rows->count(),
            'sample' => $rows->take(5)->values()->all(),
        ]);
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
            'name' => ['nullable', 'string', 'max:255'],
            'communication_template_id' => ['nullable', 'exists:communication_templates,id'],
            'message_template' => ['required_without:communication_template_id', 'string'],
            'sample_data' => ['nullable', 'array'],
            'send_mode' => ['required', Rule::enum(SmsCampaignSendMode::class)],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'recipient_source' => ['required', Rule::enum(SmsRecipientSource::class)],
            'recipient_filters' => ['nullable', 'array'],
            'recipient_filters.ids' => ['nullable', 'array'],
            'recipient_filters.ids.*' => ['integer'],
            'recipient_filters.branch_id' => ['nullable'],
            'recipient_filters.customer_type' => ['nullable', 'string'],
            'recipient_filters.status' => ['nullable', 'string'],
            'recipient_filters.has_outstanding' => ['nullable', 'string'],
            'recipient_filters.department_id' => ['nullable'],
            'recipient_filters.employment_status' => ['nullable', 'string'],
            'recipient_filters.vendor_type' => ['nullable', 'string'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'manual_phones' => ['nullable', 'string'],
        ]);

        $validated['recipient_filters'] = $this->sanitizeFilters($validated['recipient_filters'] ?? []);

        if (blank($validated['name'] ?? null)) {
            unset($validated['name']);
        }

        if (! empty($validated['manual_phones'])) {
            $parsed = $this->recipients->parseImportList($validated['manual_phones']);
            $validated['manual_recipients'] = $parsed['recipients'];
        }

        return $validated;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function sanitizeFilters(array $filters): array
    {
        $clean = [];

        foreach ($filters as $key => $value) {
            if ($key === 'ids') {
                $ids = collect(is_array($value) ? $value : [])
                    ->map(fn ($id) => (int) $id)
                    ->filter(fn (int $id) => $id > 0)
                    ->unique()
                    ->values()
                    ->all();

                if ($ids !== []) {
                    $clean['ids'] = $ids;
                }

                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            $clean[$key] = $value;
        }

        return $clean;
    }

    /**
     * @return array<string, mixed>
     */
    protected function formMeta(): array
    {
        $companyId = $this->requireCompanyId();

        $withPhone = fn (?string $phone): bool => strlen(preg_replace('/\D+/', '', (string) $phone) ?? '') >= 9;

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
            'pickerOptions' => [
                'customers' => $this->customerPickerOptions($companyId, $withPhone),
                'dynamic' => $this->customerPickerOptions($companyId, $withPhone),
                'leads' => Lead::query()
                    ->where('company_id', $companyId)
                    ->whereNotNull('phone')
                    ->orderBy('lead_name')
                    ->get(['id', 'lead_name', 'phone', 'branch_id', 'status'])
                    ->filter(fn (Lead $l) => $withPhone($l->phone))
                    ->map(fn (Lead $l) => [
                        'id' => $l->id,
                        'label' => $l->lead_name,
                        'phone' => $l->phone,
                        'branch_id' => $l->branch_id ? (string) $l->branch_id : '',
                        'status' => $this->enumValue($l->status),
                    ])
                    ->values()
                    ->all(),
                'employees' => Employee::query()
                    ->where('company_id', $companyId)
                    ->where('is_active', true)
                    ->whereNotNull('phone')
                    ->orderBy('first_name')
                    ->orderBy('last_name')
                    ->get(['id', 'first_name', 'last_name', 'phone', 'employee_number', 'department_id', 'employment_status'])
                    ->filter(fn (Employee $e) => $withPhone($e->phone))
                    ->map(fn (Employee $e) => [
                        'id' => $e->id,
                        'label' => trim("{$e->first_name} {$e->last_name}").($e->employee_number ? " ({$e->employee_number})" : ''),
                        'phone' => $e->phone,
                        'department_id' => $e->department_id ? (string) $e->department_id : '',
                        'employment_status' => $this->enumValue($e->employment_status),
                    ])
                    ->values()
                    ->all(),
                'suppliers' => Vendor::query()
                    ->where('company_id', $companyId)
                    ->whereNotNull('phone')
                    ->orderBy('vendor_name')
                    ->get(['id', 'vendor_name', 'phone', 'vendor_type', 'status'])
                    ->filter(fn (Vendor $v) => $withPhone($v->phone))
                    ->map(fn (Vendor $v) => [
                        'id' => $v->id,
                        'label' => $v->vendor_name,
                        'phone' => $v->phone,
                        'vendor_type' => $this->enumValue($v->vendor_type),
                        'status' => $this->enumValue($v->status),
                    ])
                    ->values()
                    ->all(),
            ],
        ];
    }

    /**
     * @param  callable(?string): bool  $withPhone
     * @return list<array<string, mixed>>
     */
    protected function customerPickerOptions(int $companyId, callable $withPhone): array
    {
        $outstandingIds = CustomerInvoice::query()
            ->where('company_id', $companyId)
            ->where('balance_due', '>', 0)
            ->pluck('customer_id')
            ->flip();

        return Customer::query()
            ->where('company_id', $companyId)
            ->whereNotNull('phone')
            ->orderBy('company_name')
            ->get(['id', 'company_name', 'phone', 'branch_id', 'customer_type', 'status'])
            ->filter(fn (Customer $c) => $withPhone($c->phone))
            ->map(fn (Customer $c) => [
                'id' => $c->id,
                'label' => $c->company_name,
                'phone' => $c->phone,
                'branch_id' => $c->branch_id ? (string) $c->branch_id : '',
                'customer_type' => $this->enumValue($c->customer_type),
                'status' => $this->enumValue($c->status),
                'has_outstanding' => $outstandingIds->has($c->id),
            ])
            ->values()
            ->all();
    }

    protected function enumValue(mixed $value): string
    {
        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        return $value !== null && $value !== '' ? (string) $value : '';
    }
}
