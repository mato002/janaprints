@php
    $presentation = $presentation ?? null;
    $message = $message ?? ($presentation['message'] ?? null);
    $detail = $detail ?? ($presentation['detail'] ?? null);
@endphp

<div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-rose-900">
    <p class="text-sm font-medium">{{ $message ?? __('Something went wrong while processing this form.') }}</p>
    @if ($detail)
        <p class="mt-2 text-xs text-rose-700">{{ $detail }}</p>
    @endif
</div>
