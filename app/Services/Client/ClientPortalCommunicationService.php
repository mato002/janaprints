<?php

namespace App\Services\Client;

use App\Enums\CommunicationTemplateCategory;
use App\Models\Communications\CommunicationLog;
use App\Models\Crm\Customer;
use App\Support\Communications\CommunicationLogService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ClientPortalCommunicationService
{
  /** @var list<CommunicationTemplateCategory> */
  protected array $portalCategories = [
    CommunicationTemplateCategory::ProductionStarted,
    CommunicationTemplateCategory::ProductionCompleted,
    CommunicationTemplateCategory::ReadyForCollection,
    CommunicationTemplateCategory::DispatchStarted,
    CommunicationTemplateCategory::Delivered,
    CommunicationTemplateCategory::InvoiceGenerated,
    CommunicationTemplateCategory::InvoiceOverdue,
    CommunicationTemplateCategory::PaymentReceived,
    CommunicationTemplateCategory::QuotationReady,
  ];

  public function __construct(
    protected CommunicationLogService $logs,
  ) {}

  public function paginateForCustomer(Customer $customer, int $perPage = 15): LengthAwarePaginator
  {
    $categoryValues = collect($this->portalCategories)
      ->map(fn (CommunicationTemplateCategory $category) => $category->value)
      ->all();

    return CommunicationLog::query()
      ->forTenant()
      ->where('company_id', $customer->company_id)
      ->whereHas('recipients', function ($recipientQuery) use ($customer) {
        $recipientQuery
          ->where('recipient_type', 'customer')
          ->where('recipient_id', $customer->id);
      })
      ->with(['recipients', 'template'])
      ->orderByDesc('created_at')
      ->paginate($perPage);
  }

  public function categoryLabel(CommunicationLog $log): string
  {
    $category = $log->template?->category;

    if ($category instanceof CommunicationTemplateCategory) {
      return match ($category) {
        CommunicationTemplateCategory::InvoiceGenerated,
        CommunicationTemplateCategory::InvoiceOverdue,
        CommunicationTemplateCategory::PaymentReceived => __('Invoice notification'),
        CommunicationTemplateCategory::ReadyForCollection,
        CommunicationTemplateCategory::DispatchStarted,
        CommunicationTemplateCategory::Delivered => __('Collection notification'),
        default => __('Order notification'),
      };
    }

    return __('Notification');
  }
}
