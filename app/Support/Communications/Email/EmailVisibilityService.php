<?php

namespace App\Support\Communications\Email;

use App\Enums\EmailDeliveryStatus;
use App\Models\Communications\EmailMessage;
use App\Models\Crm\Customer;
use App\Models\Employee;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\Quotation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EmailVisibilityService
{
    /** @var array<string, string> */
    protected array $departmentMailboxMap = [
        'hr' => 'hr',
        'sales' => 'sales',
        'accounts' => 'accounts',
        'production' => 'production',
        'notifications' => 'notifications',
    ];

    public function __construct(
        protected CommunicationEntityLinkResolver $entityLinks,
        protected EmailDeliveryDiagnosticsService $diagnostics,
    ) {}

    /**
     * @return Collection<int, EmailMessage>
     */
    public function forCustomer(Customer $customer, ?string $typeFilter = null, int $limit = 50): Collection
    {
        $quotationIds = Quotation::query()->where('customer_id', $customer->id)->pluck('id');
        $invoiceIds = CustomerInvoice::query()->where('customer_id', $customer->id)->pluck('id');
        $paymentIds = CustomerPayment::query()->where('customer_id', $customer->id)->pluck('id');

        $query = EmailMessage::query()
            ->forTenant()
            ->where('company_id', $customer->company_id)
            ->with(['account'])
            ->where(function (Builder $inner) use ($customer, $quotationIds, $invoiceIds, $paymentIds) {
                if (filled($customer->email)) {
                    $inner->where('to_emails', 'like', '%'.$customer->email.'%');
                }

                foreach ($quotationIds as $id) {
                    $inner->orWhere(fn (Builder $q) => $this->whereEntity($q, 'quotation', (int) $id));
                }

                foreach ($invoiceIds as $id) {
                    $inner->orWhere(fn (Builder $q) => $this->whereEntity($q, 'customer_invoice', (int) $id));
                }

                foreach ($paymentIds as $id) {
                    $inner->orWhere(fn (Builder $q) => $this->whereEntity($q, 'customer_payment', (int) $id));
                }
            });

        $this->applyCustomerTypeFilter($query, $typeFilter);

        return $query
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, EmailMessage>
     */
    public function forJobCard(ProductionJobCard $jobCard, int $limit = 30): Collection
    {
        $jobCard->loadMissing(['deliveryNotes']);

        $query = EmailMessage::query()
            ->forTenant()
            ->where('company_id', $jobCard->company_id)
            ->with(['account'])
            ->where(function (Builder $inner) use ($jobCard) {
                if ($jobCard->quotation_id) {
                    $inner->orWhere(fn (Builder $q) => $this->whereEntity($q, 'quotation', (int) $jobCard->quotation_id));
                }

                if ($jobCard->artwork_request_id) {
                    $inner->orWhere(fn (Builder $q) => $this->whereEntity($q, 'artwork_request', (int) $jobCard->artwork_request_id));
                }

                if ($jobCard->sales_order_id) {
                    $inner->orWhere(fn (Builder $q) => $this->whereEntity($q, 'sales_order', (int) $jobCard->sales_order_id));
                }

                $inner->orWhere(fn (Builder $q) => $this->whereEntity($q, 'production_job_card', (int) $jobCard->id));

                foreach ($jobCard->deliveryNotes as $deliveryNote) {
                    $inner->orWhere(fn (Builder $q) => $this->whereEntity($q, 'delivery_note', (int) $deliveryNote->id));
                }
            });

        return $query
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    public function presentCustomerMessage(EmailMessage $message): array
    {
        $metadata = $message->provider_response['metadata'] ?? [];

        return [
            'id' => $message->id,
            'subject' => $message->subject,
            'type' => $this->customerMessageType($metadata),
            'type_label' => $this->customerMessageTypeLabel($metadata),
            'sender' => $message->account?->from_email,
            'status' => $message->status->value,
            'status_label' => $message->status->label(),
            'status_badge' => $message->status->badgeClass(),
            'date' => $message->sent_at ?? $message->created_at,
            'date_formatted' => ($message->sent_at ?? $message->created_at)?->format('d M Y H:i'),
            'failure_reason' => $message->failure_reason,
            'related_entity' => $this->entityLinks->resolve($metadata),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function presentTimelineEvent(EmailMessage $message): array
    {
        $metadata = $message->provider_response['metadata'] ?? [];
        $related = $this->entityLinks->resolve($metadata);
        $isFailed = in_array($message->status, [EmailDeliveryStatus::Failed, EmailDeliveryStatus::Bounced], true);

        return [
            'at' => $message->sent_at ?? $message->created_at,
            'title' => $this->customerMessageTypeLabel($metadata).' · '.$message->subject,
            'body' => collect([
                $message->account?->from_email,
                $message->status->label(),
                $isFailed ? $message->failure_reason : null,
            ])->filter()->join(' · '),
            'badge' => __('Email'),
            'url' => $related['url'] ?? null,
            'kind' => 'communication',
            'email_message_id' => $message->id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function presentJobCommunication(EmailMessage $message): array
    {
        $metadata = $message->provider_response['metadata'] ?? [];
        $related = $this->entityLinks->resolve($metadata);

        return [
            'title' => $this->jobCommunicationTitle($metadata, $message),
            'description' => collect([
                $message->subject,
                $message->account?->from_email,
                $message->status->label(),
            ])->filter()->join(' · '),
            'event_datetime' => ($message->sent_at ?? $message->created_at)?->toIso8601String(),
            'actor_name' => $message->account?->from_name ?? $message->account?->from_email,
            'source_url' => $related['url'] ?? null,
            'color' => in_array($message->status, [EmailDeliveryStatus::Failed, EmailDeliveryStatus::Bounced], true) ? 'red' : 'sky',
            'category' => 'communications',
            'email_message_id' => $message->id,
        ];
    }

    /**
     * Salesperson mailbox folder counts (no infrastructure health).
     *
     * @return array{sent: int, drafts: int, queued: int, needs_attention: int}
     */
    public function mailboxSummary(int $companyId): array
    {
        $base = EmailMessage::query()->where('company_id', $companyId);

        return [
            'sent' => (clone $base)->whereIn('status', [
                EmailDeliveryStatus::Sent,
                EmailDeliveryStatus::Delivered,
                EmailDeliveryStatus::Opened,
                EmailDeliveryStatus::Clicked,
            ])->count(),
            'drafts' => (clone $base)->where('status', EmailDeliveryStatus::Draft)->count(),
            'queued' => (clone $base)->whereIn('status', [
                EmailDeliveryStatus::Queued,
                EmailDeliveryStatus::Sending,
            ])->count(),
            'needs_attention' => (clone $base)->whereIn('status', [
                EmailDeliveryStatus::Failed,
                EmailDeliveryStatus::Bounced,
            ])->count(),
        ];
    }

    /**
     * Compact customer panel data for an outbound email recipient.
     *
     * @return array{id: int, name: string, email: string|null, type: string|null, url: string}|null
     */
    public function customerContextForMessage(EmailMessage $message): ?array
    {
        $email = collect($message->to_emails)->pluck('email')->first();

        if (! filled($email)) {
            return null;
        }

        $customer = Customer::query()
            ->where('company_id', $message->company_id)
            ->whereRaw('LOWER(email) = ?', [mb_strtolower((string) $email)])
            ->first(['id', 'company_name', 'email', 'customer_type', 'contact_person']);

        if (! $customer) {
            return null;
        }

        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'type' => $customer->customer_type?->label(),
            'url' => route('admin.crm.customers.show', $customer),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function communicationHealth(int $companyId): array
    {
        $base = EmailMessage::query()->where('company_id', $companyId)->where('status', '!=', EmailDeliveryStatus::Draft);
        $recentWindow = (clone $base)->where('created_at', '>=', now()->subDays(7));
        $attempted = (clone $recentWindow)->whereIn('status', [
            EmailDeliveryStatus::Sent,
            EmailDeliveryStatus::Delivered,
            EmailDeliveryStatus::Opened,
            EmailDeliveryStatus::Clicked,
            EmailDeliveryStatus::Failed,
            EmailDeliveryStatus::Bounced,
        ])->count();
        $failed = (clone $recentWindow)->whereIn('status', [EmailDeliveryStatus::Failed, EmailDeliveryStatus::Bounced])->count();
        $failureRate = $attempted > 0 ? round(($failed / $attempted) * 100, 1) : 0.0;

        $queueBacklog = (clone $base)->whereIn('status', [EmailDeliveryStatus::Queued, EmailDeliveryStatus::Sending])->count();
        $diagnostics = $this->diagnostics->forCompany($companyId);
        $smtpAvailable = ($diagnostics['smtp']['status'] ?? '') === 'configured';
        $engineActive = (bool) ($diagnostics['delivery_engine']['active'] ?? false);
        $stuckSending = (int) ($diagnostics['queue']['stuck_sending'] ?? 0);
        $warningBacklog = (int) config('communications.queue.warning_backlog', 10);
        $criticalBacklog = (int) config('communications.queue.critical_backlog', 50);

        $level = 'healthy';
        $label = __('Healthy');

        if (! $smtpAvailable || ! $engineActive || $failureRate >= 20 || $queueBacklog >= $criticalBacklog || $stuckSending > 0) {
            $level = 'critical';
            $label = __('Critical');
        } elseif ($failureRate >= 5 || $queueBacklog >= $warningBacklog || ! ($diagnostics['queue']['active'] ?? true)) {
            $level = 'warning';
            $label = __('Warning');
        }

        return [
            'level' => $level,
            'label' => $label,
            'failure_rate' => $failureRate,
            'queue_backlog' => $queueBacklog,
            'smtp_available' => $smtpAvailable,
            'engine_active' => $engineActive,
            'recent_failures' => $failed,
            'url' => route('admin.communications.email.settings'),
        ];
    }

    /**
     * @return list<array{customer_id: int|null, customer_name: string, email: string, email_count: int, url: string|null}>
     */
    public function topCustomersByEmail(int $companyId, int $limit = 10): array
    {
        $messages = EmailMessage::query()
            ->where('company_id', $companyId)
            ->where('created_at', '>=', now()->startOfMonth())
            ->whereIn('status', [
                EmailDeliveryStatus::Sent,
                EmailDeliveryStatus::Delivered,
                EmailDeliveryStatus::Opened,
                EmailDeliveryStatus::Clicked,
            ])
            ->get(['to_emails', 'provider_response']);

        $counts = [];

        foreach ($messages as $message) {
            $email = collect($message->to_emails)->pluck('email')->first();
            if (! filled($email)) {
                continue;
            }

            $key = strtolower((string) $email);
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        arsort($counts);
        $counts = array_slice($counts, 0, $limit, true);

        $customers = Customer::query()
            ->where('company_id', $companyId)
            ->whereIn(DB::raw('LOWER(email)'), array_keys($counts))
            ->get(['id', 'company_name', 'email']);

        $byEmail = $customers->keyBy(fn (Customer $customer) => strtolower((string) $customer->email));

        $results = [];

        foreach ($counts as $email => $count) {
            $customer = $byEmail->get($email);

            $results[] = [
                'customer_id' => $customer?->id,
                'customer_name' => $customer?->company_name ?? $email,
                'email' => $email,
                'email_count' => $count,
                'url' => $customer ? route('admin.crm.customers.show', $customer) : null,
            ];
        }

        return $results;
    }

    /**
     * @return array<string, array{sent: int, failed: int, queued: int, label: string}>
     */
    public function departmentReport(int $companyId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $departments = [
            'sales' => ['sent' => 0, 'failed' => 0, 'queued' => 0, 'label' => __('Sales')],
            'accounts' => ['sent' => 0, 'failed' => 0, 'queued' => 0, 'label' => __('Accounts')],
            'hr' => ['sent' => 0, 'failed' => 0, 'queued' => 0, 'label' => __('HR')],
            'production' => ['sent' => 0, 'failed' => 0, 'queued' => 0, 'label' => __('Production')],
        ];

        $query = EmailMessage::query()
            ->where('company_id', $companyId)
            ->where('status', '!=', EmailDeliveryStatus::Draft)
            ->with('account');

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        foreach ($query->get() as $message) {
            $department = $this->departmentKeyForMessage($message);

            if (! isset($departments[$department])) {
                continue;
            }

            if (in_array($message->status, [EmailDeliveryStatus::Sent, EmailDeliveryStatus::Delivered, EmailDeliveryStatus::Opened, EmailDeliveryStatus::Clicked], true)) {
                $departments[$department]['sent']++;
            } elseif (in_array($message->status, [EmailDeliveryStatus::Failed, EmailDeliveryStatus::Bounced], true)) {
                $departments[$department]['failed']++;
            } elseif (in_array($message->status, [EmailDeliveryStatus::Queued, EmailDeliveryStatus::Sending], true)) {
                $departments[$department]['queued']++;
            }
        }

        return $departments;
    }

    protected function whereEntity(Builder $query, string $entityType, int $entityId): void
    {
        $query->where('provider_response->metadata->entity_type', $entityType)
            ->where('provider_response->metadata->entity_id', $entityId);
    }

    protected function applyCustomerTypeFilter(Builder $query, ?string $typeFilter): void
    {
        if (! filled($typeFilter)) {
            return;
        }

        match ($typeFilter) {
            'quotations' => $query->where('provider_response->metadata->entity_type', 'quotation'),
            'invoices' => $query->where('provider_response->metadata->entity_type', 'customer_invoice'),
            'receipts' => $query->where('provider_response->metadata->entity_type', 'customer_payment'),
            'general' => $query->where(function (Builder $inner) {
                $inner->whereNull('provider_response->metadata->entity_type')
                    ->orWhereNotIn('provider_response->metadata->entity_type', [
                        'quotation',
                        'customer_invoice',
                        'customer_payment',
                    ]);
            }),
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    protected function customerMessageType(array $metadata): string
    {
        return match ($metadata['entity_type'] ?? 'general') {
            'quotation' => 'quotation',
            'customer_invoice' => 'invoice',
            'customer_payment' => 'receipt',
            default => 'general',
        };
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    protected function customerMessageTypeLabel(array $metadata): string
    {
        return match ($this->customerMessageType($metadata)) {
            'quotation' => __('Quotation emailed'),
            'invoice' => __('Invoice emailed'),
            'receipt' => __('Receipt emailed'),
            default => in_array($metadata['entity_type'] ?? null, [null, ''], true)
                ? __('Customer email sent')
                : (in_array($metadata['entity_type'] ?? '', ['employee', 'user'], true)
                    ? __('Employee email sent')
                    : __('Customer email sent')),
        };
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    protected function jobCommunicationTitle(array $metadata, EmailMessage $message): string
    {
        if (in_array($message->status, [EmailDeliveryStatus::Failed, EmailDeliveryStatus::Bounced], true)) {
            return __('Communication failed');
        }

        return match ($metadata['entity_type'] ?? '') {
            'quotation' => __('Quotation email'),
            'artwork_request' => __('Artwork approval email'),
            'delivery_note' => __('Dispatch email'),
            'production_job_card' => __('Production update email'),
            default => __('Email sent'),
        };
    }

    protected function departmentKeyForMessage(EmailMessage $message): string
    {
        $fromEmail = strtolower((string) ($message->account?->from_email ?? ''));

        foreach ($this->departmentMailboxMap as $department => $mailbox) {
            $address = strtolower((string) (
                config('mailboxes.department.'.$mailbox)
                ?? config('mailboxes.system.'.$mailbox)
                ?? ''
            ));

            if ($address !== '' && $address === $fromEmail) {
                return $department;
            }
        }

        $entityType = (string) ($message->provider_response['metadata']['entity_type'] ?? '');

        return match ($entityType) {
            'quotation' => 'sales',
            'customer_invoice', 'customer_payment' => 'accounts',
            'employee' => 'hr',
            'production_job_card' => 'production',
            default => 'sales',
        };
    }

    /**
     * @return array<string, int>
     */
    public function topSendersByDepartment(int $companyId, ?\DateTimeInterface $since = null): array
    {
        $since ??= now()->startOfMonth();

        $totals = [
            'hr' => 0,
            'sales' => 0,
            'accounts' => 0,
            'production' => 0,
            'notifications' => 0,
        ];

        $messages = EmailMessage::query()
            ->where('company_id', $companyId)
            ->where('created_at', '>=', $since)
            ->whereIn('status', [
                EmailDeliveryStatus::Sent,
                EmailDeliveryStatus::Delivered,
                EmailDeliveryStatus::Opened,
                EmailDeliveryStatus::Clicked,
            ])
            ->with('account')
            ->get();

        foreach ($messages as $message) {
            $department = $this->departmentKeyForMessage($message);

            if ($department === 'hr') {
                $totals['hr']++;
            } elseif ($department === 'sales') {
                $totals['sales']++;
            } elseif ($department === 'accounts') {
                $totals['accounts']++;
            } elseif ($department === 'production') {
                $totals['production']++;
            } else {
                $fromEmail = strtolower((string) ($message->account?->from_email ?? ''));
                $notificationsAddress = strtolower((string) (config('mailboxes.system.notifications') ?? ''));

                if ($notificationsAddress !== '' && $fromEmail === $notificationsAddress) {
                    $totals['notifications']++;
                }
            }
        }

        return $totals;
    }

    /**
     * @return array{customers: int, employees: int}
     */
    public function topRecipientGroups(int $companyId, ?\DateTimeInterface $since = null): array
    {
        $since ??= now()->startOfMonth();

        $messages = EmailMessage::query()
            ->where('company_id', $companyId)
            ->where('created_at', '>=', $since)
            ->whereIn('status', [
                EmailDeliveryStatus::Sent,
                EmailDeliveryStatus::Delivered,
                EmailDeliveryStatus::Opened,
                EmailDeliveryStatus::Clicked,
            ])
            ->get(['provider_response']);

        $customers = 0;
        $employees = 0;

        foreach ($messages as $message) {
            $entityType = (string) ($message->provider_response['metadata']['entity_type'] ?? '');

            if (in_array($entityType, ['employee', 'user'], true)) {
                $employees++;
            } elseif (in_array($entityType, ['quotation', 'customer_invoice', 'customer_payment', 'customer'], true) || $entityType === '') {
                $customers++;
            }
        }

        return [
            'customers' => $customers,
            'employees' => $employees,
        ];
    }
}
