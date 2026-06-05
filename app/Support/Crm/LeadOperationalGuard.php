<?php

namespace App\Support\Crm;

use App\Models\Crm\Lead;
use App\Models\Sales\Quotation;

class LeadOperationalGuard
{
    public function hasDownstreamLinks(Lead $lead): bool
    {
        if ($lead->customer_id) {
            return true;
        }

        if (Quotation::query()->where('lead_id', $lead->id)->exists()) {
            return true;
        }

        if ($lead->activities()->exists()) {
            return true;
        }

        return false;
    }
}
