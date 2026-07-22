@php
    $focusPanel = $focusPanel ?? request('panel');
@endphp

<x-admin.modal-form
    :title="$request->request_number"
    maxWidth="5xl"
>
    <x-admin.artwork-preview-lightbox>
        <div
            class="space-y-4"
            @if ($focusPanel === 'versions')
                x-data
                x-init="$nextTick(() => document.getElementById('designer-desk-versions')?.scrollIntoView({ behavior: 'smooth', block: 'start' }))"
            @endif
        >
            <div class="flex flex-wrap items-center gap-2 border-b border-erp-border pb-3">
                <span class="erp-badge">{{ str_replace('_', ' ', $request->status->value) }}</span>
                <span class="text-sm text-slate-500">v{{ $request->current_version ?: '0' }}</span>
                <span class="text-sm font-medium text-slate-700">{{ $request->title }}</span>
                @if ($request->customer?->company_name)
                    <span class="text-sm text-slate-500">— {{ $request->customer->company_name }}</span>
                @endif
            </div>

            <x-admin.workflow-error />

            @if ($request->status === \App\Enums\ArtworkRequestStatus::Requested)
                <p class="text-sm text-slate-600">{{ __('Start design, then upload a version before submitting for approval.') }}</p>
            @elseif ($request->status === \App\Enums\ArtworkRequestStatus::InDesign && $request->lacksUploadedVersion())
                <p class="text-sm text-slate-600">{{ __('Upload at least one artwork version before submitting for approval.') }}</p>
            @elseif ($request->lacksUploadedVersion() && $request->status->isEditable())
                <p class="text-sm text-amber-700">{{ __('Upload a version below to continue.') }}</p>
            @endif

            <div class="flex flex-wrap gap-2">
                @can('startDesign', $request)
                    <form method="POST" action="{{ route('admin.artwork.start-design', $request) }}">
                        @csrf
                        <input type="hidden" name="from" value="designer-desk">
                        <button type="submit" class="erp-btn-secondary text-sm">
                            {{ $request->status === \App\Enums\ArtworkRequestStatus::Requested ? __('Start design') : __('Resume design') }}
                        </button>
                    </form>
                @endcan
                @can('submit', $request)
                    <form method="POST" action="{{ route('admin.artwork.submit', $request) }}">
                        @csrf
                        <input type="hidden" name="from" value="designer-desk">
                        <button type="submit" class="erp-btn-primary text-sm">{{ __('Submit for approval') }}</button>
                    </form>
                @endcan
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div class="rounded-lg border border-erp-border bg-white p-4">
                    <h3 class="mb-2 text-sm font-semibold text-slate-900">{{ __('Details') }}</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-2">
                            <dt class="text-slate-500">{{ __('Priority') }}</dt>
                            <dd>{{ ucfirst($request->priority->value) }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-slate-500">{{ __('Due') }}</dt>
                            <dd>{{ $request->due_date?->format('d M Y') ?? '—' }}</dd>
                        </div>
                        @if ($request->description)
                            <div>
                                <dt class="text-slate-500">{{ __('Description') }}</dt>
                                <dd class="mt-1 text-slate-700">{{ $request->description }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <div class="rounded-lg border border-erp-border bg-white p-4">
                    <h3 class="mb-2 text-sm font-semibold text-slate-900">{{ __('Reference files') }}</h3>
                    @forelse ($request->files as $file)
                        <div class="py-1 text-sm">{{ $file->original_name }} ({{ $file->file_type->value }})</div>
                    @empty
                        <p class="text-sm text-slate-500">{{ __('No reference files.') }}</p>
                    @endforelse
                </div>
            </div>

            <div id="designer-desk-versions" class="rounded-lg border border-erp-border bg-white p-4">
                <h3 class="mb-2 text-sm font-semibold text-slate-900">{{ __('Versions') }}</h3>
                @forelse ($request->versions as $version)
                    <div class="flex flex-wrap items-start justify-between gap-2 border-b border-slate-100 py-2 text-sm last:border-0">
                        <div>
                            <strong>v{{ $version->version_number }}</strong> — {{ $version->original_name }}
                            <span class="text-slate-500">({{ $version->uploader?->name }})</span>
                            @if ($version->notes)
                                <p class="text-slate-600">{{ $version->notes }}</p>
                            @endif
                        </div>
                        @if ($version->isPreviewable())
                            <button
                                type="button"
                                class="erp-btn-ghost text-xs"
                                data-preview-url="{{ $version->previewUrl() }}"
                                data-preview-title="{{ $version->original_name }}"
                                data-preview-pdf="{{ $version->mime_type === 'application/pdf' ? '1' : '0' }}"
                                @click="show($el.dataset.previewUrl, $el.dataset.previewTitle, $el.dataset.previewPdf === '1')"
                            >{{ __('Preview') }}</button>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-500">{{ __('No versions uploaded yet.') }}</p>
                @endforelse

                @can('create', [App\Models\Artwork\ArtworkVersion::class, $request])
                    <form
                        method="POST"
                        action="{{ route('admin.artwork.versions.store', $request) }}"
                        enctype="multipart/form-data"
                        class="mt-4 space-y-2 border-t border-erp-border pt-4"
                    >
                        @csrf
                        <input type="hidden" name="from" value="designer-desk">
                        <label class="block text-xs font-semibold text-slate-700">{{ __('Artwork file') }}</label>
                        <input type="file" name="file" class="erp-input w-full" accept=".pdf,.ai,.psd,.cdr,.svg,.png,.jpg,.jpeg" required>
                        <label class="block text-xs font-semibold text-slate-700">{{ __('Version notes') }}</label>
                        <input type="text" name="notes" class="erp-input w-full" placeholder="{{ __('Optional notes for this version') }}">
                        <button type="submit" class="erp-btn-primary text-sm">{{ __('Upload version') }}</button>
                    </form>
                @endcan
            </div>

            @if ($request->comments->isNotEmpty() || $request->approvals->isNotEmpty())
                <div class="rounded-lg border border-erp-border bg-white p-4">
                    <h3 class="mb-2 text-sm font-semibold text-slate-900">{{ __('Comments & approvals') }}</h3>
                    @foreach ($request->comments as $comment)
                        <div class="border-b border-slate-100 py-2 text-sm last:border-0">
                            <span class="erp-badge">{{ $comment->comment_type->value }}</span>
                            {{ $comment->user?->name }}: {{ $comment->comment }}
                        </div>
                    @endforeach
                    @foreach ($request->approvals as $approval)
                        <div class="mt-2 text-sm text-slate-600">
                            {{ $approval->decision->value }} — {{ $approval->approver?->name }}
                            @if ($approval->comments) ({{ $approval->comments }}) @endif
                        </div>
                    @endforeach
                </div>
            @endif

            @can('view', $request)
                <form method="POST" action="{{ route('admin.artwork.comments.store', $request) }}" class="space-y-2 rounded-lg border border-erp-border bg-slate-50 p-4">
                    @csrf
                    <input type="hidden" name="from" value="designer-desk">
                    <label class="block text-xs font-semibold text-slate-700">{{ __('Add comment') }}</label>
                    <select name="comment_type" class="erp-input w-full">
                        <option value="internal">{{ __('Internal') }}</option>
                        <option value="customer">{{ __('Customer') }}</option>
                    </select>
                    <textarea name="comment" class="erp-input w-full" rows="2" required placeholder="{{ __('Notes for the team…') }}"></textarea>
                    <button type="submit" class="erp-btn-secondary text-sm">{{ __('Save comment') }}</button>
                </form>
            @endcan
        </div>
    </x-admin.artwork-preview-lightbox>
</x-admin.modal-form>
