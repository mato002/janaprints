<?php

namespace App\Http\Controllers\Admin\Communications\Email;

use App\Http\Controllers\Admin\Communications\Concerns\ResolvesCommunicationsTenant;
use App\Http\Controllers\Controller;
use App\Models\Communications\CommunicationTemplate;
use App\Models\Communications\EmailCampaign;
use App\Models\Communications\EmailMessage;
use App\Support\Communications\Email\EmailMessageService;
use App\Support\Communications\TemplateRenderer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailComposerController extends Controller
{
    use ResolvesCommunicationsTenant;

    public function __construct(
        protected EmailMessageService $messages,
        protected TemplateRenderer $renderer,
    ) {}

    public function create(Request $request): View
    {
        $this->authorize('create', EmailCampaign::class);

        $companyId = $this->requireCompanyId();
        $templates = CommunicationTemplate::query()
            ->where('company_id', $companyId)
            ->where('channel', 'email')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('admin.communications.email.compose', [
            'templates' => $templates,
            'to' => $request->get('to'),
            'customer_id' => $request->get('customer_id'),
            'mailbox' => app(\App\Support\Communications\Email\EmailVisibilityService::class)->mailboxSummary($companyId),
        ]);
    }

    public function preview(Request $request): View
    {
        $this->authorize('create', EmailCampaign::class);

        $validated = $request->validate([
            'communication_template_id' => ['nullable', 'exists:communication_templates,id'],
            'subject' => ['required', 'string'],
            'body' => ['required', 'string'],
            'sample_data' => ['nullable', 'array'],
        ]);

        $preview = ['subject' => $validated['subject'], 'body' => $validated['body']];
        if (! empty($validated['communication_template_id'])) {
            $template = CommunicationTemplate::query()->find($validated['communication_template_id']);
            $preview = $this->renderer->render(
                $template->subject,
                $template->body,
                $validated['sample_data'] ?? [],
            );
        }

        return view('admin.communications.email.preview', ['preview' => $preview]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', EmailCampaign::class);

        $validated = $request->validate([
            'to' => ['required', 'string'],
            'cc' => ['nullable', 'string'],
            'bcc' => ['nullable', 'string'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'communication_template_id' => ['nullable', 'exists:communication_templates,id'],
            'save_draft' => ['nullable', 'boolean'],
            'customer_id' => ['nullable', 'integer'],
        ]);

        $to = $this->parseEmails($validated['to']);
        $message = $this->messages->compose(
            $this->requireCompanyId(),
            $request->user()->id,
            [
                'to' => $to,
                'cc' => $this->parseEmails($validated['cc'] ?? ''),
                'bcc' => $this->parseEmails($validated['bcc'] ?? ''),
                'subject' => $validated['subject'],
                'body' => $validated['body'],
                'communication_template_id' => $validated['communication_template_id'] ?? null,
            ],
            sendNow: ! $request->boolean('save_draft'),
        );

        return redirect()
            ->route('admin.communications.email.sent.index')
            ->with('status', $request->boolean('save_draft') ? __('Draft saved.') : __('Email queued.'));
    }

    public function sendDraft(EmailMessage $emailMessage): RedirectResponse
    {
        $this->authorize('create', EmailCampaign::class);
        abort_unless($emailMessage->company_id === $this->requireCompanyId(), 404);

        $this->messages->sendDraft($emailMessage, auth()->id());

        return back()->with('status', __('Email sent.'));
    }

    /**
     * @return list<array{email: string, name?: string}>
     */
    protected function parseEmails(string $raw): array
    {
        if (trim($raw) === '') {
            return [];
        }

        return array_map(
            fn (string $email) => ['email' => trim($email)],
            preg_split('/[,;]+/', $raw) ?: [],
        );
    }
}
