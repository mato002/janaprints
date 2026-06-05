<?php

namespace App\Enums;

enum WorkflowRuleActionType: string
{
    case CreateDocument = 'create_document';
    case SendNotification = 'send_notification';
    case SendEmail = 'send_email';
    case SendSms = 'send_sms';
    case AssignUser = 'assign_user';
    case ChangeStatus = 'change_status';
    case GenerateTask = 'generate_task';
    case GenerateApproval = 'generate_approval';

    public function label(): string
    {
        return match ($this) {
            self::CreateDocument => __('Create Document'),
            self::SendNotification => __('Send Notification'),
            self::SendEmail => __('Send Email'),
            self::SendSms => __('Send SMS'),
            self::AssignUser => __('Assign User'),
            self::ChangeStatus => __('Change Status'),
            self::GenerateTask => __('Generate Task'),
            self::GenerateApproval => __('Generate Approval'),
        };
    }
}
