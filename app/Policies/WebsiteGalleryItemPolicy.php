<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WebsiteGalleryItem;

class WebsiteGalleryItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('website.gallery.view');
    }

    public function view(User $user, WebsiteGalleryItem $item): bool
    {
        return $user->can('website.gallery.view');
    }

    public function create(User $user): bool
    {
        return $user->can('website.gallery.create');
    }

    public function update(User $user, WebsiteGalleryItem $item): bool
    {
        return $user->can('website.gallery.edit');
    }

    public function delete(User $user, WebsiteGalleryItem $item): bool
    {
        return $user->can('website.gallery.delete');
    }

    public function publish(User $user, WebsiteGalleryItem $item): bool
    {
        return $user->can('website.gallery.publish');
    }
}
