<?php

namespace App\Policies;

use App\Enums\ArtworkRequestStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;
use App\Policies\Concerns\ChecksWorkflowAttempt;

class ArtworkRequestPolicy
{
    use ChecksCrmTenant, ChecksWorkflowAttempt;

    public function viewAny(User $user): bool
    {
        return $user->can('artwork.view');
    }

    public function view(User $user, ArtworkRequest $request): bool
    {
        return $user->can('artwork.view') && $this->sameTenant($user, $request);
    }

    public function create(User $user): bool
    {
        return $user->can('artwork.create');
    }

    public function update(User $user, ArtworkRequest $request): bool
    {
        return $user->can('artwork.edit')
            && $this->sameTenant($user, $request)
            && $request->status->isEditable();
    }

    public function delete(User $user, ArtworkRequest $request): bool
    {
        return $user->can('artwork.delete')
            && $this->sameTenant($user, $request)
            && $request->status === ArtworkRequestStatus::Requested;
    }

    public function assign(User $user, ArtworkRequest $request): bool
    {
        return $this->canAttemptWorkflow(
            $user,
            $request,
            'artwork.assign',
            fn (ArtworkRequest $record) => in_array($record->status, [
                ArtworkRequestStatus::Approved,
                ArtworkRequestStatus::Rejected,
                ArtworkRequestStatus::Submitted,
            ], true),
        );
    }

    /**
     * Designer self-claim — only open (unassigned) jobs, so two designers never take the same work.
     */
    public function claim(User $user, ArtworkRequest $request): bool
    {
        if (! $user->can('artwork.edit') || ! $this->sameTenant($user, $request)) {
            return false;
        }

        if ($request->assigned_designer_id !== null) {
            return false;
        }

        return ! in_array($request->status, [
            ArtworkRequestStatus::Approved,
            ArtworkRequestStatus::Rejected,
            ArtworkRequestStatus::Submitted,
        ], true);
    }

    public function submit(User $user, ArtworkRequest $request): bool
    {
        return $this->canAttemptWorkflow(
            $user,
            $request,
            'artwork.submit',
            fn (ArtworkRequest $record) => in_array($record->status, [
                ArtworkRequestStatus::Approved,
                ArtworkRequestStatus::Rejected,
                ArtworkRequestStatus::Submitted,
            ], true),
        );
    }

    public function approve(User $user, ArtworkRequest $request): bool
    {
        return $this->canAttemptWorkflow(
            $user,
            $request,
            'artwork.approve',
            fn (ArtworkRequest $record) => in_array($record->status, [
                ArtworkRequestStatus::Approved,
                ArtworkRequestStatus::Rejected,
            ], true),
        );
    }

    public function startDesign(User $user, ArtworkRequest $request): bool
    {
        return $this->canAttemptWorkflow(
            $user,
            $request,
            'artwork.edit',
            fn (ArtworkRequest $record) => in_array($record->status, [
                ArtworkRequestStatus::Approved,
                ArtworkRequestStatus::Rejected,
            ], true) || (
                $record->status === ArtworkRequestStatus::Submitted
                && $record->currentVersionRecord() !== null
            ),
        );
    }
}
