<?php

namespace App\Support\Communications;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\DomainCommunicationEvent;
use App\Enums\FollowUpStatus;
use App\Models\Communications\CommunicationLog;
use App\Models\Crm\LeadFollowUp;
use App\Models\Sales\CustomerInvoice;
use Illuminate\Support\Collection;

class CommunicationScheduledEventScanner
{
    public function __construct(
        protected CommunicationEventDispatcher $dispatcher,
    ) {}

    /**
     * @param  list<'invoice_overdue'|'follow_up_due'>|null  $only
     * @return array{invoice_overdue: int, follow_up_due: int}
     */
    public function scan(?array $only = null): array
    {
        $results = [
            'invoice_overdue' => 0,
            'follow_up_due' => 0,
        ];

        if ($only === null || in_array('invoice_overdue', $only, true)) {
            $results['invoice_overdue'] = $this->dispatchOverdueInvoices();
        }

        if ($only === null || in_array('follow_up_due', $only, true)) {
            $results['follow_up_due'] = $this->dispatchDueFollowUps();
        }

        return $results;
    }

    protected function dispatchOverdueInvoices(): int
    {
        $count = 0;

        $this->overdueInvoices()->each(function (CustomerInvoice $invoice) use (&$count) {
            if ($this->alreadyDispatched(DomainCommunicationEvent::InvoiceOverdue, 'customer_invoice', $invoice->id)) {
                return;
            }

            $this->dispatcher->dispatch(
                DomainCommunicationEvent::InvoiceOverdue,
                $invoice,
                metadata: [
                    'due_date' => $invoice->due_date?->toDateString(),
                    'balance_due' => (float) $invoice->balance_due,
                ],
            );

            $count++;
        });

        return $count;
    }

    protected function dispatchDueFollowUps(): int
    {
        $count = 0;

        $this->dueFollowUps()->each(function (LeadFollowUp $followUp) use (&$count) {
            if ($this->alreadyDispatched(DomainCommunicationEvent::FollowUpDue, 'lead_follow_up', $followUp->id)) {
                return;
            }

            $this->dispatcher->dispatch(
                DomainCommunicationEvent::FollowUpDue,
                $followUp,
                metadata: [
                    'scheduled_at' => $followUp->scheduled_at?->toIso8601String(),
                ],
            );

            $count++;
        });

        return $count;
    }

    /**
     * @return Collection<int, CustomerInvoice>
     */
    protected function overdueInvoices(): Collection
    {
        return CustomerInvoice::query()
            ->where('status', CustomerInvoiceStatus::Posted)
            ->where('balance_due', '>', 0)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->toDateString())
            ->get();
    }

    /**
     * @return Collection<int, LeadFollowUp>
     */
    protected function dueFollowUps(): Collection
    {
        return LeadFollowUp::query()
            ->where('status', FollowUpStatus::Pending)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();
    }

    protected function alreadyDispatched(
        DomainCommunicationEvent $event,
        string $sourceType,
        int $sourceId,
    ): bool {
        return CommunicationLog::query()
            ->where('template_code', $event->value)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->where('created_at', '>=', now()->startOfDay())
            ->exists();
    }
}
