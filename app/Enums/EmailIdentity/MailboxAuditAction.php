<?php

namespace App\Enums\EmailIdentity;

enum MailboxAuditAction: string
{
    case MailboxGenerated = 'mailbox_generated';
    case MailboxCreated = 'mailbox_created';
    case InvitationSent = 'invitation_sent';
    case ActivationCompleted = 'activation_completed';
    case ActivationCompletedWithoutRole = 'activation_completed_without_role';
    case InvitationResent = 'activation_invitation_resent';
    case ActivationRegenerated = 'activation_regenerated';
    case MailboxSuspended = 'mailbox_suspended';
    case OnboardingSmsSkipped = 'onboarding_sms_skipped';
    case OnboardingSmsSent = 'onboarding_sms_sent';
    case OnboardingSmsFailed = 'onboarding_sms_failed';
    case SenderFallbackUsed = 'sender_fallback_used';
}
