<?php

namespace App\Policies;

use App\Enums\ArtworkRequestStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class ArtworkRequestPolicy
{
    use ChecksCrmTenant;

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
        return $user->can('artwork.assign')
            && $this->sameTenant($user, $request)
            && in_array($request->status, [
                ArtworkRequestStatus::Requested,
                ArtworkRequestStatus::InDesign,
                ArtworkRequestStatus::RevisionRequested,
            ], true);
    }

    public function submit(User $user, ArtworkRequest $request): bool
    {
        return $user->can('artwork.submit')
            && $this->sameTenant($user, $request)
            && $request->status === ArtworkRequestStatus::InDesign
            && $request->current_version > 0;
    }

    public function approve(User $user, ArtworkRequest $request): bool
    {
        return $user->can('artwork.approve')
            && $this->sameTenant($user, $request)
            && $request->status === ArtworkRequestStatus::Submitted
            && $request->current_version > 0;
    }

    public function startDesign(User $user, ArtworkRequest $request): bool
    {
        return $user->can('artwork.edit')
            && $this->sameTenant($user, $request)
            && $request->status === ArtworkRequestStatus::RevisionRequested;
    }
}
