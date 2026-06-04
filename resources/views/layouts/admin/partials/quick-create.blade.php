@if (! empty($quickCreate))
    <x-dropdown align="right" width="48">
        <x-slot name="trigger">
            <button type="button" class="erp-btn-primary py-2">
                <x-admin.icon name="plus" class="w-4 h-4" />
                <span class="hidden sm:inline">{{ __('Create') }}</span>
            </button>
        </x-slot>
        <x-slot name="content">
            @foreach ($quickCreate as $item)
                @if (! empty($item['coming_soon']))
                    <span class="block w-full px-4 py-2 text-start text-sm leading-5 text-slate-400 cursor-not-allowed">{{ $item['label'] }} <span class="text-xs">({{ __('Soon') }})</span></span>
                @elseif (! empty($item['route']))
                    <x-dropdown-link :href="route($item['route'])">{{ $item['label'] }}</x-dropdown-link>
                @endif
            @endforeach
        </x-slot>
    </x-dropdown>
@endif
