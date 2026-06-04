@if ($context && ! empty($context['linked_records']))
    @include('admin.communications.inbox.partials.linked-records', [
        'records' => $context['linked_records'],
        'collapsed' => true,
    ])
@else
    <p class="text-sm text-slate-500">{{ __('No linked ERP records.') }}</p>
@endif
