<?php

namespace App\Services\Client;

use App\Enums\FulfilmentStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Sales\SalesOrder;

class ClientPortalOrderTrackingService
{
  /**
   * @return array{
   *     order_number: string,
   *     status: string,
   *     status_label: string,
   *     expected_completion: ?\Illuminate\Support\Carbon,
   *     stages: list<array{key: string, label: string, state: string}>
   * }
   */
  public function track(SalesOrder $order): array
  {
    $order->loadMissing(['jobCard.fulfilment']);

    $current = $this->resolveCurrentStage($order);
    $stages = $this->buildStages($current);

    return [
      'order_number' => $order->order_number,
      'status' => $current,
      'status_label' => $this->stageLabel($current),
      'expected_completion' => $order->jobCard?->planned_end_date ?? $order->required_date,
      'stages' => $stages,
    ];
  }

  protected function resolveCurrentStage(SalesOrder $order): string
  {
    if (in_array($order->status, [SalesOrderStatus::Delivered, SalesOrderStatus::Closed], true)) {
      return 'delivered';
    }

    $fulfilment = $order->jobCard?->fulfilment;
    if ($fulfilment?->status === FulfilmentStatus::Delivered) {
      return 'delivered';
    }

    if ($fulfilment?->status === FulfilmentStatus::ReadyForCollection
      || $order->status === SalesOrderStatus::ReadyForDispatch
      || $order->jobCard?->status === ProductionJobCardStatus::ReadyForDispatch) {
      return 'ready_for_collection';
    }

    $jobStatus = $order->jobCard?->status;
    if ($jobStatus && in_array($jobStatus, [
      ProductionJobCardStatus::QualityCheck,
      ProductionJobCardStatus::AwaitingCustomerApproval,
    ], true)) {
      return 'quality_check';
    }

    if ($order->status === SalesOrderStatus::InProduction
      || ($jobStatus && in_array($jobStatus, [
        ProductionJobCardStatus::InProduction,
        ProductionJobCardStatus::Outsourced,
        ProductionJobCardStatus::Rework,
        ProductionJobCardStatus::Returned,
      ], true))) {
      return 'in_production';
    }

    return 'queued';
  }

  /**
   * @return list<array{key: string, label: string, state: string}>
   */
  protected function buildStages(string $current): array
  {
    $sequence = ['queued', 'in_production', 'quality_check', 'ready_for_collection', 'delivered'];
    $currentIndex = array_search($current, $sequence, true);

    return collect($sequence)->map(function (string $key) use ($currentIndex, $sequence) {
      $index = array_search($key, $sequence, true);
      $state = 'upcoming';

      if ($index < $currentIndex) {
        $state = 'complete';
      } elseif ($index === $currentIndex) {
        $state = 'current';
      }

      return [
        'key' => $key,
        'label' => $this->stageLabel($key),
        'state' => $state,
      ];
    })->all();
  }

  protected function stageLabel(string $stage): string
  {
    return match ($stage) {
      'queued' => __('Queued'),
      'in_production' => __('In Production'),
      'quality_check' => __('Quality Check'),
      'ready_for_collection' => __('Ready For Collection'),
      'delivered' => __('Delivered'),
      default => ucfirst(str_replace('_', ' ', $stage)),
    };
  }
}
