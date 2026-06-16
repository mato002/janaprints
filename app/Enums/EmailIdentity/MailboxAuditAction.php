<?php

namespace App\Enums\EmailIdentity;

enum MailboxAuditAction: string
{
    case InvitationSent = 'invitation_sent';
    case ActivationCompleted = 'activation_completed';
    case ActivationCompletedWithoutRole = 'activation_completed_without_role';
    case InvitationResent = 'activation_invitation_resent';
    case ActivationRegenerated = 'activation_regenerated';
    case OnboardingSmsSkipped = 'onboarding_sms_skipped';
    case OnboardingSmsSent = 'onboarding_sms_sent';
    case OnboardingSmsFailed = 'onboarding_sms_failed';
    case SenderFallbackUsed = 'sender_fallback_used';
    case CompanyMailboxCreated = 'company_mailbox_created';
    case CompanyMailboxDeleted = 'company_mailbox_deleted';
    case CompanyMailboxPasswordUpdated = 'company_mailbox_password_updated';
    case CompanyMailboxQuotaUpdated = 'company_mailbox_quota_updated';
}
