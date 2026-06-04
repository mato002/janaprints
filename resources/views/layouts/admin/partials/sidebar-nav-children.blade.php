@php
    use App\Providers\AppServiceProvider as Nav;
    $depth = $depth ?? 0;
    $padding = $depth === 0 ? 'pl-3' : 'pl-6';
@endphp

@foreach ($children as $child)
    @if (isset($child['children']))
        @php
            $subId = 'nav-'.Str::slug(($parentLabel ?? 'group').'-'.$child['label']);
            $subOpen = Nav::navGroupIsOpen($child);
            $subRoutes = implode(',', Nav::collectNavRoutes($child));
        @endphp
        <div
            x-data="navGroup('{{ $subId }}', {{ $subOpen ? 'true' : 'false' }})"
            data-nav-subgroup
            data-nav-group-routes="{{ $subRoutes }}"
            data-nav-group-open="{{ $subOpen ? '1' : '0' }}"
            class="space-y-0.5"
        >
            <button
                type="button"
                @click="toggle()"
                class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500 transition-colors hover:bg-white/5 hover:text-slate-300"
            >
                @if (! empty($child['icon']))
                    <x-admin.icon :name="$child['icon']" class="h-3.5 w-3.5 shrink-0 opacity-70" />
                @endif
                <span class="flex-1 truncate text-left">{{ $child['label'] }}</span>
                <x-admin.icon name="chevron-down" class="h-3 w-3 shrink-0 transition-transform duration-200" ::class="open ? 'rotate-180' : ''" />
            </button>
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-cloak
                class="mt-0.5 space-y-0.5 {{ $padding }}"
            >
                @include('layouts.admin.partials.sidebar-nav-children', [
                    'children' => $child['children'],
                    'depth' => $depth + 1,
                    'parentLabel' => $child['label'],
                ])
            </div>
        </div>
    @else
        @include('layouts.admin.partials.sidebar-link', [
            'child' => $child,
            'collapsed' => $collapsed ?? false,
            'indent' => $depth > 0,
        ])
    @endif
@endforeach
