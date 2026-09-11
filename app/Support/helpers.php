<?php

use App\Support\Pagination\PreferredPageSize;
use App\Support\TenantContext;

if (! function_exists('tenant')) {
    function tenant(): TenantContext
    {
        return app(TenantContext::class);
    }
}

if (! function_exists('preferred_per_page')) {
    function preferred_per_page(?int $explicit = null): int
    {
        return PreferredPageSize::apply($explicit);
    }
}
