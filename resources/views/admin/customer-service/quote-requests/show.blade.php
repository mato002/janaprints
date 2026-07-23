<x-admin-layout
    :title="__('Quote Request').' '.$workspace['reference']"
    :breadcrumbs="[
        ['label' => __('Commercial'), 'url' => route('admin.workspaces.commercial')],
        ['label' => __('Customer Service'), 'url' => route('admin.workspaces.commercial.section', 'customer-service')],
        ['label' => __('Quote Requests'), 'url' => route('admin.public-quote-requests.index')],
        ['label' => $workspace['reference']],
    ]"
>
    @php
        $artworkFileId = $workspace['printing_intelligence']['artwork_file_id'] ?? 'primary';
        $header = $workspace['header'];
        $next = $workspace['next_action'];
        $score = $workspace['lead_score'];
    @endphp

    <div
        x-data="qr360PrintingIntelligence({
            summary: @js($workspace['printing_intelligence']['summary'] ?? null),
            modalUrl: @js(route('admin.public-quote-requests.printing-analysis.modal', [$quoteRequest, $artworkFileId])),
            runUrl: @js(route('admin.public-quote-requests.printing-analysis.run', [$quoteRequest, $artworkFileId])),
            rerunUrl: @js(route('admin.public-quote-requests.printing-analysis.rerun', [$quoteRequest, $artworkFileId])),
            applyUrl: @js(route('admin.public-quote-requests.printing-analysis.apply-quotation', [$quoteRequest, $artworkFileId])),
            activeArtwork: @js($workspace['artwork_files'][0]['id'] ?? 'primary'),
        })"
    >
        <x-admin.record-workspace.shell>
            <x-slot:header>
                <x-admin.record-workspace.header
                    :eyebrow="$header['reference']"
                    :back-url="route('admin.public-quote-requests.index')"
                    :back-label="__('Quote Requests')"
                    :title="$header['customer_name']"
                    :subtitle="$header['company'] ?: __('Individual / no company')"
                    :meta="array_values(array_filter([
                        $header['service'],
                        $header['quantity'] !== '—' ? $header['quantity'].' '.__('units') : null,
                        $header['submitted_at'],
                    ]))"
                    :metrics="[
                        ['label' => __('Expected value'), 'value' => $header['expected_value']],
                        ['label' => __('Assigned'), 'value' => $header['assigned_to']],
                    ]"
                >
                    <x-slot:badges>
                        <x-admin.status-badge :variant="$header['status_variant']">{{ $header['status_label'] }}</x-admin.status-badge>
                        <span class="rw-score rw-score--{{ $score['variant'] }}">{{ $score['label'] }}</span>
                        <span class="rw-stars" title="{{ $score['hint'] }}">{{ $score['stars'] }}</span>
                    </x-slot:badges>
                </x-admin.record-workspace.header>
            </x-slot:header>

            <x-slot:workflow>
                <x-admin.record-workspace.workflow :steps="$workspace['workflow']" />
            </x-slot:workflow>

            <x-slot:actions>
                @include('admin.customer-service.quote-requests.workspace.action-bar')
            </x-slot:actions>

            <x-slot:main>
                <x-admin.record-workspace.next-action
                    :label="$next['label']"
                    :hint="$next['hint']"
                    :tone="$next['tone']"
                    :when="$next['when']"
                    :reasons="$next['reasons']"
                />

                <div class="rw__work-grid">
                    @include('admin.customer-service.quote-requests.workspace.timeline')
                    <div class="rw__work-side">
                        @include('admin.customer-service.quote-requests.workspace.snapshot')
                        @include('admin.customer-service.quote-requests.workspace.artwork')
                        @include('admin.customer-service.quote-requests.workspace.printing-intelligence-panel')
                    </div>
                </div>

                @include('admin.customer-service.quote-requests.workspace.collaboration')
                @include('admin.customer-service.quote-requests.workspace.sales-review')
            </x-slot:main>

            <x-slot:rail>
                @include('admin.customer-service.quote-requests.workspace.sidebar')
            </x-slot:rail>

            <x-slot:modals>
                @include('admin.customer-service.quote-requests.workspace.artwork-modal')
                @include('admin.customer-service.quote-requests.workspace.printing-intelligence-modal')
            </x-slot:modals>
        </x-admin.record-workspace.shell>
    </div>
</x-admin-layout>
