<?php

namespace App\Policies;

use App\Models\Assets\AssetDocument;
use App\Models\Assets\FixedAsset;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class AssetDocumentPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user, FixedAsset $asset): bool
    {
        return $user->can('assets.view') && $this->sameTenant($user, $asset);
    }

    public function view(User $user, AssetDocument $document): bool
    {
        return $user->can('assets.view') && $this->sameTenant($user, $document);
    }

    public function upload(User $user, FixedAsset $asset): bool
    {
        return ($user->can('assets.edit') || $user->can('assets.manage'))
            && $this->sameTenant($user, $asset);
    }

    public function archive(User $user, AssetDocument $document): bool
    {
        return ($user->can('assets.edit') || $user->can('assets.manage'))
            && $this->sameTenant($user, $document);
    }
}
