@php
    $validationMessages = $validationMessages
        ?? (($errors ?? null)?->any() ? $errors->all() : []);
    $validationMessages = is_array($validationMessages)
        ? array_values(array_filter($validationMessages, fn ($message) => filled($message)))
        : [];
    if ($validationMessages === [] && filled(session('modal_error'))) {
        $validationMessages = [(string) session('modal_error')];
    }
    $validationPresentation = $validationPresentation ?? session('form_error_presentation');
@endphp

@if (count($validationMessages) > 0)
    <div
        class="hidden"
        data-erp-validation-errors
        aria-hidden="true"
        @if (! empty($validationPresentation['category']))
            data-erp-validation-category="{{ $validationPresentation['category'] }}"
        @endif
        @if (! empty($validationPresentation['category_label']))
            data-erp-validation-category-label="{{ $validationPresentation['category_label'] }}"
        @endif
    >
        @foreach ($validationMessages as $error)
            <span data-erp-validation-message>{{ $error }}</span>
        @endforeach
    </div>
@endif
