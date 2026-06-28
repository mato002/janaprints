<?php

namespace App\Policies;

use App\Enums\ArtworkRequestStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Artwork\ArtworkVersion;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class ArtworkVersionPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user, ArtworkRequest $request): bool
    {
        return $user->can('artwork.view') && $this->sameTenant($user, $request);
    }

    public function view(User $user, ArtworkVersion $version): bool
    {
        $request = $version->artworkRequest;

        return $user->can('artwork.view') && $this->sameTenant($user, $request);
    }

    public function create(User $user, ArtworkRequest $request): bool
    {
        if (! $user->can('artwork.edit') && ! $user->can('artwork.submit')) {
            return false;
        }

        if (! $this->sameTenant($user, $request)) {
            return false;
        }

        if ($request->lacksUploadedVersion()) {
            return true;
        }

        return in_array($request->status, [
            ArtworkRequestStatus::Requested,
            ArtworkRequestStatus::InDesign,
            ArtworkRequestStatus::RevisionRequested,
        ], true);
    }
}
