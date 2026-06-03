<?php

namespace App\Enums;

enum ArtworkApprovalDecision: string
{
    case Approved = 'approved';
    case Rejected = 'rejected';
    case RevisionRequested = 'revision_requested';
}
