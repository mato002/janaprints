@php
    $moduleOptions = [
        '' => __('All modules'),
        'sales' => __('Sales'),
        'hr' => __('HR'),
        'storefront' => __('Storefront'),
        'system' => __('System'),
    ];
@endphp

<x-admin.card :padding="false" class="mb-4">
    <x-admin.index-toolbar :action="request()->url()" :reset-url="request()->url()" compact>
        @if (($viewMode ?? '') === 'queued')
            <x-admin.filter-pill-select name="status" :label="__('Status')" :selected="$filters['status'] ?? ''">
                <option value="">{{ __('All statuses') }}</option>
                @foreach ([App\Enums\EmailDeliveryStatus::Queued, App\Enums\EmailDeliveryStatus::Sending] as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </x-admin.filter-pill-select>
        @elseif (($viewMode ?? '') === 'inbox')
            <x-admin.filter-pill-select name="status" :label="__('Status')" :selected="$filters['status'] ?? ''">
                <option value="">{{ __('All statuses') }}</option>
                @foreach ([App\Enums\EmailDeliveryStatus::Failed, App\Enums\EmailDeliveryStatus::Bounced] as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </x-admin.filter-pill-select>
        @else
            <x-admin.filter-pill-select name="status" :label="__('Status')" :selected="$filters['status'] ?? ''">
                <option value="">{{ __('All statuses') }}</option>
                @foreach ([App\Enums\EmailDeliveryStatus::Sent, App\Enums\EmailDeliveryStatus::Delivered, App\Enums\EmailDeliveryStatus::Opened, App\Enums\EmailDeliveryStatus::Clicked] as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </x-admin.filter-pill-select>
        @endif
        <x-admin.filter-pill-select name="sender" :label="__('Sender')" :selected="$filters['sender'] ?? ''">
            <option value="">{{ __('All senders') }}</option>
            @foreach ($accounts as $account)
                <option value="{{ $account->id }}" @selected(($filters['sender'] ?? '') == $account->id)>{{ $account->from_email }}</option>
            @endforeach
        </x-admin.filter-pill-select>
        <x-admin.filter-pill-select name="module" :label="__('Module')" :selected="$filters['module'] ?? ''">
            @foreach ($moduleOptions as $value => $label)
                <option value="{{ $value }}" @selected(($filters['module'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </x-admin.filter-pill-select>
        <x-admin.filter-pill-date name="date_from" :label="__('From date')" :value="$filters['date_from'] ?? ''" />
        <x-admin.filter-pill-date name="date_to" :label="__('To date')" :value="$filters['date_to'] ?? ''" />
    </x-admin.index-toolbar>
</x-admin.card>
