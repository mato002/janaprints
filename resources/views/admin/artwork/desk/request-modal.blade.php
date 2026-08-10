@php
    $focusPanel = $focusPanel ?? request('panel');
@endphp

<x-admin.modal-form
    :title="$request->request_number"
    maxWidth="5xl"
>
    <div
        class="artwork-request-detail space-y-4"
        @if ($focusPanel === 'versions')
            x-data
            x-init="$nextTick(() => document.getElementById('designer-desk-versions')?.scrollIntoView({ behavior: 'smooth', block: 'start' }))"
        @endif
    >
        @include('admin.artwork.requests.partials.detail-header', [
            'request' => $request,
            'designerOperator' => true,
            'compact' => true,
        ])

        @include('admin.artwork.requests.partials.workflow-panel', [
            'request' => $request,
            'fromDesk' => true,
        ])

        <x-admin.artwork-preview-lightbox>
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                @include('admin.artwork.requests.partials.details-grid', ['request' => $request])
                @include('admin.artwork.requests.partials.versions-panel', [
                    'request' => $request,
                    'fromDesk' => true,
                ])
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                @include('admin.artwork.requests.partials.reference-files-panel', [
                    'request' => $request,
                    'fromDesk' => true,
                ])
                @include('admin.artwork.requests.partials.comments-panel', [
                    'request' => $request,
                    'fromDesk' => true,
                ])
            </div>
        </x-admin.artwork-preview-lightbox>
    </div>
</x-admin.modal-form>
