<?php

namespace App\Enums\EmailIdentity;

enum MailboxStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Suspended = 'suspended';
    case Archived = 'archived';
}
