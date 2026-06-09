<?php

namespace App\Support\Communications;

use App\Enums\NotificationType;
use App\Models\Crm\LeadFollowUp;
use App\Support\Governance\WorkflowCommunicationService;

class FollowUpDueAutomationService
{
    public function __construct(
        protected NotificationService $notifications,
        protected WorkflowCommunicationService $workflowComms,
    ) {}

    /**
     * @return array{staff_alert: bool, customer_reminder: bool}
     */
    public function process(LeadFollowUp $followUp): array
    {
        $config = config('customer_journey_communications.follow_up_due', []);
        $result = ['staff_alert' => false, 'customer_reminder' => false];

        if (! ($config['enabled'] ?? true)) {
            return $result;
        }

        $followUp->loadMissing(['lead.customer', 'assignee']);

        if (($config['staff_alert'] ?? true) && $followUp->assigned_to) {
            $notification = $this->notifications->create([
                'company_id' => (int) $followUp->company_id,
                'recipient_user_id' => (int) $followUp->assigned_to,
                'type' => NotificationType::QuotationSubmitted,
                'title' => __('Follow-up due'),
                'body' => __('Follow-up for :lead is due now.', [
                    'lead' => $followUp->lead?->lead_name ?? __('Lead'),
                ]),
                'subject_type' => LeadFollowUp::class,
                'subject_id' => $followUp->id,
            ]);

            $result['staff_alert'] = $notification !== null;
        }

        if ($config['customer_reminder'] ?? false) {
            $leadName = $followUp->lead?->lead_name ?? __('your enquiry');
            $emailResult = $this->workflowComms->sendEmail($followUp, [
                'subject' => __('Follow-up reminder'),
                'message' => __('We will follow up shortly regarding :lead.', ['lead' => $leadName]),
            ], $followUp->assignee);

            if (! ($emailResult['skipped'] ?? false)) {
                $result['customer_reminder'] = (bool) ($emailResult['success'] ?? false);
            }
        }

        return $result;
    }
}
