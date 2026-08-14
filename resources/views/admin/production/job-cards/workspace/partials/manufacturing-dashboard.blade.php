@php
    $cards = $tabData['dashboard_cards'] ?? [];
    $sections = $tabData['sections'] ?? [];
    $operators = $tabData['operators'] ?? [];
    $recommendations = $tabData['recommendations'] ?? [];
    $materialPlan = $tabData['material_plan'] ?? [];
    $costSummary = $tabData['cost_summary'] ?? null;
    $qcHints = $tabData['qc_hints'] ?? [];
    $artwork = $tabData['artwork'] ?? [];

    $cardToneClasses = [
        'success' => 'border-emerald-200 bg-emerald-50/60 hover:border-emerald-300',
        'warning' => 'border-amber-200 bg-amber-50/60 hover:border-amber-300',
        'danger' => 'border-red-200 bg-red-50/60 hover:border-red-300',
        'active' => 'border-sky-200 bg-sky-50/60 hover:border-sky-300',
        'neutral' => 'border-slate-200 bg-slate-50/80 hover:border-slate-300',
    ];

    $statusToneClasses = [
        'success' => 'bg-emerald-100 text-emerald-800',
        'warning' => 'bg-amber-100 text-amber-800',
        'danger' => 'bg-red-100 text-red-800',
        'active' => 'bg-sky-100 text-sky-800',
        'neutral' => 'bg-slate-100 text-slate-700',
    ];
@endphp

<div
    class="mfg-dashboard"
    x-data="{
        activeCard: null,
        openCard(id) { this.activeCard = id },
        closeDrawer() { this.activeCard = null },
        cardLabel(id) {
            if (id === 'cost') {
                return @js(__('Cost summary'));
            }
            const labels = @js(collect($cards)->mapWithKeys(fn ($c) => [$c['id'] => $c['label']])->all());
            return labels[id] ?? id;
        },
    }"
    @keydown.escape.window="closeDrawer()"
