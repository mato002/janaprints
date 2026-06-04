<?php

namespace App\Http\Controllers\Admin\Communications\Sms\Concerns;

trait ResolvesSmsTenant
{
    protected function requireCompanyId(): int
    {
        $companyId = tenant()->companyId();

        if ($companyId === null) {
            abort(403, __('Select a company context to manage SMS.'));
        }

        return $companyId;
    }
}
