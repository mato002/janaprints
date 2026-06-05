<?php

namespace App\Support\Crm;

use App\Models\Commercial\CommercialComplaint;
use App\Models\Commercial\CommercialSupportTicket;
use App\Models\Crm\Customer;
use App\Models\Pos\PosSale;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\Artwork\ArtworkRequest;

class CustomerOperationalGuard
{
    public function hasOperationalHistory(Customer $customer): bool
    {
        if ($customer->leads()->exists()) {
            return true;
        }

        if ($customer->activities()->exists()) {
            return true;
        }

        if (Quotation::query()->where('customer_id', $customer->id)->exists()) {
            return true;
        }

        if (SalesOrder::query()->where('customer_id', $customer->id)->exists()) {
            return true;
        }

        if (ArtworkRequest::query()->where('customer_id', $customer->id)->exists()) {
            return true;
        }

        if (CustomerInvoice::query()->where('customer_id', $customer->id)->exists()) {
            return true;
        }

        if (CustomerPayment::query()->where('customer_id', $customer->id)->exists()) {
            return true;
        }

        if (CommercialComplaint::query()->where('customer_id', $customer->id)->exists()) {
            return true;
        }

        if (CommercialSupportTicket::query()->where('customer_id', $customer->id)->exists()) {
            return true;
        }

        if (PosSale::query()->where('customer_id', $customer->id)->exists()) {
            return true;
        }

        return false;
    }
}
