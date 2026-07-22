@if (! empty($quickCreate))
    <x-dropdown align="right" width="48">
        <x-slot name="trigger">
            <button type="button" class="erp-btn-primary erp-topbar__quick-create !min-h-9 !min-w-9 !p-2 sm:!min-h-0 sm:!min-w-0 sm:px-4 sm:py-2">
                <x-admin.icon name="plus" class="h-4 w-4 shrink-0" />
                <span class="hidden sm:inline">{{ __('Create') }}</span>
            </button>
        </x-slot>
        <x-slot name="content">
            @foreach ($quickCreate as $item)
                @if (! empty($item['coming_soon']))
                    <span class="block w-full px-4 py-2 text-start text-sm leading-5 text-slate-400 cursor-not-allowed">{{ $item['label'] }} <span class="text-xs">({{ __('Soon') }})</span></span>
                @elseif (! empty($item['route']))
                    @if (! empty($item['modal']))
                        <a
                            href="{{ route($item['route'], $item['route_params'] ?? []) }}"
                            data-erp-modal-open
                            class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out"
                        >{{ $item['label'] }}</a>
                    @else
                        <x-dropdown-link :href="route($item['route'], $item['route_params'] ?? [])">{{ $item['label'] }}</x-dropdown-link>
                    @endif
                @endif
            @endforeach
        </x-slot>
    </x-dropdown>
@endif