>
    <x-admin.card>
        <div class="mb-4 flex flex-wrap items-end justify-between gap-2">
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Manufacturing overview') }}</h3>
                @if (! empty($tabData['template_name']))
                    <p class="mt-0.5 text-xs text-slate-500">{{ __('Template') }}: {{ $tabData['template_name'] }}</p>
                @endif
            </div>
            <p class="text-xs text-slate-500">{{ __('Click a module for details') }}</p>
        </div>

        <div class="erp-card-grid">
            @foreach ($cards as $card)
                @php
                    $tone = $card['tone'] ?? 'neutral';
                    $cardClass = $cardToneClasses[$tone] ?? $cardToneClasses['neutral'];
                    $badgeClass = $statusToneClasses[$tone] ?? $statusToneClasses['neutral'];
                @endphp
                <button
                    type="button"
                    class="group flex min-h-[7.5rem] w-full flex-col rounded-lg border p-4 text-left shadow-sm transition hover:shadow-md focus:outline-none focus:ring-2 focus:ring-erp-primary/25 {{ $cardClass }}"
                    @click="openCard(@js($card['id']))"
                    :aria-expanded="activeCard === @js($card['id'])"
                >
                    <div class="flex w-full items-start justify-between gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-600">{{ $card['label'] }}</span>
                        <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide {{ $badgeClass }}">
                            {{ $card['status'] }}
                        </span>
                    </div>
                    @if (! empty($card['summary']))
                        <p class="mt-2 line-clamp-2 text-sm font-medium text-slate-800">{{ $card['summary'] }}</p>
                    @else
                        <p class="mt-2 text-sm text-slate-400">{{ __('No summary') }}</p>
                    @endif
                    <span class="mt-auto pt-3 text-xs font-medium text-slate-500 group-hover:text-erp-primary">
                        {{ __('View details') }} →
                    </span>
                </button>
            @endforeach
        </div>

        @if ($costSummary)
            <div class="mt-3 border-t border-erp-border pt-3">
                <button
                    type="button"
                    class="flex w-full max-w-xs items-center justify-between gap-3 rounded-lg border border-slate-200 bg-slate-50/80 px-4 py-3 text-left transition hover:border-slate-300 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-erp-primary/25"
                    @click="openCard('cost')"
                >
                    <span class="text-xs font-semibold uppercase tracking-wide text-slate-600">{{ __('Cost summary') }}</span>
                    <span class="font-semibold tabular-nums text-slate-900">{{ number_format($costSummary['total'], 2) }}</span>
                </button>
            </div>
        @endif
    </x-admin.card>

    {{-- Side drawer --}}
    <div
        class="fixed inset-0 z-40 flex justify-end"
        x-show="activeCard"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="absolute inset-0 bg-slate-900/30" @click="closeDrawer()" aria-hidden="true"></div>
        <aside
            class="relative z-10 flex h-full w-full max-w-md flex-col border-l border-erp-border bg-white shadow-xl"
            role="dialog"
            aria-modal="true"
            :aria-labelledby="'mfg-drawer-title-' + activeCard"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
        >
            <div class="flex items-center justify-between gap-3 border-b border-erp-border px-5 py-4">
                <h4 class="text-base font-semibold text-erp-primary" :id="'mfg-drawer-title-' + activeCard" x-text="cardLabel(activeCard)"></h4>
                <button type="button" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-700" @click="closeDrawer()" aria-label="{{ __('Close') }}">
                    <x-admin.icon name="x-mark" class="h-5 w-5" />
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-5 py-4">
                <div x-show="activeCard === 'general'" x-cloak>
                    @if (! empty($sections['general']))
                        @include('admin.production.job-cards.workspace.partials.manufacturing-field-list', ['fields' => $sections['general']])
                    @else
                        <p class="text-sm text-slate-500">{{ __('No general specification fields.') }}</p>
                    @endif
                    @if (! empty($sections['notes']))
                        <div class="mt-4 border-t border-erp-border pt-4">
                            <h5 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Production notes') }}</h5>
                            @include('admin.production.job-cards.workspace.partials.manufacturing-field-list', ['fields' => $sections['notes']])
                        </div>
                    @endif
                    @if (! empty($sections['delivery']))
                        <div class="mt-4 border-t border-erp-border pt-4">
                            <h5 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Delivery') }}</h5>
                            @include('admin.production.job-cards.workspace.partials.manufacturing-field-list', ['fields' => $sections['delivery']])
                        </div>
                    @endif
                </div>

                <div x-show="activeCard === 'materials'" x-cloak>
                    @if (! empty($sections['material']))
                        @include('admin.production.job-cards.workspace.partials.manufacturing-field-list', ['fields' => $sections['material']])
                    @endif
                    @if (! empty($materialPlan['paper']) || ! empty($materialPlan['estimated_sheets']))
                        <div class="@if (! empty($sections['material'])) mt-4 border-t border-erp-border pt-4 @endif">
                            <h5 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Material summary') }}</h5>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                <div class="rounded-lg border border-erp-border bg-slate-50 p-3">
                                    <div class="text-xs text-slate-500">{{ __('Paper') }}</div>
                                    <div class="mt-1 font-medium">{{ $materialPlan['paper'] ?? '—' }}</div>
                                </div>
                                <div class="rounded-lg border border-erp-border bg-slate-50 p-3">
                                    <div class="text-xs text-slate-500">{{ __('Estimated sheets') }}</div>
                                    <div class="mt-1 font-medium tabular-nums">{{ $materialPlan['estimated_sheets'] ?? '—' }}</div>
                                </div>
                                <div class="rounded-lg border border-erp-border bg-slate-50 p-3">
                                    <div class="text-xs text-slate-500">{{ __('Waste') }}</div>
                                    <div class="mt-1 font-medium tabular-nums">
                                        @if (($materialPlan['waste_percent'] ?? null) !== null)
                                            {{ number_format((float) $materialPlan['waste_percent'], 1) }}%
                                        @else
                                            —
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-slate-500">{{ __('Planning view only — stock is not reserved from this panel.') }}</p>
                        </div>
                    @endif
                    <a href="{{ route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'materials']) }}" class="mt-4 inline-flex items-center text-xs font-medium text-erp-primary hover:underline">{{ __('Open materials tab') }} →</a>
                </div>

                <div x-show="activeCard === 'production'" x-cloak>
                    @if (! empty($sections['production']))
                        @include('admin.production.job-cards.workspace.partials.manufacturing-field-list', ['fields' => $sections['production']])
                    @else
                        <p class="text-sm text-slate-500">{{ __('No production specification fields.') }}</p>
                    @endif
                </div>

                <div x-show="activeCard === 'printing'" x-cloak>
                    @if (! empty($sections['printing']))
                        @include('admin.production.job-cards.workspace.partials.manufacturing-field-list', ['fields' => $sections['printing']])
                    @else
                        <p class="text-sm text-slate-500">{{ __('No printing specification fields.') }}</p>
                    @endif
                </div>

                <div x-show="activeCard === 'finishing'" x-cloak>
                    @if (! empty($sections['finishing']))
                        @include('admin.production.job-cards.workspace.partials.manufacturing-field-list', ['fields' => $sections['finishing']])
                    @else
                        <p class="text-sm text-slate-500">{{ __('No finishing specification fields.') }}</p>
                    @endif
                </div>

                <div x-show="activeCard === 'qc'" x-cloak>
                    <ul class="space-y-2 text-sm text-slate-700">
                        @foreach ($qcHints as $hint)
                            <li class="flex items-start gap-2">
                                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-erp-primary"></span>
                                <span>{{ $hint }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'quality']) }}" class="mt-4 inline-flex items-center text-xs font-medium text-erp-primary hover:underline">{{ __('Open QC tab') }} →</a>
                </div>

                <div x-show="activeCard === 'dispatch'" x-cloak>
                    @if (! empty($sections['delivery']))
                        @include('admin.production.job-cards.workspace.partials.manufacturing-field-list', ['fields' => $sections['delivery']])
                    @else
                        <p class="text-sm text-slate-600">{{ __('Dispatch details are managed from the Dispatch tab once the job is ready.') }}</p>
                    @endif
                    <a href="{{ route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'dispatch']) }}" class="mt-4 inline-flex items-center text-xs font-medium text-erp-primary hover:underline">{{ __('Open dispatch tab') }} →</a>
                </div>

                <div x-show="activeCard === 'artwork'" x-cloak>
                    @if (! empty($sections['artwork']))
                        @include('admin.production.job-cards.workspace.partials.manufacturing-field-list', ['fields' => $sections['artwork']])
                    @endif
                    @if (! empty($artwork) && empty($artwork['empty']))
                        <div class="@if (! empty($sections['artwork'])) mt-4 border-t border-erp-border pt-4 @endif">
                            @if ($artwork['request'] ?? null)
                                <p class="font-medium">{{ $artwork['request']->request_number }} · v{{ $artwork['request']->current_version }}</p>
                                <p class="text-xs text-slate-500">{{ str_replace('_', ' ', $artwork['approval_status'] ?? '') }}</p>
                            @else
                                <p class="text-sm text-slate-500">{{ __('No artwork request linked.') }}</p>
                            @endif
                        </div>
                    @endif
                    <a href="{{ route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'artwork']) }}" class="mt-4 inline-flex items-center text-xs font-medium text-erp-primary hover:underline">{{ __('Open artwork tab') }} →</a>
                </div>

                <div x-show="activeCard === 'machine'" x-cloak>
                    <h5 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Machine recommendation') }}</h5>
                    <dl class="divide-y divide-erp-border rounded-lg border border-erp-border text-sm">
                        <div class="flex justify-between gap-3 px-3 py-2"><dt class="text-slate-500">{{ __('Recommended work centre') }}</dt><dd class="font-medium">{{ $recommendations['work_center'] ?? '—' }}</dd></div>
                        <div class="flex justify-between gap-3 px-3 py-2"><dt class="text-slate-500">{{ __('Recommended machine') }}</dt><dd class="font-medium">{{ $recommendations['machine'] ?? '—' }}</dd></div>
                        <div class="flex justify-between gap-3 px-3 py-2"><dt class="text-slate-500">{{ __('Recommended department') }}</dt><dd class="font-medium">{{ $recommendations['department'] ?? '—' }}</dd></div>
                    </dl>
                    <p class="mt-2 text-xs text-slate-500">{{ __('Recommendations only — operators may override assignments.') }}</p>

                    <div class="mt-4 border-t border-erp-border pt-4">
                        <h5 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Operator information') }}</h5>
                        <dl class="divide-y divide-erp-border rounded-lg border border-erp-border text-sm">
                            <div class="flex justify-between gap-3 px-3 py-2"><dt class="text-slate-500">{{ __('Assigned operator') }}</dt><dd class="font-medium">{{ $operators['operator'] ?? '—' }}</dd></div>
                            <div class="flex justify-between gap-3 px-3 py-2"><dt class="text-slate-500">{{ __('Assigned supervisor') }}</dt><dd class="font-medium">{{ $operators['supervisor'] ?? '—' }}</dd></div>
                            <div class="flex justify-between gap-3 px-3 py-2"><dt class="text-slate-500">{{ __('Assigned machine') }}</dt><dd class="font-medium">{{ $operators['machine'] ?? '—' }}</dd></div>
                            <div class="flex justify-between gap-3 px-3 py-2"><dt class="text-slate-500">{{ __('Assigned department') }}</dt><dd class="font-medium">{{ $operators['department'] ?? '—' }}</dd></div>
                        </dl>
                    </div>
                </div>

                @if ($costSummary)
                    <div x-show="activeCard === 'cost'" x-cloak>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            @foreach ([__('Material') => $costSummary['material'], __('Labour') => $costSummary['labor'], __('Outsource') => $costSummary['outsource'], __('Total') => $costSummary['total']] as $label => $value)
                                <div class="rounded-lg border border-erp-border bg-slate-50 p-3">
                                    <div class="text-xs text-slate-500">{{ $label }}</div>
                                    <div class="mt-1 font-semibold tabular-nums">{{ number_format($value, 2) }}</div>
                                </div>
                            @endforeach
                        </div>
                        <p class="mt-3 text-xs text-slate-500">{{ __('Read-only — use Commercial tab or costing workspace for full detail.') }}</p>
                    </div>
                @endif
            </div>
        </aside>
    </div>
</div>
