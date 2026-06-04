<?php

namespace App\Http\Controllers\Admin\Communications\Concerns;

trait ResolvesCommunicationsTenant
{
    protected function requireCompanyId(): int
    {
        $companyId = tenant()->companyId();

        if ($companyId === null) {
            abort(403, __('Select a company context to manage communication templates.'));
        }

        return $companyId;
    }
}
