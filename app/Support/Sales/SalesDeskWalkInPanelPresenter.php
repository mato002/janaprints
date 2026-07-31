<?php

namespace App\Support\Sales;

use App\Models\Crm\CustomerPrintSpecification;
use App\Models\Sales\SalesOrder;

/**
 * Step-aware right-rail context for the Sales Desk walk-in wizard.
 * Reuses already-loaded desk payloads — no extra decision-support queries.
 */
class SalesDeskWalkInPanelPresenter
{
    /**
     * @param  array<string, mixed>|null  $customerContext
     * @param  list<array<string, mixed>>  $printSpecifications
     * @param  array<string, mixed>|null  $orderPresentation
     * @param  array<string, mixed>  $deskUrls
     * @return array<string, mixed>
     */
    public function present(
        int $step,
        ?array $customerContext,
        ?CustomerPrintSpecification $specification,
        array $printSpecifications,
        ?SalesOrder $order,
        ?array $orderPresentation,
        array $deskUrls = [],
    ): array {
        return match (true) {
            $step >= 4 && $orderPresentation !== null => $this->releasePanel($orderPresentation, $deskUrls),
            $step === 3 => $this->orderPanel($customerContext, $specification, $orderPresentation, $deskUrls),
            $step === 2 => $this->specificationPanel($customerContext, $specification, $printSpecifications, $deskUrls),
            default => $this->customerPanel($customerContext, $deskUrls),
        };
    }

