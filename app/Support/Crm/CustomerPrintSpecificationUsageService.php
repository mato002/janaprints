<?php

namespace App\Support\Crm;

use App\Enums\SalesOrderStatus;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerPrintSpecification;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionSession;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CustomerPrintSpecificationUsageService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function batchUsageMetrics(Collection $specifications): array
    {
        if ($specifications->isEmpty()) {
            return [];
        }

        $specIds = $specifications->pluck('id')->all();
        $cancelled = [SalesOrderStatus::Draft->value, SalesOrderStatus::Cancelled->value];

        $orderStats = SalesOrder::query()
            ->whereIn('customer_print_specification_id', $specIds)
            ->whereNotIn('status', $cancelled)
            ->selectRaw('customer_print_specification_id')
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('COALESCE(SUM(total_amount), 0) as total_revenue')
            ->selectRaw('MAX(order_date) as last_ordered_at')
            ->groupBy('customer_print_specification_id')
            ->get()
            ->keyBy('customer_print_specification_id');

        $itemStats = SalesOrderItem::query()
            ->join('sales_orders', 'sales_orders.id', '=', 'sales_order_items.sales_order_id')
            ->whereIn('sales_order_items.customer_print_specification_id', $specIds)
            ->whereNotIn('sales_orders.status', $cancelled)
            ->selectRaw('sales_order_items.customer_print_specification_id')
            ->selectRaw('AVG(sales_order_items.quantity) as average_quantity')
            ->groupBy('sales_order_items.customer_print_specification_id')
            ->get()
            ->keyBy('customer_print_specification_id');

        $lastPrices = DB::table('sales_order_items')
            ->join('sales_orders', 'sales_orders.id', '=', 'sales_order_items.sales_order_id')
            ->whereIn('sales_order_items.customer_print_specification_id', $specIds)
            ->whereNotIn('sales_orders.status', $cancelled)
            ->select([
                'sales_order_items.customer_print_specification_id',
                'sales_order_items.unit_price',
                'sales_orders.order_date',
            ])
            ->orderByDesc('sales_orders.order_date')
            ->orderByDesc('sales_orders.id')
            ->get()
            ->unique('customer_print_specification_id')
            ->keyBy('customer_print_specification_id');

        $lastProduced = ProductionJobCard::query()
            ->whereIn('customer_print_specification_id', $specIds)
            ->selectRaw('customer_print_specification_id, MAX(COALESCE(actual_end_date, updated_at)) as last_produced_at')
            ->groupBy('customer_print_specification_id')
            ->pluck('last_produced_at', 'customer_print_specification_id');

        $metrics = [];

        foreach ($specifications as $spec) {
            $orders = $orderStats->get($spec->id);
            $items = $itemStats->get($spec->id);
            $price = $lastPrices->get($spec->id);

            $metrics[$spec->id] = [
                'orders_count' => (int) ($orders->orders_count ?? 0),
                'total_revenue' => (float) ($orders->total_revenue ?? 0),
                'last_ordered_at' => $orders->last_ordered_at ?? null,
                'last_produced_at' => $lastProduced[$spec->id] ?? null,
                'average_quantity' => $items ? round((float) $items->average_quantity, 3) : null,
                'last_selling_price' => $price ? (float) $price->unit_price : null,
            ];
        }

        return $metrics;
    }

    /**
     * @return array<string, mixed>
     */
    public function usageMetrics(CustomerPrintSpecification $spec): array
    {
        $batch = $this->batchUsageMetrics(collect([$spec]));

        return $batch[$spec->id] ?? $this->emptyMetrics();
    }

    /**
     * @return array<string, mixed>
     */
    public function usageHistory(CustomerPrintSpecification $spec, int $perPage = 10): array
    {
        $cancelled = [SalesOrderStatus::Draft, SalesOrderStatus::Cancelled];

        $salesOrders = SalesOrder::query()
            ->forTenant()
            ->where('customer_print_specification_id', $spec->id)
            ->whereNotIn('status', $cancelled)
            ->with(['customer:id,company_name', 'jobCard:id,job_card_number,sales_order_id'])
            ->select([
                'id', 'order_number', 'order_date', 'status', 'total_amount',
                'customer_id', 'is_direct_order', 'repeat_source_sales_order_id',
            ])
            ->latest('order_date')
            ->paginate($perPage, ['*'], 'orders_page');

        $jobCards = ProductionJobCard::query()
            ->forTenant()
            ->where('customer_print_specification_id', $spec->id)
            ->with(['salesOrder:id,order_number'])
            ->select([
                'id', 'job_card_number', 'status', 'sales_order_id',
                'actual_end_date', 'created_at', 'estimated_duration_minutes',
            ])
            ->latest('created_at')
            ->paginate($perPage, ['*'], 'jobs_page');

        $orderIds = SalesOrder::query()
            ->where('customer_print_specification_id', $spec->id)
            ->pluck('id');

        $invoices = CustomerInvoice::query()
            ->forTenant()
            ->whereIn('sales_order_id', $orderIds)
            ->select(['id', 'invoice_number', 'invoice_date', 'status', 'total_amount', 'sales_order_id'])
            ->latest('invoice_date')
            ->paginate($perPage, ['*'], 'invoices_page');

        $repeatOrders = SalesOrder::query()
            ->forTenant()
            ->where('customer_print_specification_id', $spec->id)
            ->whereNotNull('repeat_source_sales_order_id')
            ->whereNotIn('status', $cancelled)
            ->with(['repeatSource:id,order_number'])
            ->select(['id', 'order_number', 'order_date', 'repeat_source_sales_order_id', 'total_amount'])
            ->latest('order_date')
            ->paginate($perPage, ['*'], 'repeat_page');

        $jobCardIds = ProductionJobCard::query()
            ->where('customer_print_specification_id', $spec->id)
            ->pluck('id');

        $sessions = ProductionSession::query()
            ->whereIn('production_job_card_id', $jobCardIds)
            ->with(['jobCard:id,job_card_number', 'operator:id,name'])
            ->select([
                'id', 'production_job_card_id', 'started_at', 'ended_at',
                'good_quantity', 'waste_quantity', 'operator_user_id',
            ])
            ->latest('started_at')
            ->paginate($perPage, ['*'], 'sessions_page');

        $recentActivity = $this->recentActivity($spec, 15);

        return compact('salesOrders', 'jobCards', 'invoices', 'repeatOrders', 'sessions', 'recentActivity');
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function recentActivity(CustomerPrintSpecification $spec, int $limit = 15): array
    {
        $events = [];

        foreach ($spec->salesOrders()->latest('order_date')->limit($limit)->get(['id', 'order_number', 'order_date']) as $order) {
            $events[] = [
                'at' => $order->order_date,
                'label' => __('Sales order :number', ['number' => $order->order_number]),
                'type' => 'sales_order',
                'url' => route('admin.sales-orders.show', $order),
            ];
        }

        foreach ($spec->jobCards()->latest('created_at')->limit($limit)->get(['id', 'job_card_number', 'created_at']) as $job) {
            $events[] = [
                'at' => $job->created_at?->toDateString(),
                'label' => __('Job card :number', ['number' => $job->job_card_number]),
                'type' => 'job_card',
                'url' => route('admin.production.job-cards.show', $job),
            ];
        }

        usort($events, fn (array $a, array $b) => strcmp((string) $b['at'], (string) $a['at']));

        return array_slice($events, 0, $limit);
    }

    /**
     * @return array<string, mixed>
     */
    protected function emptyMetrics(): array
    {
        return [
            'orders_count' => 0,
            'total_revenue' => 0.0,
            'last_ordered_at' => null,
            'last_produced_at' => null,
            'average_quantity' => null,
            'last_selling_price' => null,
        ];
    }
}
