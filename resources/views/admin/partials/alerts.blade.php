@if (session('status'))
    <div hidden data-erp-flash-status>{{ session('status') }}</div>
@endif

@if (session('error'))
    <div hidden data-erp-flash-error>{{ session('error') }}</div>
@endif

@if ($errors->any())
    <div hidden data-erp-validation-errors>
        @foreach ($errors->all() as $error)
            <span>{{ $error }}</span>
        @endforeach
    </div>
@endif
