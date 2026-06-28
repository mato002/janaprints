<?php

namespace App\Services\Client;

use App\Models\Crm\Customer;
use App\Support\Export\TabularExportWriter;
use App\Support\Sales\CustomerStatementService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientPortalStatementDocumentService
{
  public function __construct(
    protected CustomerStatementService $statements,
    protected TabularExportWriter $exports,
  ) {}

  /**
   * @param  array{from_date: string, to_date: string}  $filters
   */
  public function build(Customer $customer, array $filters): array
  {
    return $this->statements->build([
      'customer_id' => $customer->id,
      'from_date' => $filters['from_date'],
      'to_date' => $filters['to_date'],
    ]);
  }

  /**
   * @param  array{from_date: string, to_date: string}  $filters
   */
  public function download(Customer $customer, array $filters, string $format = 'pdf'): StreamedResponse
  {
    $report = $this->build($customer, $filters);

    $headers = [
      __('Date'),
      __('Type'),
      __('Reference'),
      __('Description'),
      __('Debit'),
      __('Credit'),
    ];

    $rows = $report['entries']->map(fn ($entry) => [
      $entry->date,
      $entry->type,
      $entry->reference,
      $entry->description,
      $entry->debit > 0 ? number_format($entry->debit, 2, '.', '') : '',
      $entry->credit > 0 ? number_format($entry->credit, 2, '.', '') : '',
    ])->all();

    $basename = 'statement-'.$customer->id.'-'.$filters['from_date'].'-'.$filters['to_date'];
    $subtitle = $report['customer']->company_name.' · '.$filters['from_date'].' — '.$filters['to_date'];

    return $this->exports->download(
      $format,
      $basename,
      $headers,
      $rows,
      __('Account statement'),
      $subtitle,
    );
  }
}
