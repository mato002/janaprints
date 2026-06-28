<?php

namespace App\Support\Sales;

use App\Enums\SalesOrderBillingType;
use App\Models\Crm\Customer;
use App\Models\Sales\SalesOrder;

class CustomerOrderBillingDefaultsService
{
    public function resolveForCustomer(Customer $customer): array
    {
        $terms = strtolower((string) $customer->payment_terms);

        $billingType = match (true) {
            str_contains($terms, 'deposit') || str_contains($terms, '50') => SalesOrderBillingType::Deposit50,
            str_contains($terms, 'advance') || str_contains($terms, '100') || str_contains($terms, 'prepaid') => SalesOrderBillingType::Advance100,
            default => SalesOrderBillingType::Net30,
        };

        return [
            'billing_type' => $billingType,
            'payment_terms_days' => $billingType->defaultPaymentTermsDays(),
        ];
    }

    public function applyToOrder(SalesOrder $order, ?Customer $customer = null): SalesOrder
    {
        $customer ??= $order->customer;

        if (! $customer) {
            return $order;
        }

        if ($order->billing_type) {
            app(SalesOrderFinancialStatusService::class)->syncDepositAmounts($order);

            return $order->fresh();
        }

        $defaults = $this->resolveForCustomer($customer);
        $order->update($defaults);
        app(SalesOrderFinancialStatusService::class)->syncDepositAmounts($order->fresh());

        return $order->fresh();
    }
}
