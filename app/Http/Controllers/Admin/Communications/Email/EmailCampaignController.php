<?php

namespace App\Http\Controllers\Admin\Communications\Email;

use App\Http\Controllers\Admin\Communications\Concerns\ResolvesCommunicationsTenant;
use App\Http\Controllers\Controller;
use App\Models\Communications\CommunicationTemplate;
use App\Models\Communications\EmailCampaign;
use App\Support\Communications\Email\EmailCampaignService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailCampaignController extends Controller
{
    use ResolvesCommunicationsTenant;

    public function __construct(
        protected EmailCampaignService $campaigns,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', EmailCampaign::class);

        $campaigns = $this->campaigns
            ->query($this->requireCompanyId())
            ->paginate(20);

        return view('admin.communications.email.campaigns.index', compact('campaigns'));
    }

    public function create(): View
    {
        $this->authorize('create', EmailCampaign::class);

        $templates = CommunicationTemplate::query()
            ->where('company_id', $this->requireCompanyId())
            ->where('channel', 'email')
            ->where('status', 'active')
            ->get();

        return view('admin.communications.email.campaigns.create', compact('templates'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', EmailCampaign::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'campaign_type' => ['required', 'string'],
            'subject' => ['required', 'string'],
            'body' => ['required', 'string'],
            'to' => ['required', 'string'],
            'communication_template_id' => ['nullable', 'exists:communication_templates,id'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        $campaign = $this->campaigns->create($this->requireCompanyId(), $request->user()->id, [
            'name' => $validated['name'],
            'campaign_type' => $validated['campaign_type'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'to_emails' => $this->parseEmails($validated['to']),
            'communication_template_id' => $validated['communication_template_id'] ?? null,
            'scheduled_at' => $validated['scheduled_at'] ?? null,
        ]);

        if (! empty($validated['scheduled_at'])) {
            $this->authorize('schedule', EmailCampaign::class);
            $this->campaigns->schedule($campaign, new \DateTimeImmutable($validated['scheduled_at']));
        }

        return redirect()
            ->route('admin.communications.email.campaigns.show', $campaign)
            ->with('status', __('Campaign saved.'));
    }

    public function show(EmailCampaign $emailCampaign): View
    {
        $this->authorize('view', $emailCampaign);

        $emailCampaign->load(['recipients', 'messages', 'creator', 'account']);

        return view('admin.communications.email.campaigns.show', ['campaign' => $emailCampaign]);
    }

    public function send(EmailCampaign $emailCampaign): RedirectResponse
    {
        $this->authorize('send', $emailCampaign);

        $this->campaigns->send($emailCampaign, auth()->id());

        return back()->with('status', __('Campaign sent.'));
    }

    /**
     * @return list<array{email: string}>
     */
    protected function parseEmails(string $raw): array
    {
        return array_map(
            fn (string $email) => ['email' => trim($email)],
            array_filter(preg_split('/[,;]+/', $raw) ?: []),
        );
    }
}
