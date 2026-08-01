@php
    use App\Support\Navigation\WorkspaceEmbed;
    use App\Support\Procurement\ProcurementDeskViews;

    $activeProcurementView = ProcurementDeskViews::normalize($activeProcurementView ?? request('view'));
    $isPanel = ProcurementDeskViews::isPanelView($activeProcurementView);
    $deskTitle = $isPanel ? ($registerTitle ?? __('Buy Desk')) : __('Buy Desk');
@endphp

<x-admin-layout
    :title="$deskTitle"
    :breadcrumbs="[
        ['label' => __('Supply Chain'), 'url' => $fullSupplyChainDeskUrl],
        ['label' => __('Buy Desk')],
    ]"
>
    <div class="buy-desk-command store-desk-command">
        @unless (WorkspaceEmbed::inWorkspaceContext())
            @include('admin.procurement.partials.desk-mode-nav', ['activeProcurementView' => $activeProcurementView])
        @endunless

        @if (session('status'))
            <div class="mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-3 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-800">
                <ul class="list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($isPanel)
            @include('admin.procurement.desk.partials.register-panel')
        @else
            @include('admin.procurement.desk.partials.summary-strip', ['workQueue' => $workQueue])

            @include('admin.procurement.desk.partials.pipeline', ['pipelineStages' => $pipelineStages])

            <div class="store-desk-command__split mb-3 grid gap-3 lg:grid-cols-5">
                <div class="lg:col-span-3">
                    @include('admin.procurement.desk.partials.needs-attention', [
                        'needsAttention' => $workQueue['needs_attention'] ?? [],
                    ])
                </div>
                <div class="lg:col-span-2">
                    @include('admin.procurement.desk.partials.fast-actions', ['fastActions' => $fastActions])
                </div>
            </div>

            <div class="store-desk-command__split mb-3 grid gap-3 lg:grid-cols-2">
                @include('admin.procurement.desk.partials.work-queue', ['queueItems' => $queueItems])
                @include('admin.procurement.desk.partials.receiving-pipeline', [
                    'receivingPipeline' => $receivingPipeline,
                ])
            </div>
        @endif
    </div>
</x-admin-layout>
