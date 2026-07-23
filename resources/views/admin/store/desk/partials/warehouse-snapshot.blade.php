@php
    use App\Support\Navigation\WorkspaceEmbed;
@endphp

@if (count($warehouseSnapshot ?? []) > 0)
    <section class="mb-3 rounded-xl border border-erp-border bg-white shadow-sm" aria-label="{{ __('Warehouse snapshot') }}">
        <div class="flex items-center justify-between gap-2 border-b border-erp-border px-3 py-2">
            <h2 class="text-sm font-semibold text-slate-900">{{ __('Warehouse snapshot') }}</h2>
            <a
                href="{{ WorkspaceEmbed::url(\App\Support\Inventory\StoreDeskViews::deskUrl(\App\Support\Inventory\StoreDeskViews::BALANCES)) }}"
                class="text-[11px] font-semibold text-erp-accent hover:underline"
                data-turbo-frame="{{ WorkspaceEmbed::turboFrame() }}"
                data-turbo-action="advance"
            >{{ __('Balances') }}</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">
                        <th class="px-3 py-2 font-bold">{{ __('Warehouse') }}</th>
                        <th class="px-3 py-2 font-bold">{{ __('Health') }}</th>
                        <th class="w-16 px-3 py-2 text-right font-bold">%</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($warehouseSnapshot as $warehouse)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-3 py-2">
                                <a
                                    href="{{ WorkspaceEmbed::url($warehouse['url']) }}"
                                    class="font-medium text-slate-900 hover:text-erp-accent"
                                    data-turbo-frame="{{ WorkspaceEmbed::turboFrame() }}"
                                    data-turbo-action="advance"
                                >{{ $warehouse['name'] }}</a>
                            </td>
                            <td class="px-3 py-2">
                                <div class="h-1.5 max-w-[12rem] overflow-hidden rounded-full bg-slate-100">
                                    <div
                                        @class([
                                            'h-full rounded-full transition-all',
                                            'bg-emerald-500' => ($warehouse['fill_percent'] ?? 0) >= 70,
                                            'bg-amber-500' => ($warehouse['fill_percent'] ?? 0) >= 35 && ($warehouse['fill_percent'] ?? 0) < 70,
                                            'bg-rose-500' => ($warehouse['fill_percent'] ?? 0) < 35,
                                        ])
                                        style="width: {{ min(100, max(0, $warehouse['fill_percent'])) }}%"
                                    ></div>
                                </div>
                            </td>
                            <td class="px-3 py-2 text-right tabular-nums font-semibold text-slate-700">{{ $warehouse['fill_percent'] }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endif
