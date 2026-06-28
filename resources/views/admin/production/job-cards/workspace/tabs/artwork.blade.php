@if (! empty($tabData['empty']))
    <x-admin.empty-state :title="__('No artwork linked')" :description="__('This job has no artwork on record.')" />
@else
    @php
        $request = $tabData['request'] ?? null;
        $latest = $tabData['latest_approval'] ?? null;
        $customerArtwork = $tabData['customer_artwork'] ?? null;
    @endphp

    @if ($customerArtwork)
        <x-admin.card class="mb-6">
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Customer artwork library') }}</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Name') }}</dt><dd>{{ $customerArtwork->artwork_name }}</dd></div>
                <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Version') }}</dt><dd>{{ $customerArtwork->versionLabel() }}</dd></div>
            </dl>
            @if ($customerArtwork->isPreviewable())
                <a href="{{ $customerArtwork->previewUrl() }}" target="_blank" class="erp-btn-secondary mt-4 text-sm">{{ __('Open preview') }}</a>
            @endif
        </x-admin.card>
    @endif

    @if ($request)
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <x-admin.card>
                <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Artwork request') }}</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Number') }}</dt><dd>
                        @can('view', $request)
                            <a href="{{ route('admin.artwork.show', $request) }}" class="text-erp-accent" data-turbo-frame="erp-main">{{ $request->request_number }}</a>
                        @else
                            {{ $request->request_number }}
                        @endcan
                    </dd></div>
                    <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Title') }}</dt><dd>{{ $request->title ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Status') }}</dt><dd><x-admin.enum-status-badge :status="$request->status->value" /></dd></div>
                    <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Revisions') }}</dt><dd>{{ $tabData['revision_count'] ?? 0 }}</dd></div>
                    <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Designer') }}</dt><dd>{{ $request->assignedDesigner?->name ?? '—' }}</dd></div>
                </dl>
            </x-admin.card>

            <x-admin.card>
                <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Approval') }}</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Approval status') }}</dt><dd>{{ str_replace('_', ' ', $tabData['approval_status'] ?? '—') }}</dd></div>
                    @if ($latest)
                        <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Approved by') }}</dt><dd>{{ $latest->approver?->name ?? '—' }}</dd></div>
                        <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Approved at') }}</dt><dd>{{ $latest->created_at?->format('Y-m-d H:i') ?? '—' }}</dd></div>
                    @endif
                    @if (! empty($tabData['rejection_reason']))
                        <div>
                            <dt class="text-slate-500">{{ __('Rejection reason') }}</dt>
                            <dd class="mt-1 rounded border border-red-200 bg-red-50 p-2 text-red-800">{{ $tabData['rejection_reason'] }}</dd>
                        </div>
                    @endif
                </dl>
                <p class="mt-4 text-xs text-slate-500">{{ $tabData['portal_placeholder'] ?? '' }}</p>
            </x-admin.card>
        </div>

        @if ($request->files->isNotEmpty())
            <x-admin.card class="mt-6">
                <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Uploaded files') }}</h3>
                <ul class="divide-y divide-erp-border text-sm">
                    @foreach ($request->files as $file)
                        <li class="py-2 flex justify-between gap-2">
                            <span>{{ $file->original_name ?? $file->path }}</span>
                            <span class="text-slate-500">{{ $file->created_at?->format('Y-m-d') }}</span>
                        </li>
                    @endforeach
                </ul>
            </x-admin.card>
        @else
            <x-admin.card class="mt-6 border-dashed">
                <p class="text-sm text-slate-500">{{ __('No artwork files uploaded yet.') }}</p>
            </x-admin.card>
        @endif
    @endif
@endif
