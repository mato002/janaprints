@php
    $designerOperator = auth()->user()?->prefersDesignerOperatorMode() ?? false;
    $artworkHomeLabel = $designerOperator ? __('Designer Desk') : __('Artwork');
    $artworkHomeUrl = $designerOperator
        ? route('admin.artwork.desk')
        : route('admin.artwork.dashboard');
@endphp

<x-admin.modal-form
    :title="__('Edit :number', ['number' => $request->request_number])"
    :breadcrumbs="[['label' => $artworkHomeLabel, 'url' => $artworkHomeUrl], ['label' => $request->request_number, 'url' => route('admin.artwork.show', $request)], ['label' => __('Edit')]]"
    maxWidth="2xl"
>
    <x-admin.form-shell :action="route('admin.artwork.update', $request)" method="PUT" class="space-y-4">
        @include('admin.artwork.requests.partials.form', ['request' => $request])
        <x-admin.form-modal-actions>
            <button type="submit" class="erp-btn-primary">{{ __('Save changes') }}</button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
