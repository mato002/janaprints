@php
    $fromDesk = ($fromDesk ?? false) || request('from') === 'designer-desk';
    $hasFiles = $request->files->isNotEmpty();
@endphp

<div class="artwork-detail-card">
    <h2 class="artwork-detail-card__title">{{ __('Reference files') }}</h2>

    @if ($hasFiles)
        <ul class="space-y-1 text-sm">
            @foreach ($request->files as $file)
                <li class="rounded bg-slate-50 px-2.5 py-1.5">{{ $file->original_name }} <span class="text-slate-500">({{ $file->file_type->value }})</span></li>
            @endforeach
        </ul>
    @else
        <div class="artwork-detail-empty py-6">
            <span class="artwork-detail-empty__icon" aria-hidden="true">📎</span>
            <p class="artwork-detail-empty__title">{{ __('No reference files') }}</p>
            <p class="artwork-detail-empty__hint">{{ __('Upload customer briefs, logos, or other reference material.') }}</p>
        </div>
    @endif

    @can('update', $request)
        <div class="artwork-detail-upload-section">
            <p class="artwork-detail-upload-section__title">{{ __('Upload reference') }}</p>
            <form
                method="POST"
                action="{{ route('admin.artwork.files.store', $request) }}"
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
                <x-admin.file-upload
                    name="file"
                    :label="__('Choose reference file')"
                    :hint="__('PDF, images, or design files')"
                    required
                />
                <button type="submit" class="erp-btn-secondary text-sm">{{ __('Upload reference') }}</button>
            </form>
        </div>
    @endcan
</div>
