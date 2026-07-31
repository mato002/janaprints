@php
    use App\Support\Navigation\WorkspaceEmbed;

    $frame = WorkspaceEmbed::turboFrame();
@endphp

<section class="rounded-xl border border-erp-border bg-white shadow-sm" aria-label="{{ __('Awaiting delivery') }}">
    <div class="flex items-center justify-between gap-2 border-b border-erp-border px-3 py-2">
        <h2 class="text-sm font-semibold text-slate-900">{{ __('Awaiting delivery') }}</h2>
        <a
            href="{{ WorkspaceEmbed::url(route('admin.procurement.orders.index')) }}"
            class="text-[11px] font-semibold text-erp-accent hover:underline"
            data-turbo-frame="{{ $frame }}"
            data-turbo-action="advance"
        >{{ __('Orders') }}</a>
    </div>

    @if (count($receivingPipeline ?? []) === 0)
        <div class="px-3 py-5 text-center text-sm text-slate-500">{{ __('No open purchase deliveries.') }}</div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">
                        <th class="px-3 py-2">{{ __('Supplier') }}</th>
                        <th class="px-3 py-2">{{ __('Expected') }}</th>
                        <th class="px-3 py-2 text-right">{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($receivingPipeline as $row)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-3 py-2">
                                <a
                                    href="{{ WorkspaceEmbed::url($row['url']) }}"
                                    class="block"
                                    data-turbo-frame="{{ $frame }}"
                                    data-turbo-action="advance"
                                >
                                    <span class="block truncate font-medium text-slate-900">{{ $row['supplier'] }}</span>
                                    <span class="block font-mono text-[11px] text-slate-500">{{ $row['label'] }}</span>
                                </a>
                            </td>
                            <td class="px-3 py-2 text-xs text-slate-600">{{ $row['timing'] }}</td>
                            <td class="px-3 py-2 text-right">
                                <span @class([
                                    'text-xs font-semibold',
                                    'text-rose-700' => $row['overdue'] ?? false,
                                    'text-amber-700' => ! ($row['overdue'] ?? false) && ($row['status'] ?? '') === __('Due today'),
                                    'text-emerald-700' => ! ($row['overdue'] ?? false) && ($row['status'] ?? '') === __('On time'),
                                    'text-slate-600' => ! ($row['overdue'] ?? false) && ! in_array($row['status'] ?? '', [__('Due today'), __('On time')], true),
                                ])>{{ $row['status'] ?? $row['timing'] }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
