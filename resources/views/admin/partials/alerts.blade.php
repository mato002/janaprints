@if (session('status') || session('success') || session('message'))
    <div hidden data-erp-flash-status>{{ session('status') ?? session('success') ?? session('message') }}</div>
@endif

@if (session('error') || session('danger'))
    <div hidden data-erp-flash-error>{{ session('error') ?? session('danger') }}</div>
@endif

@if (session('warning'))
    <div hidden data-erp-flash-warning>{{ session('warning') }}</div>
@endif

@if (session('info'))
    <div hidden data-erp-flash-info>{{ session('info') }}</div>
@endif

{{-- Legacy / domain-specific flash flags promoted into the same SweetAlert channel --}}
@if (session('inbox_reply_sent'))
    <div hidden data-erp-flash-status>{{ __('Message sent.') }}</div>
@endif

@if (session('inbox_attachment_sent'))
    <div hidden data-erp-flash-status>{{ __('Attachment uploaded.') }}</div>
@endif

@if (($errors ?? null)?->any())
    <div hidden data-erp-validation-errors>
        @foreach ($errors->all() as $error)
            <span>{{ $error }}</span>
        @endforeach
    </div>
@endif
