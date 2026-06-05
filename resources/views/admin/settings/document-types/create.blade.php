@php
    $scopeQuery = array_filter([
        'company_id' => $companyId,
        'branch_id' => $branchId,
    ]);
    $backUrl = route('admin.settings.document-types.index', $scopeQuery);
    $documentType = new \App\Models\Platform\DocumentTypeDefinition();
@endphp

<x-admin-layout
    :title="__('Create Document Type')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('Document Types'), 'url' => $backUrl],
        ['label' => __('Create')],
    ]"
>
    @include('admin.settings.partials.hub-toolbar', [
        'title' => __('Create Document Type'),
        'description' => __('Register a new ERP document type for centralized governance.'),
        'backUrl' => $backUrl,
    ])

    <x-admin.card>
        <form method="POST" action="{{ route('admin.settings.document-types.store') }}">
            @csrf
            <input type="hidden" name="company_id" value="{{ $companyId }}">
            @if ($branchId)
                <input type="hidden" name="branch_id" value="{{ $branchId }}">
            @endif

            @include('admin.settings.document-types.partials.form')

            <div class="mt-8 flex justify-end gap-3">
                <a href="{{ $backUrl }}" class="erp-btn erp-btn--ghost">{{ __('Cancel') }}</a>
                <button type="submit" class="erp-btn erp-btn--primary">{{ __('Create Document Type') }}</button>
            </div>
        </form>
    </x-admin.card>
</x-admin-layout>
