<?php

namespace App\Support\Communications\Inbox;

use App\Enums\InboxConversationStatus;
use App\Models\Communications\Inbox\CommunicationConversation;
use App\Models\Communications\Inbox\CommunicationConversationAssignment;
use App\Models\User;
use Illuminate\Support\Collection;

class InboxTeamPerformanceService
{
    /**
     * @return array<string, mixed>
     */
    public function report(int $companyId, ?int $branchId = null): array
    {
        $base = CommunicationConversation::query()->where('company_id', $companyId);
        if ($branchId) {
            $base->where('branch_id', $branchId);
        }

        $users = User::query()->where('company_id', $companyId)->orderBy('name')->get();

        $byUser = $users->map(function (User $user) use ($companyId) {
            $handled = CommunicationConversationAssignment::query()
                ->whereHas('conversation', fn ($q) => $q->where('company_id', $companyId))
                ->where('to_user_id', $user->id)
                ->count();
            $assigned = CommunicationConversation::query()
                ->where('company_id', $companyId)
                ->where('assigned_user_id', $user->id)
                ->whereIn('status', InboxConversationStatus::activeValues())
                ->count();
            $escalated = CommunicationConversation::query()
                ->where('company_id', $companyId)
                ->where('assigned_user_id', $user->id)
                ->where('is_escalated', true)
                ->count();

            return [
                'user' => $user->name,
                'conversations_handled' => $handled,
                'assigned_load' => $assigned,
                'escalation_rate' => $handled > 0 ? round(($escalated / $handled) * 100, 1) : 0,
                'avg_response_minutes' => null,
                'avg_resolution_minutes' => null,
                'satisfaction' => __('—'),
            ];
        });

        return [
            'by_user' => $byUser,
            'totals' => [
                'active' => (clone $base)->whereIn('status', InboxConversationStatus::activeCases())->count(),
                'escalated' => (clone $base)->where('is_escalated', true)->count(),
                'unassigned' => (clone $base)->whereNull('assigned_user_id')
                    ->whereIn('status', InboxConversationStatus::activeValues())->count(),
            ],
        ];
    }
}
