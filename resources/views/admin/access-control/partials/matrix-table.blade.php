{{-- Included with: sections, matrixState, editable, uncatalogued --}}
@php
    $columnCount = count(config('permission_catalog.columns', []));
    $editable = $editable ?? false;
    $uncatalogued = $uncatalogued ?? [];
@endphp

@foreach ($sections as $section)
    <x-admin.card class="mb-6 !p-0 overflow-hidden">
        <div class="border-b border-erp-border bg-erp-page/30 px-5 py-3 sm:px-6">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __($section['module_label']) }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="erp-table erp-table--grid min-w-full">
                <thead>
                    <tr>
                        <th class="w-[14rem] pl-5 sm:pl-6">{{ __('Capability') }}</th>
                        @foreach (config('permission_catalog.columns', []) as $column => $meta)
                            <th class="w-24 text-center">{{ __($meta['label']) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-erp-border bg-white">
                    @foreach ($section['rows'] as $row)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 pl-5 font-medium text-slate-700 sm:pl-6">{{ __($row['entity_label']) }}</td>
                            @foreach (config('permission_catalog.columns', []) as $column => $meta)
                                <td class="py-3 text-center">
                                    @if (! empty($row['cells'][$column]['permission']))
                                        @if ($editable)
                                            <label class="inline-flex cursor-pointer items-center justify-center">
                                                <input
                                                    type="checkbox"
                                                    name="permissions[]"
                                                    value="{{ $row['cells'][$column]['permission'] }}"
                                                    class="h-4 w-4 rounded border-erp-border text-erp-accent focus:ring-erp-accent"
                                                    @checked($matrixState[$row['cells'][$column]['permission']] ?? false)
                                                >
                                                <span class="sr-only">{{ __($row['entity_label']) }} — {{ __($meta['label']) }}</span>
                                            </label>
                                        @elseif ($matrixState[$row['cells'][$column]['permission']] ?? false)
                                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-emerald-50 text-sm font-semibold text-emerald-700" title="{{ __('Granted') }}">✓</span>
                                        @else
                                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-slate-100 text-sm text-slate-400" title="{{ __('Not granted') }}">✗</span>
                                        @endif
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        @if (! empty($row['extra']))
                            <tr class="bg-erp-page/40">
                                <td class="py-2.5 pl-5 text-xs font-medium uppercase tracking-wide text-slate-500 sm:pl-6">{{ __('Additional actions') }}</td>
                                <td colspan="{{ $columnCount }}" class="py-2.5 pr-5 sm:pr-6">
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($row['extra'] as $extra)
                                            @if ($editable)
                                                <label class="inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 text-sm {{ ($matrixState[$extra['permission']] ?? false) ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-erp-border bg-white text-slate-700' }}">
                                                    <input
                                                        type="checkbox"
                                                        name="permissions[]"
                                                        value="{{ $extra['permission'] }}"
                                                        class="rounded border-erp-border text-erp-accent focus:ring-erp-accent"
                                                        @checked($matrixState[$extra['permission']] ?? false)
                                                    >
                                                    <span>{{ __($extra['label']) }}</span>
                                                </label>
                                            @else
                                                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ ($matrixState[$extra['permission']] ?? false) ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-slate-100 text-slate-500 ring-slate-500/10' }}">
                                                    {{ ($matrixState[$extra['permission']] ?? false) ? '✓' : '✗' }} {{ __($extra['label']) }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-admin.card>
@endforeach

@if ($editable && $uncatalogued !== [])
    <div class="rounded-xl border border-dashed border-amber-300 bg-amber-50 px-5 py-4 text-sm text-amber-900">
        <p class="font-medium">{{ __('Additional system permissions preserved') }}</p>
        <p class="mt-1 text-xs text-amber-800">{{ __('These remain assigned and are not mapped in the matrix above.') }}</p>
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach ($uncatalogued as $permission)
                <span class="inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-white px-2 py-1 font-mono text-xs">
                    <input type="hidden" name="permissions[]" value="{{ $permission }}">
                    {{ $permission }}
                </span>
            @endforeach
        </div>
    </div>
@endif
