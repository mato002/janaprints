@php
    $designerOperator = auth()->user()?->prefersDesignerOperatorMode() ?? false;
    $artworkHomeLabel = $designerOperator ? __('Designer Desk') : __('Artwork');
    $artworkHomeUrl = $designerOperator
        ? route('admin.artwork.desk')
        : route('admin.artwork.dashboard');
@endphp

<x-admin-layout :title="$request->request_number" :breadcrumbs="[['label' => $artworkHomeLabel, 'url' => $artworkHomeUrl], ['label' => $request->request_number]]">
    <div class="artwork-request-detail">
        @include('admin.artwork.requests.partials.detail-header', [
            'request' => $request,
            'designerOperator' => $designerOperator,
        ])

        @include('admin.artwork.requests.partials.workflow-panel', [
            'request' => $request,
            'fromDesk' => false,
        ])

        <x-admin.artwork-preview-lightbox>
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                @include('admin.artwork.requests.partials.details-grid', ['request' => $request])
                @include('admin.artwork.requests.partials.versions-panel', ['request' => $request, 'fromDesk' => false])
            </div>

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                @include('admin.artwork.requests.partials.reference-files-panel', ['request' => $request, 'fromDesk' => false])
                @include('admin.artwork.requests.partials.comments-panel', ['request' => $request, 'fromDesk' => false])
            </div>
        </x-admin.artwork-preview-lightbox>
    </div>
</x-admin-layout>
