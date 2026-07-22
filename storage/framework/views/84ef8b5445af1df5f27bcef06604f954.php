<?php
    use App\Enums\CommunicationLogChannel;
    use App\Enums\CommunicationLogStatus;

    $recipientCount = $log->recipients->count();
    $eventCount = $log->deliveryEvents->count();
    $timelineEvents = $log->deliveryEvents->sortBy('created_at')->values();

    $displayBody = app(\App\Support\Hr\PayrollConfidentialityService::class)->communicationLogBodyForViewer($log);
    $messageBodyHtml = nl2br(e($displayBody));
    if ($displayBody) {
        $messageBodyHtml = preg_replace(
            '/(https?:\/\/[^\s<]+)/',
            '<a href="$1" class="comm-log-360__link" target="_blank" rel="noopener noreferrer">$1</a>',
            $messageBodyHtml
        );
    }

    $auditEntries = collect();
    if ($log->creator) {
        $auditEntries->push([
            'action' => __('Created'),
            'user' => $log->creator->name,
            'at' => $log->created_at,
        ]);
    }
    if ($log->sentByUser && $log->sent_at) {
        $auditEntries->push([
            'action' => __('Sent'),
            'user' => $log->sentByUser->name,
            'at' => $log->sent_at,
        ]);
    }
    if ($log->delivered_at) {
        $auditEntries->push([
            'action' => __('Delivered'),
            'user' => __('System'),
            'at' => $log->delivered_at,
        ]);
    }
    if ($log->read_at) {
        $auditEntries->push([
            'action' => __('Read'),
            'user' => __('Recipient'),
            'at' => $log->read_at,
        ]);
    }
    if ($log->failed_at) {
        $auditEntries->push([
            'action' => __('Failed'),
            'user' => __('System'),
            'at' => $log->failed_at,
        ]);
    }
    foreach ($timelineEvents as $event) {
        if ($event->creator) {
            $auditEntries->push([
                'action' => ucfirst(str_replace('_', ' ', $event->event)),
                'user' => $event->creator->name,
                'at' => $event->created_at,
            ]);
        }
    }
    $auditEntries = $auditEntries
        ->unique(fn ($row) => ($row['action'] ?? '').'|'.($row['at']?->timestamp ?? 0))
        ->sortBy(fn ($row) => $row['at']?->timestamp ?? 0)
        ->values();

    $failureCount = $log->status === CommunicationLogStatus::Failed
        ? max(1, $log->deliveryEvents->filter(fn ($e) => str_contains(strtolower((string) $e->event), 'fail'))->count())
        : $log->deliveryEvents->filter(fn ($e) => str_contains(strtolower((string) $e->event), 'fail'))->count();

    $deliveryRateLabel = match (true) {
        in_array($log->status, [CommunicationLogStatus::Delivered, CommunicationLogStatus::Read], true) => '100%',
        $log->status === CommunicationLogStatus::Sent && $log->delivered_at => '100%',
        $log->status === CommunicationLogStatus::Failed => '0%',
        $log->status === CommunicationLogStatus::Queued => '—',
        default => $log->delivered_at ? '100%' : ($log->sent_at ? __('Pending') : '—'),
    };

    $responseLabel = $log->read_at
        ? $log->read_at->diffForHumans()
        : ($log->read_receipt_at ? $log->read_receipt_at->diffForHumans() : __('N/A'));

    $statusTone = match ($log->status) {
        CommunicationLogStatus::Sent, CommunicationLogStatus::Delivered, CommunicationLogStatus::Read => 'success',
        CommunicationLogStatus::Failed => 'danger',
        CommunicationLogStatus::Queued, CommunicationLogStatus::Sending => 'info',
        CommunicationLogStatus::Draft => 'warning',
        default => 'neutral',
    };

    $bubbleTone = match ($log->channel) {
        CommunicationLogChannel::WhatsApp => 'whatsapp',
        CommunicationLogChannel::Email => 'email',
        CommunicationLogChannel::Sms => 'sms',
        default => 'neutral',
    };

    view()->share(compact(
        'recipientCount',
        'eventCount',
        'timelineEvents',
        'messageBodyHtml',
        'auditEntries',
        'failureCount',
        'deliveryRateLabel',
        'responseLabel',
        'statusTone',
        'bubbleTone',
    ));
?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\logs\360\_data.blade.php ENDPATH**/ ?>