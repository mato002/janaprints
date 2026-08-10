@php
    use App\Enums\ArtworkRequestStatus;

    $needsVersion = $request->lacksUploadedVersion();
    $fromDesk = ($fromDesk ?? false) || request('from') === 'designer-desk';
    $canUpload = auth()->user()?->can('create', [\App\Models\Artwork\ArtworkVersion::class, $request]) ?? false;

    $statusHeadline = match ($request->status) {
        ArtworkRequestStatus::Requested => __('Awaiting design'),
        ArtworkRequestStatus::InDesign => __('Design in progress'),
        ArtworkRequestStatus::Submitted => __('Awaiting approval'),
        ArtworkRequestStatus::Approved => __('Approved'),
        ArtworkRequestStatus::RevisionRequested => __('Revision requested'),
        ArtworkRequestStatus::Rejected => __('Rejected'),
    };

    $guidance = match (true) {
        $request->status === ArtworkRequestStatus::Requested => __('Assign a designer, start design, or upload a version before submitting for approval.'),
        $request->status === ArtworkRequestStatus::InDesign && $needsVersion => __('Upload at least one artwork version before submitting for approval.'),
        $needsVersion => __('No artwork file is attached yet. Upload a version below to unblock this request.'),
        default => null,
    };
@endphp

<div class="artwork-detail-card artwork-detail-card--workflow">
    <h2 class="artwork-detail-card__title">{{ __('Workflow') }}</h2>
    <x-admin.workflow-error />
    <p class="artwork-detail-workflow__status">{{ $statusHeadline }}</p>
    @if ($guidance)
        <p class="artwork-detail-workflow__hint">{{ $guidance }}</p>
    @endif

    <div class="mt-4 flex flex-wrap gap-2">
        @can('assign', $request)
            <form method="POST" action="{{ route('admin.artwork.assign', $request) }}" class="flex flex-wrap items-end gap-2">
                @csrf
                @if ($fromDesk)
                    <input type="hidden" name="from" value="designer-desk">
                @endif
                <select name="assigned_designer_id" class="erp-input text-sm" required>
                    <option value="">{{ __('Assign designer') }}</option>
                    @foreach (\App\Models\User::query()->where('company_id', $request->company_id)->where('is_active', true)->orderBy('name')->get() as $designer)
                        <option value="{{ $designer->id }}" @selected($request->assigned_designer_id === $designer->id)>{{ $designer->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="erp-btn-secondary text-sm">{{ __('Assign') }}</button>
            </form>
        @endcan

        @if ($canUpload && $request->status->isEditable())
            <a
                href="#artwork-versions-upload"
                @class([
                    'erp-btn-primary text-sm' => $needsVersion,
                    'erp-btn-secondary text-sm' => ! $needsVersion,
                ])
            >{{ __('Upload artwork') }}</a>
        @endif

        @can('startDesign', $request)
            <form method="POST" action="{{ route('admin.artwork.start-design', $request) }}">
                @csrf
                @if ($fromDesk)
                    <input type="hidden" name="from" value="designer-desk">
                @endif
                <button type="submit" class="erp-btn-secondary text-sm">
                    {{ $request->status === ArtworkRequestStatus::Requested ? __('Start design') : __('Resume design') }}
                </button>
            </form>
        @endcan

        @can('submit', $request)
            <form method="POST" action="{{ route('admin.artwork.submit', $request) }}">
                @csrf
                @if ($fromDesk)
                    <input type="hidden" name="from" value="designer-desk">
                @endif
                <button
                    type="submit"
                    @class([
                        'erp-btn-primary text-sm' => ! $needsVersion,
                        'erp-btn-secondary text-sm opacity-60' => $needsVersion,
                    ])
                >{{ __('Submit for approval') }}</button>
            </form>
        @endcan

        @can('approve', $request)
            @if ($request->status === ArtworkRequestStatus::Submitted && $needsVersion)
                <form method="POST" action="{{ route('admin.artwork.approve', $request) }}" class="flex flex-wrap items-end gap-2">
                    @csrf
                    @if ($fromDesk)
                        <input type="hidden" name="from" value="designer-desk">
                    @endif
                    <input type="hidden" name="decision" value="rejected">
                    <input type="text" name="comments" class="erp-input text-sm" placeholder="{{ __('Rejection reason') }}">
                    <button type="submit" class="erp-btn-secondary text-sm">{{ __('Reject request') }}</button>
                </form>
                @can('startDesign', $request)
                    <form method="POST" action="{{ route('admin.artwork.start-design', $request) }}">
                        @csrf
                        @if ($fromDesk)
                            <input type="hidden" name="from" value="designer-desk">
                        @endif
                        <button type="submit" class="erp-btn-primary text-sm">{{ __('Return to design') }}</button>
                    </form>
                @endcan
            @elseif ($request->canApproveOrRequestRevision())
                <form method="POST" action="{{ route('admin.artwork.approve', $request) }}" class="flex flex-wrap items-end gap-2">
                    @csrf
                    @if ($fromDesk)
                        <input type="hidden" name="from" value="designer-desk">
                    @endif
                    <select name="decision" class="erp-input text-sm" required>
                        <option value="approved">{{ __('Approve') }}</option>
                        <option value="revision_requested">{{ __('Request revision') }}</option>
                        <option value="rejected">{{ __('Reject') }}</option>
                    </select>
                    <input type="text" name="comments" class="erp-input text-sm" placeholder="{{ __('Comments') }}">
                    <button type="submit" class="erp-btn-primary text-sm">{{ __('Record decision') }}</button>
                </form>
            @endif
        @endcan
    </div>
</div>
