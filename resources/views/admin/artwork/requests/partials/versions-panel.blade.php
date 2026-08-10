@php
    $fromDesk = ($fromDesk ?? false) || request('from') === 'designer-desk';
    $uploadAnchor = $uploadAnchor ?? 'artwork-versions-upload';
    $hasVersions = $request->versions->isNotEmpty();
    $accept = '.pdf,.ai,.psd,.cdr,.svg,.png,.jpg,.jpeg';
@endphp

<div class="artwork-detail-card" id="{{ $uploadAnchor === 'artwork-versions-upload' ? 'designer-desk-versions' : $uploadAnchor }}">
    <h2 class="artwork-detail-card__title">{{ __('Versions') }}</h2>

    @if ($hasVersions)
        <div class="mb-2">
            @foreach ($request->versions as $version)
                <div class="artwork-detail-version-row">
                    <div class="min-w-0">
                        <strong>v{{ $version->version_number }}</strong> — {{ $version->original_name }}
                        <span class="text-slate-500">({{ $version->uploader?->name }})</span>
                        @if ($version->notes)
                            <p class="mt-0.5 text-slate-600">{{ $version->notes }}</p>
                        @endif
                    </div>
                    @if ($version->isPreviewable())
                        <button
                            type="button"
                            class="erp-btn-ghost shrink-0 text-xs"
                            data-preview-url="{{ $version->previewUrl() }}"
                            data-preview-title="{{ $version->original_name }}"
                            data-preview-pdf="{{ $version->mime_type === 'application/pdf' ? '1' : '0' }}"
                            @click="show($el.dataset.previewUrl, $el.dataset.previewTitle, $el.dataset.previewPdf === '1')"
                        >{{ __('Preview') }}</button>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="artwork-detail-empty">
            <span class="artwork-detail-empty__icon" aria-hidden="true">↑</span>
            <p class="artwork-detail-empty__title">{{ __('No versions yet') }}</p>
            <p class="artwork-detail-empty__hint">{{ __('Upload the first artwork version to begin the approval workflow.') }}</p>
        </div>
    @endif

    @can('create', [App\Models\Artwork\ArtworkVersion::class, $request])
        <div id="artwork-versions-upload" class="artwork-detail-upload-section">
            <p class="artwork-detail-upload-section__title">{{ __('Upload version') }}</p>
            <form
                method="POST"
                action="{{ route('admin.artwork.versions.store', $request) }}"
                enctype="multipart/form-data"
                @unless ($fromDesk)
                    data-turbo-frame="erp-main"
                @endunless
                @if ($fromDesk)
                    data-erp-desk-form
                @endif
                class="space-y-3"
            >
                @csrf
                @if ($fromDesk)
                    <input type="hidden" name="from" value="designer-desk">
                @endif
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('Artwork file') }}</label>
                    <x-admin.file-upload
                        name="file"
                        :accept="$accept"
                        :label="__('Choose artwork file')"
                        :hint="__('PDF, AI, PSD, PNG, JPG…')"
                        required
                    />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('Version notes') }}</label>
                    <input type="text" name="notes" class="erp-input w-full text-sm" placeholder="{{ __('Optional notes for this version') }}">
                </div>
                <button type="submit" @class(['erp-btn-primary text-sm' => ! $hasVersions, 'erp-btn-secondary text-sm' => $hasVersions])>{{ __('Upload version') }}</button>
            </form>
        </div>
    @else
        @if ($request->lacksUploadedVersion())
            <p class="artwork-detail-upload-section mt-4 text-sm text-slate-500">{{ __('You do not have permission to upload artwork. Ask a designer or administrator to attach a file.') }}</p>
        @endif
    @endcan
</div>
