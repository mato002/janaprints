<?php

use App\Enums\DomainCommunicationEvent;

return [

    /*
    |--------------------------------------------------------------------------
    | Customer journey events (COM-H4 P1)
    |--------------------------------------------------------------------------
    | When a domain event fires, dispatch outbound messages on these channels
    | when templates and integration providers are configured.
    */
    'journey_events' => [
        DomainCommunicationEvent::CustomerCreated->value,
        DomainCommunicationEvent::QuotationSent->value,
        DomainCommunicationEvent::ArtworkApproved->value,
        DomainCommunicationEvent::InvoiceGenerated->value,
        DomainCommunicationEvent::PaymentReceived->value,
        DomainCommunicationEvent::DeliveryCompleted->value,
        DomainCommunicationEvent::InvoiceOverdue->value,
    ],

    'channels' => ['email', 'sms', 'whatsapp'],

    /*
    |--------------------------------------------------------------------------
    | Scheduled engines (COM-H5 / COM-H6)
    |--------------------------------------------------------------------------
    */
    'payment_reminders' => [
        'enabled' => true,
        'customer_channels' => ['email', 'sms', 'whatsapp'],
    ],

    'follow_up_due' => [
        'enabled' => true,
        'staff_alert' => true,
        'customer_reminder' => true,
        'customer_channels' => ['email', 'sms'],
    ],

];
