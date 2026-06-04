<?php

namespace App\Support\Communications\Inbox;

use App\Enums\InboxAssignmentAction;
use App\Enums\InboxAuditEventType;
use App\Enums\InboxConversationStatus;
use App\Models\Communications\Inbox\CommunicationConversation;
use App\Models\Communications\Inbox\CommunicationConversationAssignment;
use App\Models\User;

class InboxAssignmentService
{
    public function __construct(
        protected InboxConversationService $conversations,
        protected InboxAuditService $audit,
    ) {}

    public function assign(CommunicationConversation $conversation, int $toUserId, int $actorId, ?string $note = null): void
    {
        $from = $conversation->assigned_user_id;
        $conversation->update(['assigned_user_id' => $toUserId]);

        $this->record($conversation, $from ? InboxAssignmentAction::Reassign : InboxAssignmentAction::Assign, $from, $toUserId, $actorId, $note);
        $this->audit->record($conversation, InboxAuditEventType::AssignmentChanged, $actorId, [
            'summary' => __('Assigned to :name', ['name' => User::find($toUserId)?->name]),
        ]);
    }

    public function assignDepartment(CommunicationConversation $conversation, ?int $departmentId, int $actorId): void
    {
        $conversation->update(['assigned_department_id' => $departmentId]);
        $this->record($conversation, InboxAssignmentAction::AssignDepartment, null, null, $actorId, null, null, $departmentId);
        $this->audit->record($conversation, InboxAuditEventType::AssignmentChanged, $actorId, [
            'summary' => __('Department assignment updated'),
        ]);
    }

    public function assignBranch(CommunicationConversation $conversation, ?int $branchId, int $actorId): void
    {
        $conversation->update(['branch_id' => $branchId]);
        $this->record($conversation, InboxAssignmentAction::AssignBranch, null, null, $actorId, null, $branchId);
        $this->audit->record($conversation, InboxAuditEventType::AssignmentChanged, $actorId, [
            'summary' => __('Branch assignment updated'),
        ]);
    }

    public function takeOwnership(CommunicationConversation $conversation, int $userId): void
    {
        $conversation->update([
            'owner_user_id' => $userId,
            'assigned_user_id' => $userId,
        ]);

        $this->record($conversation, InboxAssignmentAction::TakeOwnership, null, $userId, $userId);
        $this->audit->record($conversation, InboxAuditEventType::AssignmentChanged, $userId, [
            'summary' => __('Took ownership'),
        ]);
    }

    public function release(CommunicationConversation $conversation, int $actorId): void
    {
        $from = $conversation->assigned_user_id;
        $conversation->update(['assigned_user_id' => null, 'owner_user_id' => null]);

        $this->record($conversation, InboxAssignmentAction::Release, $from, null, $actorId);
        $this->audit->record($conversation, InboxAuditEventType::AssignmentChanged, $actorId, [
            'summary' => __('Released ownership'),
        ]);
    }

    public function escalate(CommunicationConversation $conversation, int $actorId): void
    {
        $from = $conversation->status;
        $conversation->update([
            'is_escalated' => true,
            'escalated_at' => now(),
            'status' => InboxConversationStatus::Escalated,
        ]);

        $this->conversations->recordStatus($conversation, $from, InboxConversationStatus::Escalated, 'escalated', $actorId);
        $this->record($conversation, InboxAssignmentAction::Escalate, $conversation->assigned_user_id, null, $actorId);
        $this->audit->record($conversation, InboxAuditEventType::EscalationCreated, $actorId, [
            'summary' => __('Conversation escalated'),
        ]);
    }

    public function addWatcher(CommunicationConversation $conversation, int $userId, int $actorId): void
    {
        $watchers = $conversation->watcher_user_ids ?? [];
        if (! in_array($userId, $watchers, true)) {
            $watchers[] = $userId;
            $conversation->update(['watcher_user_ids' => $watchers]);
        }

        $this->record($conversation, InboxAssignmentAction::AddWatcher, null, $userId, $actorId);
        $this->audit->record($conversation, InboxAuditEventType::WatcherChanged, $actorId, [
            'summary' => __('Watcher added'),
        ]);
    }

    public function removeWatcher(CommunicationConversation $conversation, int $userId, int $actorId): void
    {
        $watchers = array_values(array_filter(
            $conversation->watcher_user_ids ?? [],
            fn ($id) => (int) $id !== $userId,
        ));
        $conversation->update(['watcher_user_ids' => $watchers ?: null]);

        $this->record($conversation, InboxAssignmentAction::RemoveWatcher, null, $userId, $actorId);
        $this->audit->record($conversation, InboxAuditEventType::WatcherChanged, $actorId, [
            'summary' => __('Watcher removed'),
        ]);
    }

    public function setStatus(CommunicationConversation $conversation, InboxConversationStatus $status, int $actorId): void
    {
        $from = $conversation->status;
        $updates = ['status' => $status];
        if ($status === InboxConversationStatus::Closed) {
            $updates['closed_at'] = now();
        }
        if ($status === InboxConversationStatus::Resolved) {
            $updates['resolved_at'] = now();
        }
        if ($status === InboxConversationStatus::Open) {
            $updates['closed_at'] = null;
            $updates['resolved_at'] = null;
            $updates['waiting_since'] = now();
        }
        $conversation->update($updates);
        $this->conversations->recordStatus($conversation, $from, $status, 'status_change', $actorId);
        $this->audit->record($conversation, InboxAuditEventType::StatusChanged, $actorId, [
            'summary' => $from->value.' → '.$status->value,
        ]);
    }

    public function close(CommunicationConversation $conversation, int $actorId): void
    {
        $this->setStatus($conversation, InboxConversationStatus::Closed, $actorId);
    }

    public function reopen(CommunicationConversation $conversation, int $actorId): void
    {
        $this->setStatus($conversation, InboxConversationStatus::Open, $actorId);
    }

    protected function record(
        CommunicationConversation $conversation,
        InboxAssignmentAction $action,
        ?int $fromUserId,
        ?int $toUserId,
        int $createdBy,
        ?string $note = null,
        ?int $branchId = null,
        ?int $departmentId = null,
    ): void {
        CommunicationConversationAssignment::query()->create([
            'communication_conversation_id' => $conversation->id,
            'from_user_id' => $fromUserId,
            'to_user_id' => $toUserId,
            'action' => $action,
            'note' => $note,
            'assigned_department_id' => $departmentId,
            'assigned_branch_id' => $branchId,
            'created_by' => $createdBy,
        ]);
    }
}
