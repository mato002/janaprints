<?php

namespace App\Support\Communications\Sms;

use App\Enums\CommunicationChannel;
use App\Enums\SmsCampaignSendMode;
use App\Enums\SmsCampaignStatus;
use App\Enums\SmsDeliveryStatus;
use App\Enums\SmsMessageQueueStatus;
use App\Enums\SmsRecipientSource;
use App\Jobs\Communications\ProcessSmsCampaignJob;
use App\Models\Communications\CommunicationTemplate;
use App\Models\Communications\SmsCampaign;
use App\Models\Communications\SmsMessage;
use App\Models\Communications\SmsRecipient;
use App\Models\User;
use App\Support\Communications\NotificationService;
use App\Support\Communications\TemplateRenderer;
use App\Support\Communications\TemplateVariableEngine;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SmsCampaignService
{
    public function __construct(
        protected SmsRecipientResolver $resolver,
        protected SmsPreviewService $preview,
        protected SmsCreditService $credits,
        protected TemplateRenderer $renderer,
        protected TemplateVariableEngine $variables,
        protected NotificationService $notifications,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $actor, int $companyId): SmsCampaign
    {
        $template = $this->resolveTemplate($data['communication_template_id'] ?? null, $companyId);
        $messageTemplate = $template?->body ?? $data['message_template'];
        $preview = $this->preview->preview($template, $messageTemplate, $data['sample_data'] ?? []);

        return DB::transaction(function () use ($data, $actor, $companyId, $template, $messageTemplate, $preview) {
            $balance = $this->credits->balanceFor($companyId);

            $campaign = SmsCampaign::query()->create([
                'company_id' => $companyId,
                'branch_id' => $data['branch_id'] ?? tenant()->branchId(),
                'department_id' => $data['department_id'] ?? null,
                'campaign_code' => $this->nextCode($companyId),
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'communication_template_id' => $template?->id,
                'message_template' => $messageTemplate,
                'sample_data' => $data['sample_data'] ?? [],
                'send_mode' => $data['send_mode'] ?? SmsCampaignSendMode::Immediate,
                'status' => SmsCampaignStatus::Draft,
                'recipient_source' => $data['recipient_source'],
                'recipient_filters' => $data['recipient_filters'] ?? [],
                'scheduled_at' => $data['scheduled_at'] ?? null,
                'character_count' => $preview['character_count'],
                'estimated_segments' => $preview['segments'],
                'cost_per_sms' => $balance->cost_per_sms,
                'created_by' => $actor->id,
            ]);

            $this->syncRecipients($campaign, $data);

            return $campaign->fresh(['recipients', 'creator', 'template']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(SmsCampaign $campaign, array $data): SmsCampaign
    {
        if (! $campaign->status->canEdit()) {
            throw ValidationException::withMessages(['status' => __('Campaign cannot be edited in its current status.')]);
        }

        $template = $this->resolveTemplate($data['communication_template_id'] ?? $campaign->communication_template_id, $campaign->company_id);
        $messageTemplate = $template?->body ?? ($data['message_template'] ?? $campaign->message_template);
        $preview = $this->preview->preview($template, $messageTemplate, $data['sample_data'] ?? $campaign->sample_data ?? []);

        return DB::transaction(function () use ($campaign, $data, $template, $messageTemplate, $preview) {
            $campaign->fill([
                'name' => $data['name'] ?? $campaign->name,
                'description' => $data['description'] ?? $campaign->description,
                'communication_template_id' => $template?->id,
                'message_template' => $messageTemplate,
                'sample_data' => $data['sample_data'] ?? $campaign->sample_data,
                'send_mode' => $data['send_mode'] ?? $campaign->send_mode,
                'recipient_source' => $data['recipient_source'] ?? $campaign->recipient_source,
                'recipient_filters' => $data['recipient_filters'] ?? $campaign->recipient_filters,
                'scheduled_at' => $data['scheduled_at'] ?? $campaign->scheduled_at,
                'branch_id' => $data['branch_id'] ?? $campaign->branch_id,
                'department_id' => $data['department_id'] ?? $campaign->department_id,
                'character_count' => $preview['character_count'],
                'estimated_segments' => $preview['segments'],
            ]);
            $campaign->save();

            if (isset($data['recipient_filters']) || isset($data['manual_recipients'])) {
                $campaign->recipients()->delete();
                $this->syncRecipients($campaign, $data);
            }

            $this->recalculateCosts($campaign);

            return $campaign->fresh(['recipients', 'creator', 'template']);
        });
    }

    public function approve(SmsCampaign $campaign, User $actor): SmsCampaign
    {
        $campaign->update([
            'approved_by' => $actor->id,
            'approved_at' => now(),
        ]);

        return $campaign->fresh();
    }

    public function queue(SmsCampaign $campaign, User $actor): SmsCampaign
    {
        if (! $campaign->status->canQueue()) {
            throw ValidationException::withMessages(['status' => __('Only draft campaigns can be queued.')]);
        }

        if ($campaign->recipients()->count() === 0) {
            throw ValidationException::withMessages(['recipients' => __('Add at least one recipient.')]);
        }

        $this->recalculateCosts($campaign);
        $requiredCredits = (float) $campaign->estimated_cost;
        $balance = $this->credits->balanceFor($campaign->company_id);

        if ((float) $balance->remaining_credits < $requiredCredits) {
            throw ValidationException::withMessages([
                'credits' => __('Insufficient credits. Required: :required, available: :available', [
                    'required' => $requiredCredits,
                    'available' => $balance->remaining_credits,
                ]),
            ]);
        }

        $campaign->update([
            'status' => SmsCampaignStatus::Queued,
            'queued_at' => now(),
            'sent_by' => $actor->id,
            'scheduled_by' => $campaign->send_mode === SmsCampaignSendMode::Scheduled ? $actor->id : null,
        ]);

        $delay = $campaign->send_mode === SmsCampaignSendMode::Scheduled && $campaign->scheduled_at
            ? $campaign->scheduled_at
            : now();

        ProcessSmsCampaignJob::dispatch($campaign->id)->delay($delay);

        return $campaign->fresh();
    }

    public function cancel(SmsCampaign $campaign): SmsCampaign
    {
        if (! $campaign->status->canCancel()) {
            throw ValidationException::withMessages(['status' => __('Campaign cannot be cancelled.')]);
        }

        $campaign->update(['status' => SmsCampaignStatus::Cancelled]);

        $campaign->messages()
            ->whereIn('queue_status', [SmsMessageQueueStatus::Queued, SmsMessageQueueStatus::Processing])
            ->update(['queue_status' => SmsMessageQueueStatus::Cancelled]);

        return $campaign->fresh();
    }

    public function buildMessages(SmsCampaign $campaign): void
    {
        DB::transaction(function () use ($campaign) {
            $campaign->update([
                'status' => SmsCampaignStatus::Sending,
                'started_at' => now(),
                'sent_at' => now(),
            ]);

            $template = $campaign->template;
            $balance = $this->credits->balanceFor($campaign->company_id);

            foreach ($campaign->recipients as $recipient) {
                $data = array_merge(
                    $this->variables->sampleData(),
                    $campaign->sample_data ?? [],
                    $recipient->variable_data ?? [],
                );

                $rendered = $this->renderer->render(null, $campaign->message_template, $data);
                $segments = app(SmsSegmentCalculator::class)->calculate($rendered['body']);

                SmsMessage::query()->create([
                    'sms_campaign_id' => $campaign->id,
                    'sms_recipient_id' => $recipient->id,
                    'company_id' => $campaign->company_id,
                    'branch_id' => $campaign->branch_id,
                    'phone_number' => $recipient->phone_number,
                    'message_body' => $rendered['body'],
                    'queue_status' => SmsMessageQueueStatus::Queued,
                    'delivery_status' => SmsDeliveryStatus::Queued,
                    'segments_count' => $segments['segments'],
                    'character_count' => $segments['characters'],
                    'credit_cost' => (float) $balance->cost_per_sms * $segments['segments'],
                ]);
            }

            $campaign->update(['total_recipients' => $campaign->recipients()->count()]);
        });
    }

    public function finalizeCampaign(SmsCampaign $campaign): void
    {
        $sent = $campaign->messages()->where('queue_status', SmsMessageQueueStatus::Sent)->count();
        $failed = $campaign->messages()->where('queue_status', SmsMessageQueueStatus::Failed)->count();
        $actualCost = (float) $campaign->messages()
            ->where('queue_status', SmsMessageQueueStatus::Sent)
            ->sum('credit_cost');

        $campaign->update([
            'status' => SmsCampaignStatus::Completed,
            'completed_at' => now(),
            'sent_count' => $sent,
            'failed_count' => $failed,
            'actual_cost' => $actualCost,
        ]);

        $this->notifications->create([
            'company_id' => $campaign->company_id,
            'recipient_user_id' => $campaign->created_by,
            'type' => \App\Enums\NotificationType::SmsCampaignCompleted,
            'title' => __('SMS campaign completed'),
            'body' => __(':name finished. :sent sent, :failed failed.', [
                'name' => $campaign->name,
                'sent' => $sent,
                'failed' => $failed,
            ]),
            'action_url' => route('admin.communications.sms.campaigns.show', $campaign),
            'priority' => \App\Enums\NotificationPriority::Normal,
            'created_by' => $campaign->sent_by,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function syncRecipients(SmsCampaign $campaign, array $data): void
    {
        $source = $campaign->recipient_source instanceof SmsRecipientSource
            ? $campaign->recipient_source
            : SmsRecipientSource::from($campaign->recipient_source);

        $rows = $this->resolver->resolve(
            $source,
            $campaign->company_id,
            $campaign->recipient_filters ?? [],
            $data['manual_recipients'] ?? [],
        );

        foreach ($rows as $row) {
            SmsRecipient::query()->create([
                'sms_campaign_id' => $campaign->id,
                ...$row,
                'variable_data' => $row['variable_data'],
            ]);
        }

        $this->recalculateCosts($campaign->fresh(['recipients']));
    }

    protected function recalculateCosts(SmsCampaign $campaign): void
    {
        $count = max(1, $campaign->recipients()->count());
        $segments = max(1, (int) $campaign->estimated_segments);
        $costPer = (float) $campaign->cost_per_sms;

        $campaign->update([
            'total_recipients' => $campaign->recipients()->count(),
            'estimated_cost' => round($count * $segments * $costPer, 2),
        ]);
    }

    protected function resolveTemplate(?int $templateId, int $companyId): ?CommunicationTemplate
    {
        if ($templateId === null) {
            return null;
        }

        return CommunicationTemplate::query()
            ->where('company_id', $companyId)
            ->where('channel', CommunicationChannel::Sms)
            ->findOrFail($templateId);
    }

    protected function nextCode(int $companyId): string
    {
        $count = SmsCampaign::query()->where('company_id', $companyId)->count() + 1;

        return 'SMS-'.now()->format('ymd').'-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}
