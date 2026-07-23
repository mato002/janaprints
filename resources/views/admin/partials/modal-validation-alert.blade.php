@if ($errors->any())
    <x-admin.alert variant="danger" class="mb-4" data-erp-validation-errors>
        <p class="font-medium">{{ $title ?? __('Unable to save. Please correct the highlighted fields.') }}</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-admin.alert>
@elseif (session('modal_error'))
    <x-admin.alert variant="danger" class="mb-4" data-erp-validation-errors data-erp-modal-error>
        <p class="font-medium">{{ __('Unable to save. Please review the form and try again.') }}</p>
        <p class="mt-2 text-sm">{{ session('modal_error') }}</p>
    </x-admin.alert>
@endif
