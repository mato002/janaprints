<?php

namespace App\Enums;

enum InboxAssignmentAction: string
{
    case Assign = 'assign';
    case Reassign = 'reassign';
    case TakeOwnership = 'take_ownership';
    case Release = 'release';
    case Escalate = 'escalate';
    case AddWatcher = 'add_watcher';
    case RemoveWatcher = 'remove_watcher';
    case AssignDepartment = 'assign_department';
    case AssignBranch = 'assign_branch';

    public function label(): string
    {
        return match ($this) {
            self::Assign => __('Assigned'),
            self::Reassign => __('Reassigned'),
            self::TakeOwnership => __('Took ownership'),
            self::Release => __('Released'),
            self::Escalate => __('Escalated'),
            self::AddWatcher => __('Watcher added'),
            self::RemoveWatcher => __('Watcher removed'),
            self::AssignDepartment => __('Assigned to department'),
            self::AssignBranch => __('Assigned to branch'),
        };
    }
}
