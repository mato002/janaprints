<x-admin-layout :title="$requisition->title">
    <x-admin.page-header :title="$requisition->title" :description="$requisition->reference">
        <x-slot name="actions">
            <span class="erp-badge bg-slate-100 text-slate-700">{{ $requisition->status->label() }}</span>
            @can('update', $requisition)
                @if ($requisition->status === \App\Enums\JobRequisitionStatus::Draft)
                    <form method="POST" action="{{ route('admin.hr.recruitment.requisitions.submit', $requisition) }}">@csrf<button type="submit" class="erp-btn-primary text-xs">{{ __('Submit') }}</button></form>
                @endif
                @if ($requisition->status === \App\Enums\JobRequisitionStatus::Submitted)
                    <form method="POST" action="{{ route('admin.hr.recruitment.requisitions.approve', $requisition) }}">@csrf<button type="submit" class="erp-btn-primary text-xs">{{ __('Approve') }}</button></form>
                @endif
            @endcan
        </x-slot>
    </x-admin.page-header>

<x-admin.card>
        <dl class="grid gap-3 text-sm md:grid-cols-2">
            <div><dt class="text-slate-500">{{ __('Department') }}</dt><dd>{{ $requisition->department?->name ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Job Title') }}</dt><dd>{{ $requisition->jobTitle?->title ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Headcount') }}</dt><dd>{{ $requisition->headcount }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Requested By') }}</dt><dd>{{ $requisition->requestedBy?->name ?? '—' }}</dd></div>
            <div class="md:col-span-2"><dt class="text-slate-500">{{ __('Description') }}</dt><dd>{{ $requisition->description ?: '—' }}</dd></div>
            <div class="md:col-span-2"><dt class="text-slate-500">{{ __('Justification') }}</dt><dd>{{ $requisition->justification ?: '—' }}</dd></div>
        </dl>
    </x-admin.card>
</x-admin-layout>
