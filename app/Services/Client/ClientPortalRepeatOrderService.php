<?php

namespace App\Services\Client;

use App\Enums\ClientPortalRepeatRequestStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Client\ClientPortalRepeatRequest;
use App\Models\Crm\Customer;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ClientPortalRepeatOrderService
{
  public function paginateEligibleOrders(Customer $customer, int $perPage = 12): LengthAwarePaginator
  {
    return SalesOrder::query()
      ->where('customer_id', $customer->id)
      ->whereNotIn('status', [SalesOrderStatus::Draft, SalesOrderStatus::Cancelled])
      ->with(['items'])
      ->latest('order_date')
      ->paginate($perPage);
  }

  public function requestRepeat(SalesOrder $order, Customer $customer, User $requester, ?string $notes = null): ClientPortalRepeatRequest
  {
    abort_unless((int) $order->customer_id === (int) $customer->id, 404);

    $existing = ClientPortalRepeatRequest::query()
      ->where('customer_id', $customer->id)
      ->where('sales_order_id', $order->id)
      ->where('status', ClientPortalRepeatRequestStatus::Pending)
      ->first();

    if ($existing) {
      return $existing;
    }

    return DB::transaction(function () use ($order, $customer, $requester, $notes) {
      return ClientPortalRepeatRequest::query()->create([
        'company_id' => $customer->company_id,
        'branch_id' => $customer->branch_id,
        'customer_id' => $customer->id,
        'sales_order_id' => $order->id,
        'requested_by' => $requester->id,
        'status' => ClientPortalRepeatRequestStatus::Pending,
        'notes' => $notes,
      ]);
    });
  }
}
