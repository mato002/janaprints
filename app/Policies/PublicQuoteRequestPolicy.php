<?php

namespace App\Policies;

use App\Models\PublicQuoteRequest;
use App\Models\User;

class PublicQuoteRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('public_leads.quote_requests.view');
    }

    public function view(User $user, PublicQuoteRequest $quoteRequest): bool
    {
        return $user->can('public_leads.quote_requests.view');
    }

    public function update(User $user, PublicQuoteRequest $quoteRequest): bool
    {
        return $user->can('public_leads.quote_requests.manage');
    }
}
