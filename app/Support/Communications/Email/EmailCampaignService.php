<?php

namespace App\Support\Communications\Email;

use App\Enums\EmailCampaignStatus;
use App\Enums\EmailCampaignType;
use App\Enums\EmailDeliveryStatus;
use App\Models\Communications\CommunicationTemplate;
use App\Models\Communications\EmailCampaign;
use App\Models\Communications\EmailRecipient;
use App\Models\Communications\EmailTemplate;
use App\Support\Communications\TemplateRenderer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class EmailCampaignService
{
    public function __construct(
        protected EmailAccountService $accounts,
        protected EmailMessageService $messages,
        protected TemplateRenderer $renderer,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function query(int $companyId, array $filters = []): Builder
    {
        $query = EmailCampaign::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->with(['creator', 'account']);

        if ($status = $filters['status'] ?? null) {
            $query->where('status', $status);
        }

        return $query->orderByDesc('created_at');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(int $companyId, int $userId, array $data): EmailCampaign
    {
        $account = $this->accounts->ensureDefaultAccount($companyId, $userId);
        $body = $data['body'];
        $subject = $data['subject'];

        if (! empty($data['communication_template_id'])) {
            $template = CommunicationTemplate::query()->findOrFail($data['communication_template_id']);
            $rendered = $this->renderer->render($template->subject, $template->body, $data['sample_data'] ?? []);
            $body = $rendered['body'];
            $subject = $rendered['subject'] ?? $subject;
        }

        $toEmails = $this->normalizeEmails($data['to_emails'] ?? []);
        $recipients = $data['recipients'] ?? [];

        return DB::transaction(function () use ($companyId, $userId, $data, $account, $body, $subject, $toEmails, $recipients) {
            $campaign = EmailCampaign::query()->create([
                'company_id' => $companyId,
                'branch_id' => tenant()->branchId(),
                'department_id' => $data['department_id'] ?? null,
                'email_account_id' => $account->id,
                'campaign_code' => $this->nextCode($companyId),
                'name' => $data['name'],
                'campaign_type' => $data['campaign_type'] ?? EmailCampaignType::Single,
                'status' => EmailCampaignStatus::Draft,
                'communication_template_id' => $data['communication_template_id'] ?? null,
                'email_template_id' => $data['email_template_id'] ?? null,
                'subject' => $subject,
                'body' => $body,
                'to_emails' => $toEmails,
                'cc_emails' => $this->normalizeEmails($data['cc_emails'] ?? []),
                'bcc_emails' => $this->normalizeEmails($data['bcc_emails'] ?? []),
                'sample_data' => $data['sample_data'] ?? null,
                'scheduled_at' => $data['scheduled_at'] ?? null,
                'created_by' => $userId,
            ]);

            $allRecipients = $recipients;
            foreach ($toEmails as $entry) {
                $allRecipients[] = [
                    'source_type' => 'manual',
                    'email' => $entry['email'],
                    'display_name' => $entry['name'] ?? null,
                ];
            }

            foreach ($allRecipients as $row) {
                EmailRecipient::query()->create([
                    'email_campaign_id' => $campaign->id,
                    'source_type' => $row['source_type'] ?? 'manual',
                    'source_id' => $row['source_id'] ?? null,
                    'email' => $row['email'],
                    'display_name' => $row['display_name'] ?? null,
                    'variable_data' => $row['variable_data'] ?? null,
                ]);
            }

            $campaign->update(['total_recipients' => $campaign->recipients()->count()]);

            return $campaign->fresh(['recipients']);
        });
    }

    public function send(EmailCampaign $campaign, int $userId): EmailCampaign
    {
        $campaign->update([
            'status' => EmailCampaignStatus::Sending,
            'started_at' => now(),
            'sent_by' => $userId,
        ]);

        $sent = 0;
        $failed = 0;

        foreach ($campaign->recipients as $recipient) {
            try {
                $body = $campaign->body;
                if ($recipient->variable_data) {
                    $rendered = $this->renderer->render($campaign->subject, $campaign->body, $recipient->variable_data);
                    $body = $rendered['body'];
                }
                $this->messages->createForRecipient($recipient, $userId, $campaign->subject, $body);
                $recipient->update(['status' => 'sent']);
                $sent++;
            } catch (\Throwable) {
                $recipient->update(['status' => 'failed']);
                $failed++;
            }
        }

        $campaign->update([
            'status' => EmailCampaignStatus::Completed,
            'completed_at' => now(),
            'sent_at' => now(),
            'sent_count' => $sent,
            'failed_count' => $failed,
            'delivered_count' => $sent,
        ]);

        return $campaign->fresh();
    }

    public function schedule(EmailCampaign $campaign, \DateTimeInterface $at): EmailCampaign
    {
        $campaign->update([
            'status' => EmailCampaignStatus::Scheduled,
            'scheduled_at' => $at,
            'campaign_type' => EmailCampaignType::Scheduled,
        ]);

        return $campaign;
    }

    /**
     * @param  list<string>|list<array{email: string, name?: string}>  $emails
     * @return list<array{email: string, name?: string}>
     */
    protected function normalizeEmails(array $emails): array
    {
        $normalized = [];
        foreach ($emails as $entry) {
            if (is_string($entry)) {
                $normalized[] = ['email' => trim($entry)];
            } elseif (is_array($entry) && ! empty($entry['email'])) {
                $normalized[] = ['email' => trim($entry['email']), 'name' => $entry['name'] ?? null];
            }
        }

        return $normalized;
    }

    protected function nextCode(int $companyId): string
    {
        $count = EmailCampaign::query()->where('company_id', $companyId)->count() + 1;

        return 'EML-'.now()->format('ymd').'-'.str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }
}
