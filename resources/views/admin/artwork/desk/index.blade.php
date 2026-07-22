@php
    $operatorMode = (bool) ($operatorMode ?? false);
@endphp

<x-admin-layout
    :title="$operatorMode ? __('Designer Desk') : __('Artwork Desk')"
    :breadcrumbs="$operatorMode
        ? [['label' => __('Designer Desk')]]
        : [
            ['label' => __('Artwork'), 'url' => route('admin.artwork.dashboard')],
            ['label' => __('Designer Desk')],
        ]"
    :compact-page="false"
>
    <div class="designer-desk-shell">
        @if ($operatorMode)
            <div class="mb-3 flex flex-col gap-2 rounded-lg border border-erp-accent/25 bg-erp-accent/5 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-erp-primary">{{ __('Designer desk') }}</p>
                    <p class="text-xs text-slate-600">{{ __('Your assigned artwork — open a request in a modal to upload, submit, and finish without leaving this desk.') }}</p>
                </div>
            </div>
        @else
            <x-admin.page-header
                :title="__('Designer Desk')"
                :description="__('Focused view of artwork requests assigned to you — upload versions, start design, and submit for approval.')"
            >
                <x-slot name="actions">
                    <a href="{{ route('admin.artwork.dashboard') }}" class="erp-btn-secondary" data-turbo-frame="_top">{{ __('Full Artwork dashboard') }}</a>
                </x-slot>
            </x-admin.page-header>
        @endif

        @if (session('status'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif

        {{-- Summary strip --}}
        <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-lg border border-erp-border bg-white px-4 py-3 text-center">
                <p class="text-2xl font-bold text-blue-600">{{ $summary['in_design'] }}</p>
                <p class="text-xs text-slate-600">{{ __('In Design') }}</p>
            </div>
            <div class="rounded-lg border border-erp-border bg-white px-4 py-3 text-center">
                <p class="text-2xl font-bold text-indigo-600">{{ $summary['submitted'] }}</p>
                <p class="text-xs text-slate-600">{{ __('Submitted') }}</p>
            </div>
            <div class="rounded-lg border border-erp-border bg-white px-4 py-3 text-center">
                <p class="text-2xl font-bold text-amber-600">{{ $summary['revision_requested'] }}</p>
                <p class="text-xs text-slate-600">{{ __('Revision Requested') }}</p>
            </div>
            <div class="rounded-lg border border-erp-border bg-white px-4 py-3 text-center">
                <p class="text-2xl font-bold text-emerald-600">{{ $summary['approved'] }}</p>
                <p class="text-xs text-slate-600">{{ __('Approved') }}</p>
            </div>
        </div>

        {{-- Requests table --}}
        <x-admin.card :padding="false">
            <div class="overflow-x-auto">
                <table class="erp-table w-full text-sm">
                    <thead>
                        <tr>
                            <th>{{ __('Request #') }}</th>
                            <th>{{ __('Customer') }}</th>
                            <th>{{ __('Title') }}</th>
                            <th>{{ __('Priority') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Due Date') }}</th>
                            <th>{{ __('Version') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($requests as $artworkRequest)
                            <tr>
                                <td class="font-mono text-xs">{{ $artworkRequest->request_number }}</td>
                                <td>{{ $artworkRequest->customer?->name ?? '—' }}</td>
                                <td class="font-medium">{{ $artworkRequest->title }}</td>
                                <td>
                                    @php
                                        $priorityColors = [
                                            'low' => 'bg-slate-100 text-slate-700',
                                            'normal' => 'bg-blue-100 text-blue-700',
                                            'high' => 'bg-amber-100 text-amber-700',
                                            'urgent' => 'bg-rose-100 text-rose-700',
                                        ];
                                    @endphp
                                    <span @class([
                                        'inline-flex rounded-full px-2 py-0.5 text-xs font-medium',
                                        $priorityColors[$artworkRequest->priority->value] ?? 'bg-slate-100 text-slate-700',
                                    ])>{{ ucfirst($artworkRequest->priority->value) }}</span>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'requested' => 'bg-slate-100 text-slate-700',
                                            'in_design' => 'bg-blue-100 text-blue-700',
                                            'submitted' => 'bg-indigo-100 text-indigo-700',
                                            'approved' => 'bg-emerald-100 text-emerald-700',
                                            'revision_requested' => 'bg-amber-100 text-amber-700',
                                            'rejected' => 'bg-rose-100 text-rose-700',
                                        ];
                                        $statusLabels = [
                                            'requested' => __('Requested'),
                                            'in_design' => __('In Design'),
                                            'submitted' => __('Submitted'),
                                            'approved' => __('Approved'),
                                            'revision_requested' => __('Revision Requested'),
                                            'rejected' => __('Rejected'),
                                        ];
                                    @endphp
                                    <span @class([
                                        'inline-flex rounded-full px-2 py-0.5 text-xs font-medium',
                                        $statusColors[$artworkRequest->status->value] ?? 'bg-slate-100 text-slate-700',
                                    ])>{{ $statusLabels[$artworkRequest->status->value] ?? $artworkRequest->status->value }}</span>
                                </td>
                                <td class="text-xs">{{ $artworkRequest->due_date?->format('d M Y') ?? '—' }}</td>
                                <td class="text-center">{{ $artworkRequest->current_version ?: '—' }}</td>
                                <td>
                                    <div class="flex flex-wrap items-center gap-1">
                                        @php
                                            $modalUrl = route('admin.artwork.show', [$artworkRequest, 'from' => 'designer-desk']);
                                            $uploadModalUrl = route('admin.artwork.show', [
                                                $artworkRequest,
                                                'from' => 'designer-desk',
                                                'panel' => 'versions',
                                            ]);
                                        @endphp

                                        @if ($artworkRequest->status->isEditable())
                                            <a href="{{ $modalUrl }}" class="erp-btn-primary px-2 py-1 text-xs" data-erp-modal-open>{{ __('Work') }}</a>
                                            <a href="{{ $uploadModalUrl }}" class="erp-btn-secondary px-2 py-1 text-xs" data-erp-modal-open>{{ __('Upload') }}</a>
                                        @else
                                            <a href="{{ $modalUrl }}" class="erp-btn-secondary px-2 py-1 text-xs" data-erp-modal-open>{{ __('View') }}</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-sm text-slate-500">{{ __('No artwork requests assigned to you.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-admin.card>

        <div class="mt-4 pb-6">{{ $requests->links() }}</div>
    </div>
</x-admin-layout>
