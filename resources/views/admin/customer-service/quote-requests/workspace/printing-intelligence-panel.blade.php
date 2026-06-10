@php
    $pi = $workspace['printing_intelligence'] ?? [];
    $summary = $pi['summary'] ?? null;
    $artworkFileId = $pi['artwork_file_id'] ?? 'primary';
    $supported = (bool) ($pi['supported'] ?? false);
@endphp

@if ($supported || $summary)
    <section class="qr-360__card qr-360__card--pi" id="qr-360-printing-intelligence">
        <div class="qr-360__card-head">
            <h2 class="qr-360__card-title">{{ __('Printing Intelligence') }}</h2>
            <div class="flex flex-wrap gap-2">
                @can('printing.artwork.analyze')
                    @if ($supported)
                        <form
                            method="POST"
                            class="inline"
                            :action="piSummary ? piRerunUrl : piRunUrl"
                            @submit.prevent="submitPiForm($event)"
                        >
                            @csrf
                            <button type="submit" class="crm-360__btn crm-360__btn--primary crm-360__btn--sm" :disabled="piAnalysisLoading">
                                <span x-show="! piSummary">{{ __('Run Printing Intelligence Analysis') }}</span>
                                <span x-show="piSummary" x-cloak>{{ __('Re-run Analysis') }}</span>
                            </button>
                        </form>
                    @endif
                @endcan

                <button
                    type="button"
                    class="crm-360__btn crm-360__btn--outline crm-360__btn--sm"
                    x-show="piSummary"
                    x-cloak
                    @click="openPiModal()"
                >
                    {{ __('View Analysis') }}
                </button>
            </div>
        </div>

        <div x-show="piSummary" x-cloak class="qr-360__pi-compact">
            <p class="text-sm text-slate-700">
                <span class="font-medium" x-text="piSummary?.analysis_status_label"></span>
                <template x-if="piSummary?.page_count != null">
                    <span>
                        <span class="text-slate-400"> · </span>
                        <span x-text="piSummary.page_count + ' {{ __('pages') }}'"></span>
                    </span>
                </template>
                <template x-if="piSummary?.dimensions">
                    <span>
                        <span class="text-slate-400"> · </span>
                        <span x-text="piSummary.dimensions"></span>
                    </span>
                </template>
            </p>
            <p class="mt-1 text-xs text-slate-500">{{ __('Full results open in the analysis modal on this page.') }}</p>
        </div>

        @if ($supported && auth()->user()?->can('printing.artwork.analyze'))
            <p x-show="! piSummary" class="text-sm text-slate-600">
                {{ __('Run Printing Intelligence analysis on the customer artwork without re-uploading the file.') }}
            </p>
        @elseif (auth()->user()?->can('printing.intelligence.view'))
            <p x-show="! piSummary" class="text-sm text-slate-500">{{ __('No Printing Intelligence analysis has been run for this artwork yet.') }}</p>
        @endif
    </section>
@endif
