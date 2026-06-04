@php
    $timeline = $workspaceData['timeline'] ?? collect();
@endphp

<aside class="shared-inbox__ctx-panel flex h-full min-h-0 w-full flex-col">
    <div class="shared-inbox__ctx-head">
        <p class="shared-inbox__ctx-head-title">{{ __('Customer info') }}</p>
        <button
            type="button"
            class="shared-inbox__ctx-close"
            @click="closeDrawer()"
            aria-label="{{ __('Close customer info') }}"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <div class="shared-inbox__ctx-tabs" role="tablist">
        @foreach ([
            'summary' => __('Customer'),
            'records' => __('Orders & quotes'),
            'files' => __('Files'),
            'notes' => __('Notes'),
        ] as $key => $label)
            <button
                type="button"
                role="tab"
                @click="ctxTab='{{ $key }}'"
                class="shared-inbox__ctx-tab"
                :class="ctxTab==='{{ $key }}' && 'shared-inbox__ctx-tab--active'"
            >{{ $label }}</button>
        @endforeach
        <button type="button" role="tab" @click="ctxTab='manage'" class="shared-inbox__ctx-tab" :class="ctxTab==='manage' && 'shared-inbox__ctx-tab--active'">{{ __('Insights') }}</button>
        <button type="button" role="tab" @click="ctxTab='timeline'" class="shared-inbox__ctx-tab" :class="ctxTab==='timeline' && 'shared-inbox__ctx-tab--active'">{{ __('Activity') }}</button>
    </div>

    <div class="shared-inbox__ctx-body">
        <div x-show="ctxTab==='summary'" x-cloak>
            @include('admin.communications.inbox.workspace.tab-summary')
        </div>
        <div x-show="ctxTab==='manage'" x-cloak>
            @include('admin.communications.inbox.workspace.tab-manage', [
                'kpis' => $workspaceData['kpis'],
                'slaDetail' => $workspaceData['sla_detail'],
            ])
        </div>
        <div x-show="ctxTab==='records'" x-cloak>
            @include('admin.communications.inbox.workspace.tab-records')
        </div>
        <div x-show="ctxTab==='files'" x-cloak>
            @include('admin.communications.inbox.workspace.attachments-hub')
        </div>
        <div x-show="ctxTab==='timeline'" x-cloak>
            @include('admin.communications.inbox.workspace.tab-timeline', ['events' => $timeline])
        </div>
        <div x-show="ctxTab==='notes'" x-cloak>
            @include('admin.communications.inbox.workspace.tab-notes')
        </div>
    </div>
</aside>
