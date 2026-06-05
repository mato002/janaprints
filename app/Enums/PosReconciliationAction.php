<?php

namespace App\Enums;

enum PosReconciliationAction: string
{
    case Created = 'created';
    case Submitted = 'submitted';
    case Reviewed = 'reviewed';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
