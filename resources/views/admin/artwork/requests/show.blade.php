@php
    $designerOperator = auth()->user()?->prefersDesignerOperatorMode() ?? false;
    $artworkHomeLabel = $designerOperator ? __('Designer Desk') : __('Artwork');
    $artworkHomeUrl = $designerOperator
        ? route('admin.artwork.desk')
        : route('admin.artwork.dashboard');
@endphp

<x-admin-layout :title="$request->request_number" :breadcrumbs="[['label' => $artworkHomeLabel, 'url' => $artworkHomeUrl], ['label' => $request->request_number]]">
    <x-admin.page-header :title="$request->request_number" :description="$request->customer?->company_name">
        @if ($designerOperator)
            <a href="{{ route('admin.artwork.desk') }}" class="erp-btn-secondary" data-turbo-frame="_top">{{ __('Back to Designer Desk') }}</a>
        @endif
        <span class="erp-badge">{{ str_replace('_', ' ', $request->status->value) }}</span>
        <span class="text-sm text-slate-500">v{{ $request->current_version }}</span>
        @can('update', $request)
            <a href="{{ route('admin.artwork.edit', $request) }}" class="erp-btn-secondary">{{ __('Edit') }}</a>
        @endcan
    </x-admin.page-header>

    <x-admin.card class="mb-6">
        <h3 class="font-medium mb-3">{{ __('Workflow') }}</h3>
        <x-admin.workflow-error />
        @if ($request->status === \App\Enums\ArtworkRequestStatus::Requested)
            <p class="mb-3 text-sm text-slate-600">{{ __('Assign a designer, start design, or upload a version before submitting for approval.') }}</p>
        @elseif ($request->status === \App\Enums\ArtworkRequestStatus::InDesign && $request->lacksUploadedVersion())
            <p class="mb-3 text-sm text-slate-600">{{ __('Upload at least one artwork version before submitting for approval.') }}</p>
        @elseif ($request->lacksUploadedVersion())
            <p class="mb-3 text-sm text-amber-700">{{ __('No artwork file is attached yet. Upload a version below to unblock this request.') }}</p>
        @endif
        <div class="flex flex-wrap gap-2">
            @can('assign', $request)
                <form method="POST" action="{{ route('admin.artwork.assign', $request) }}" class="flex flex-wrap gap-2 items-end">
                    @csrf
                    <select name="assigned_designer_id" class="erp-input" required>
                        <option value="">{{ __('Assign designer') }}</option>
                        @foreach (\App\Models\User::query()->where('company_id', $request->company_id)->where('is_active', true)->orderBy('name')->get() as $designer)
                            <option value="{{ $designer->id }}" @selected($request->assigned_designer_id === $designer->id)>{{ $designer->name }}</option>
                        @endforeach
                    </select>
                    <button class="erp-btn-secondary">{{ __('Assign') }}</button>
                </form>
            @endcan
            @can('submit', $request)
                <form method="POST" action="{{ route('admin.artwork.submit', $request) }}">@csrf
                    <button class="erp-btn-primary">{{ __('Submit for approval') }}</button></form>
            @endcan
            @can('startDesign', $request)
                <form method="POST" action="{{ route('admin.artwork.start-design', $request) }}">@csrf
                    <button class="erp-btn-secondary">
                        {{ $request->status === \App\Enums\ArtworkRequestStatus::Requested ? __('Start design') : __('Resume design') }}
                    </button></form>
            @endcan
            @can('approve', $request)
                @if ($request->status === \App\Enums\ArtworkRequestStatus::Submitted && $request->lacksUploadedVersion())
                    <form method="POST" action="{{ route('admin.artwork.approve', $request) }}" class="flex flex-wrap gap-2 items-end">
                        @csrf
                        <input type="hidden" name="decision" value="rejected">
                        <input type="text" name="comments" class="erp-input" placeholder="{{ __('Rejection reason') }}">
                        <button class="erp-btn-secondary">{{ __('Reject request') }}</button>
                    </form>
                    @can('startDesign', $request)
                        <form method="POST" action="{{ route('admin.artwork.start-design', $request) }}">@csrf
                            <button class="erp-btn-primary">{{ __('Return to design') }}</button>
                        </form>
                    @endcan
                @elseif ($request->canApproveOrRequestRevision())
                    <form method="POST" action="{{ route('admin.artwork.approve', $request) }}" class="flex flex-wrap gap-2 items-end">
                        @csrf
                        <select name="decision" class="erp-input" required>
                            <option value="approved">{{ __('Approve') }}</option>
                            <option value="revision_requested">{{ __('Request revision') }}</option>
                            <option value="rejected">{{ __('Reject') }}</option>
                        </select>
                        <input type="text" name="comments" class="erp-input" placeholder="{{ __('Comments') }}">
                        <button class="erp-btn-primary">{{ __('Record decision') }}</button>
                    </form>
                @endif
            @endcan
        </div>
    </x-admin.card>

    <x-admin.artwork-preview-lightbox>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <x-admin.card>
            <h3 class="font-medium mb-3">{{ __('Details') }}</h3>
            <dl class="text-sm space-y-2">
                <div><dt class="text-slate-500">{{ __('Title') }}</dt><dd>{{ $request->title }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Priority') }}</dt><dd>{{ $request->priority->value }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Due') }}</dt><dd>{{ $request->due_date?->format('Y-m-d') ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Designer') }}</dt><dd>{{ $request->assignedDesigner?->name ?? '—' }}</dd></div>
                @if ($request->description)
                    <div><dt class="text-slate-500">{{ __('Description') }}</dt><dd>{{ $request->description }}</dd></div>
                @endif
            </dl>
        </x-admin.card>

        <x-admin.card>
            <h3 class="font-medium mb-3">{{ __('Versions') }}</h3>
            @forelse ($request->versions as $version)
                <div class="flex flex-wrap items-start justify-between gap-2 border-b border-slate-100 py-2 text-sm">
                    <div>
                        <strong>v{{ $version->version_number }}</strong> — {{ $version->original_name }}
                        <span class="text-slate-500">({{ $version->uploader?->name }})</span>
                        @if ($version->notes)<p class="text-slate-600">{{ $version->notes }}</p>@endif
                    </div>
                    @if ($version->isPreviewable())
                        <button
                            type="button"
                            class="erp-btn-ghost text-xs"
                            data-preview-url="{{ $version->previewUrl() }}"
                            data-preview-title="{{ $version->original_name }}"
                            data-preview-pdf="{{ $version->mime_type === 'application/pdf' ? '1' : '0' }}"
                            @click="show($el.dataset.previewUrl, $el.dataset.previewTitle, $el.dataset.previewPdf === '1')"
                        >{{ __('View') }}</button>
                    @endif
                </div>
            @empty
                <p class="text-sm text-slate-500">{{ __('No versions uploaded yet.') }}</p>
            @endforelse
            @can('create', [App\Models\Artwork\ArtworkVersion::class, $request])
                <form method="POST" action="{{ route('admin.artwork.versions.store', $request) }}" enctype="multipart/form-data" data-turbo-frame="_top" class="mt-4 space-y-2">
                    @csrf
                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Artwork file') }}</label>
                    <input type="file" name="file" class="erp-input w-full" accept=".pdf,.ai,.psd,.cdr,.svg,.png,.jpg,.jpeg" required>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Version notes') }}</label>
                    <input type="text" name="notes" class="erp-input w-full" placeholder="{{ __('Optional notes for this version') }}">
                    <button class="erp-btn-secondary">{{ __('Upload version') }}</button>
                </form>
            @else
                @if ($request->lacksUploadedVersion())
                    <p class="mt-3 text-sm text-slate-500">{{ __('You do not have permission to upload artwork. Ask a designer or administrator to attach a file.') }}</p>
                @endif
            @endcan
        </x-admin.card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-admin.card>
            <h3 class="font-medium mb-3">{{ __('Reference files') }}</h3>
            @forelse ($request->files as $file)
                <div class="text-sm py-1">{{ $file->original_name }} ({{ $file->file_type->value }})</div>
            @empty
                <p class="text-sm text-slate-500">{{ __('No reference files.') }}</p>
            @endforelse
            @can('update', $request)
                <form method="POST" action="{{ route('admin.artwork.files.store', $request) }}" enctype="multipart/form-data" data-turbo-frame="_top" class="mt-4">
                    @csrf
                    <input type="file" name="file" class="erp-input w-full" required>
                    <button class="erp-btn-secondary mt-2">{{ __('Upload reference') }}</button>
                </form>
            @endcan
        </x-admin.card>

        <x-admin.card>
            <h3 class="font-medium mb-3">{{ __('Comments & approvals') }}</h3>
            @foreach ($request->comments as $comment)
                <div class="text-sm border-b py-2">
                    <span class="erp-badge">{{ $comment->comment_type->value }}</span>
                    {{ $comment->user?->name }}: {{ $comment->comment }}
                </div>
            @endforeach
            @can('view', $request)
                <form method="POST" action="{{ route('admin.artwork.comments.store', $request) }}" class="mt-4 space-y-2">
                    @csrf
                    <select name="comment_type" class="erp-input w-full">
                        <option value="internal">{{ __('Internal') }}</option>
                        <option value="customer">{{ __('Customer') }}</option>
                    </select>
                    <textarea name="comment" class="erp-input w-full" rows="2" required></textarea>
                    <button class="erp-btn-secondary">{{ __('Add comment') }}</button>
                </form>
            @endcan
            @foreach ($request->approvals as $approval)
                <div class="text-sm mt-3 text-slate-600">
                    {{ $approval->decision->value }} — {{ $approval->approver?->name }}
                    @if ($approval->comments) ({{ $approval->comments }}) @endif
                </div>
            @endforeach
        </x-admin.card>
    </div>
    </x-admin.artwork-preview-lightbox>
</x-admin-layout>
