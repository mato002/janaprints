<?php

namespace App\Enums\EmailIdentity;

enum EmployeeActivationStatus: string
{
    case PendingActivation = 'pending_activation';
    case Activated = 'activated';
}