    /**
     * @param  array<string, mixed>|null  $customerContext
     * @param  array<string, mixed>  $deskUrls
     * @return array<string, mixed>
     */
    protected function customerPanel(?array $customerContext, array $deskUrls): array
    {
        if (! $customerContext) {
            return [
                'mode' => 'customer',
                'title' => __('Customer summary'),
                'empty' => __('Select a customer to see credit status, open quotes, and last order.'),
            ];
        }

        return [
            'mode' => 'customer',
            'title' => __('Customer summary'),
            'name' => $customerContext['name'] ?? null,
            'customer_type' => $customerContext['customer_type'] ?? null,
            'outstanding_balance' => $customerContext['outstanding_balance'] ?? null,
            'credit_limit' => $customerContext['credit_limit'] ?? null,
            'overdue_amount' => $customerContext['overdue_amount'] ?? null,
            'collection_risk' => $customerContext['collection_risk'] ?? null,
            'phone' => $customerContext['phone'] ?? null,
            'email' => $customerContext['email'] ?? null,
            'contact_person' => $customerContext['contact_person'] ?? null,
            'last_order' => $customerContext['last_order'] ?? null,
            'open_quotations' => array_slice($customerContext['open_quotations'] ?? [], 0, 3),
            'artwork_pending_count' => $customerContext['artwork_pending_count'] ?? 0,
            'warnings' => $customerContext['warnings'] ?? [],
            'customer_360_url' => $deskUrls['customer_360'] ?? ($customerContext['show_url'] ?? null),
            'edit_url' => $deskUrls['edit_customer'] ?? ($customerContext['edit_url'] ?? null),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $customerContext
     * @param  list<array<string, mixed>>  $printSpecifications
     * @param  array<string, mixed>  $deskUrls
     * @return array<string, mixed>
     */
    protected function specificationPanel(
        ?array $customerContext,
        ?CustomerPrintSpecification $specification,
        array $printSpecifications,
        array $deskUrls,
    ): array {
        $selected = null;

        if ($specification) {
            $artwork = $specification->activeArtworkVersion;
            $selected = [
                'name' => $specification->name,
                'code' => $specification->specification_code,
                'product' => $specification->inventoryItem?->item_name,
                'artwork_label' => $artwork?->versionLabel(),
                'artwork_name' => $artwork?->artwork_name,
                'has_artwork' => $artwork !== null,
                'default_quantity' => $specification->default_quantity,
                'default_unit_price' => $specification->default_unit_price !== null
                    ? number_format((float) $specification->default_unit_price, 2)
                    : null,
            ];
        }

        $recent = collect($printSpecifications)
            ->take(5)
            ->map(fn (array $spec) => [
                'name' => $spec['name'] ?? '—',
                'code' => $spec['specification_code'] ?? null,
                'product' => $spec['product_name'] ?? null,
                'artwork' => $spec['current_artwork_label'] ?? null,
                'has_artwork' => (bool) ($spec['has_active_artwork'] ?? false),
                'price' => $spec['default_unit_price'] ?? null,
            ])
            ->values()
            ->all();

        return [
            'mode' => 'specification',
            'title' => __('Specification context'),
            'customer_name' => $customerContext['name'] ?? null,
            'selected' => $selected,
            'saved_count' => count($printSpecifications),
            'recent' => $recent,
            'customer_360_url' => $deskUrls['customer_360'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $customerContext
     * @param  array<string, mixed>|null  $orderPresentation
     * @param  array<string, mixed>  $deskUrls
     * @return array<string, mixed>
     */
    protected function orderPanel(
        ?array $customerContext,
        ?CustomerPrintSpecification $specification,
        ?array $orderPresentation,
        array $deskUrls,
    ): array {
        return [
            'mode' => 'order',
            'title' => __('Order context'),
            'customer_name' => $customerContext['name'] ?? null,
            'specification_name' => $specification?->name,
            'product' => $specification?->inventoryItem?->item_name,
            'default_quantity' => $specification?->default_quantity,
            'default_unit_price' => $specification?->default_unit_price !== null
                ? number_format((float) $specification->default_unit_price, 2)
                : null,
            'artwork_label' => $specification?->activeArtworkVersion?->versionLabel(),
            'has_artwork' => $specification?->activeArtworkVersion !== null,
            'outstanding_balance' => $customerContext['outstanding_balance'] ?? null,
            'credit_limit' => $customerContext['credit_limit'] ?? null,
            'warnings' => array_values(array_filter(
                $customerContext['warnings'] ?? [],
                fn (array $warning) => in_array($warning['severity'] ?? '', ['danger', 'warning'], true),
            )),
            'customer_360_url' => $deskUrls['customer_360'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $orderPresentation
     * @param  array<string, mixed>  $deskUrls
     * @return array<string, mixed>
     */
    protected function releasePanel(array $orderPresentation, array $deskUrls): array
    {
        $checks = collect($orderPresentation['readiness']['checks'] ?? []);
        $byKey = $checks->keyBy('key');

        $dashboard = [
            $this->dashboardRow('customer', __('Customer'), $byKey, ['customer', 'order_status']),
            $this->dashboardRow('specification', __('Specification'), $byKey, ['production_spec', 'spec_approval', 'job_card']),
            $this->dashboardRow('artwork', __('Artwork'), $byKey, ['artwork']),
            $this->dashboardRow('materials', __('Materials'), $byKey, ['materials']),
            $this->dashboardRow('machine', __('Routing'), $byKey, ['routing', 'queue_route']),
            $this->dashboardRow('commercial', __('Commercial'), $byKey, ['commercial', 'due_date']),
        ];

        $failed = collect($dashboard)
            ->filter(fn (array $row) => ! ($row['passed'] ?? false))
            ->values()
            ->all();

        $actionableWarnings = collect($orderPresentation['readiness']['warnings'] ?? [])
            ->map(fn (string $message) => ['severity' => 'warning', 'message' => $message])
            ->merge(
                $checks
                    ->filter(fn (array $check) => ! ($check['passed'] ?? false))
                    ->map(fn (array $check) => [
                        'severity' => $check['severity'] ?? 'blocker',
                        'title' => $check['label'] ?? null,
                        'message' => $check['message'] ?? $check['label'] ?? null,
                    ])
            )
            ->unique(fn (array $item) => ($item['title'] ?? '').'|'.($item['message'] ?? ''))
            ->values()
            ->all();

        $production = $orderPresentation['production'] ?? null;

        return [
            'mode' => 'release',
            'title' => __('Production readiness'),
            'ready' => (bool) ($orderPresentation['readiness']['ready'] ?? false),
            'snapshot' => [
                'order_number' => $orderPresentation['order_number'] ?? null,
                'customer' => $orderPresentation['customer']['label'] ?? null,
                'product' => $orderPresentation['product_name'] ?? $orderPresentation['specification_name'] ?? null,
                'quantity' => $orderPresentation['quantity'] ?? null,
                'due' => $orderPresentation['required_date'] ?? null,
                'priority' => $orderPresentation['priority'] ?? null,
                'total' => $orderPresentation['total_amount'] ?? null,
            ],
            'dashboard' => $dashboard,
            'failed' => $failed,
            'warnings' => $actionableWarnings,
            'estimate' => [
                'department' => $production['department_label'] ?? $production['production_type'] ?? null,
                'work_center' => $production['work_center'] ?? null,
                'queue_status' => $production['queue_status'] ?? null,
                'job_card_number' => $orderPresentation['job_card_number'] ?? null,
            ],
            'customer_360_url' => $deskUrls['customer_360'] ?? null,
            'show_url' => $orderPresentation['show_url'] ?? null,
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<string, array<string, mixed>>  $byKey
     * @param  list<string>  $keys
     * @return array<string, mixed>
     */
    protected function dashboardRow(string $id, string $label, $byKey, array $keys): array
    {
        $relevant = collect($keys)
            ->map(fn (string $key) => $byKey->get($key))
            ->filter()
            ->values();

        if ($relevant->isEmpty()) {
            return [
                'id' => $id,
                'label' => $label,
                'passed' => true,
                'severity' => 'info',
                'message' => __('Not applicable'),
            ];
        }

        $blocker = $relevant->first(
            fn (array $check) => ! ($check['passed'] ?? false) && ($check['severity'] ?? '') === 'blocker'
        );
        $warning = $relevant->first(
            fn (array $check) => ! ($check['passed'] ?? false)
        );
        $failed = $blocker ?? $warning;
        $passed = $failed === null;

        return [
            'id' => $id,
            'label' => $label,
            'passed' => $passed,
            'severity' => $passed
                ? 'ok'
                : (($failed['severity'] ?? 'blocker') === 'warning' ? 'warning' : 'blocker'),
            'message' => $failed['message'] ?? ($passed ? null : ($failed['label'] ?? null)),
            'detail_label' => $failed['label'] ?? null,
        ];
    }
}
