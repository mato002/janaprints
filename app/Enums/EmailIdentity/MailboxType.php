<?php

namespace App\Enums\EmailIdentity;

enum MailboxType: string
{
    case Corporate = 'corporate';
    case Department = 'department';
    case System = 'system';
    case Shared = 'shared';
}
