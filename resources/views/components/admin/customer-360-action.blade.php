@props(['customer'])

@php
    $has360 = \Illuminate\Support\Facades\Route::has('admin.crm.customers.360');
@endphp

@if ($has360)
    <a href="{{ route('admin.crm.customers.360', $customer) }}" {{ $attributes->merge(['class' => 'erp-btn-secondary text-xs']) }}>
        {{ __('View 360') }}
    </a>
@else
    <span {{ $attributes->merge(['class' => 'inline-flex cursor-not-allowed items-center rounded-md border border-erp-border bg-slate-50 px-3 py-1.5 text-xs text-slate-400']) }} title="{{ __('Customer 360 profile') }}">
        {{ __('View 360') }} — {{ __('Coming Soon') }}
    </span>
@endif
