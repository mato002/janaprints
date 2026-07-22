@if (count($workQueue['items'] ?? []) > 0)
    <x-admin.card class="mb-4">
        <div class="border-b border-erp-border px-4 py-3">
            <h2 class="text-sm font-semibold text-slate-900">{{ __("Today's store queue") }}</h2>
            <p class="mt-0.5 text-xs text-slate-500">{{ __('Receipts, issues, and counts waiting for you.') }}</p>
        </div>
        <ul class="divide-y divide-slate-100">
            @foreach ($workQueue['items'] as $item)
                @php
                    $toneClasses = match ($item['tone'] ?? 'slate') {
                        'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                        'rose' => 'border-rose-200 bg-rose-50 text-rose-800',
                        'blue' => 'border-blue-200 bg-blue-50 text-blue-800',
                        default => 'border-slate-200 bg-slate-50 text-slate-800',
                    };
                    $kindLabel = match ($item['kind'] ?? '') {
                        'receipt' => __('Receive'),
                        'issue' => __('Issue'),
                        'count' => __('Count'),
                        default => __('Task'),
                    };
                @endphp
                <li class="px-4 py-3">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <div class="mb-1 flex flex-wrap items-center gap-2">
                                <span class="inline-flex rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide {{ $toneClasses }}">{{ $kindLabel }}</span>
                                <span class="font-mono text-xs font-medium text-slate-700">{{ $item['label'] }}</span>
                                <span class="text-xs font-medium {{ ($item['status'] ?? '') === __('Due now') ? 'text-amber-700' : 'text-slate-500' }}">{{ $item['status'] }}</span>
                            </div>
                            <p class="font-medium text-slate-900">{{ $item['title'] }}</p>
                            @if (! empty($item['meta']))
                                <p class="text-xs text-slate-500">{{ $item['meta'] }}</p>
                            @endif
                        </div>
                        <div class="flex shrink-0 flex-wrap items-center gap-2">
                            <a
                                href="{{ $item['url'] }}"
                                class="erp-btn-secondary text-xs"
                                @if ($item['modal'] ?? false) data-erp-modal-open @else data-turbo-frame="_top" @endif
                            >{{ __('Review') }}</a>
                            @if (($item['can_post'] ?? false) && ! empty($item['post_url']))
                                <form method="POST" action="{{ $item['post_url'] }}" class="inline" data-erp-desk-form>
                                    @csrf
                                    <input type="hidden" name="from" value="store-desk">
                                    <button type="submit" class="erp-btn-primary text-xs">{{ __('Post to stock') }}</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </x-admin.card>
@endif
