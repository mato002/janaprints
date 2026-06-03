@props(['groupedModules', 'uncatalogued' => [], 'editable' => false])

@foreach ($groupedModules as $module)
    <x-admin.card class="mb-6">
        <h3 class="text-base font-semibold text-erp-primary">{{ __($module['module_label']) }}</h3>

        <div class="mt-4 space-y-6">
            @foreach ($module['entities'] as $entity)
                <div>
                    <h4 class="text-sm font-medium text-slate-700">{{ __($entity['entity_label']) }}</h4>
                    <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                        @foreach ($entity['items'] as $item)
                            @if ($editable)
                                <label class="inline-flex items-center gap-2 rounded-lg border border-erp-border bg-erp-page/40 px-3 py-2 text-sm">
                                    <input
                                        type="checkbox"
                                        name="permissions[]"
                                        value="{{ $item['permission'] }}"
                                        class="rounded border-erp-border text-erp-accent focus:ring-erp-accent"
                                        @checked($item['checked'])
                                    >
                                    <span>{{ __($item['label']) }}</span>
                                </label>
                            @else
                                <div @class([
                                    'rounded-lg border px-3 py-2 text-sm',
                                    'border-emerald-200 bg-emerald-50 text-emerald-800' => $item['checked'],
                                    'border-slate-200 bg-slate-50 text-slate-400' => ! $item['checked'],
                                ])>
                                    {{ __($item['label']) }}
                                </div>
                            @endif
                        @endforeach
                    </div>

                    @if (! empty($entity['extra']))
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($entity['extra'] as $extra)
                                @if ($editable)
                                    <label class="inline-flex items-center gap-2 rounded-lg border border-erp-border bg-white px-3 py-2 text-sm">
                                        <input type="checkbox" name="permissions[]" value="{{ $extra['permission'] }}" class="rounded border-erp-border text-erp-accent" @checked($extra['checked'])>
                                        <span>{{ __($extra['label']) }}</span>
                                    </label>
                                @else
                                    <span @class([
                                        'inline-flex rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset',
                                        'bg-emerald-50 text-emerald-700 ring-emerald-600/20' => $extra['checked'],
                                        'bg-slate-100 text-slate-500 ring-slate-500/10' => ! $extra['checked'],
                                    ])>
                                        {{ __($extra['label']) }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </x-admin.card>
@endforeach

@if ($editable && $uncatalogued !== [])
    <div class="rounded-lg border border-dashed border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        <p class="font-medium">{{ __('Additional system permissions preserved') }}</p>
        <p class="mt-1 text-xs">{{ __('These remain assigned and are not shown in the business module groups above.') }}</p>
        <ul class="mt-2 space-y-1 font-mono text-xs">
            @foreach ($uncatalogued as $permission)
                <li>
                    <input type="hidden" name="permissions[]" value="{{ $permission }}">
                    {{ $permission }}
                </li>
            @endforeach
        </ul>
    </div>
@endif
