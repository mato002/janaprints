@php
    $validationMessages = $errors->any()
        ? $errors->all()
        : array_filter([(string) (session('modal_error') ?? '')]);
@endphp

@if (count($validationMessages) > 0)
    <div class="hidden" data-erp-validation-errors aria-hidden="true">
        @foreach ($validationMessages as $error)
            <span data-erp-validation-message>{{ $error }}</span>
        @endforeach
    </div>
@endif
