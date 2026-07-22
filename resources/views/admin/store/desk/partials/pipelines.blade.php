@if (count($receivingPipeline ?? []) > 0 || count($issuePipeline ?? []) > 0)
    <div class="mb-4 grid gap-4 lg:grid-cols-2">
        @if (count($receivingPipeline ?? []) > 0)
            <x-admin.card :padding="false">
                <div class="border-b border-erp-border px-4 py-3">
                    <h2 class="text-sm font-semibold text-slate-900">{{ __('Awaiting delivery') }}</h2>
                </div>
                <ul class="divide-y divide-slate-100">
                    @foreach ($receivingPipeline as $row)
                        <li>
                            <a
                                href="{{ $row['url'] }}"
                                class="flex items-center justify-between gap-3 px-4 py-3 text-sm transition hover:bg-slate-50"
                                @if ($row['modal'] ?? false) data-erp-modal-open @else data-turbo-frame="_top" @endif
                            >
                                <span class="min-w-0">
                                    <span class="block font-mono text-xs font-medium text-slate-900">{{ $row['label'] }}</span>
                                    <span class="block truncate text-xs text-slate-500">{{ $row['supplier'] }}</span>
                                </span>
                                <span @class([
                                    'shrink-0 text-xs font-medium',
                                    'text-rose-700' => $row['overdue'] ?? false,
                                    'text-amber-700' => ! ($row['overdue'] ?? false) && ($row['timing'] ?? '') === __('Expected today'),
                                    'text-slate-600' => ! ($row['overdue'] ?? false) && ($row['timing'] ?? '') !== __('Expected today'),
                                ])>{{ $row['timing'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </x-admin.card>
        @endif

        @if (count($issuePipeline ?? []) > 0)
            <x-admin.card :padding="false">
                <div class="border-b border-erp-border px-4 py-3">
                    <h2 class="text-sm font-semibold text-slate-900">{{ __('Pending issues') }}</h2>
                </div>
                <ul class="divide-y divide-slate-100">
                    @foreach ($issuePipeline as $row)
                        <li>
                            <a
                                href="{{ $row['url'] }}"
                                class="flex items-center justify-between gap-3 px-4 py-3 text-sm transition hover:bg-slate-50"
                                @if ($row['modal'] ?? false) data-erp-modal-open @endif
                            >
                                <span class="min-w-0">
                                    <span class="block font-medium text-slate-900">{{ $row['label'] }}</span>
                                    <span class="block truncate text-xs text-slate-500">{{ $row['item'] }}</span>
                                </span>
                                <span class="shrink-0 text-xs font-medium text-slate-600">{{ $row['status'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </x-admin.card>
        @endif
    </div>
@endif
