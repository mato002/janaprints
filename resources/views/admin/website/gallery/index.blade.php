<x-admin-layout
    :title="__('Gallery')"
    :breadcrumbs="[
        ['label' => __('Administration'), 'url' => route('admin.workspaces.administration')],
        ['label' => __('Website Content'), 'url' => route('admin.workspaces.administration.section', 'website-content')],
        ['label' => __('Gallery')],
    ]"
>
    @include('admin.website.partials.role-guidance', ['context' => 'gallery'])

    <x-admin.page-header
        :title="__('Website Gallery')"
        :description="__('Manage published portfolio items shown on the public storefront.')"
    >
        @can('create', App\Models\WebsiteGalleryItem::class)
            <x-slot:actions>
                <a href="{{ route('admin.website.gallery.create') }}" class="erp-btn-primary">
                    <x-admin.icon name="plus" class="mr-1.5 h-4 w-4" />
                    {{ __('Add Gallery Item') }}
                </a>
            </x-slot:actions>
        @endcan
    </x-admin.page-header>

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-4">{{ session('status') }}</x-admin.alert>
    @endif

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="route('admin.website.gallery.index')" :reset-url="route('admin.website.gallery.index')">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="erp-toolbar-input min-w-[12rem] flex-1" placeholder="{{ __('Search title, description, location…') }}" aria-label="{{ __('Search') }}" data-erp-auto-search>
            <select name="category" class="erp-toolbar-select" aria-label="{{ __('Category') }}">
                <option value="">{{ __('All categories') }}</option>
                @foreach ($categories as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['category'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <x-admin.status-pills
                :options="[['value' => '', 'label' => __('All statuses')], ['value' => '1', 'label' => __('Published')], ['value' => '0', 'label' => __('Hidden')]]"
                param="published"
                :current="$filters['published'] ?? ''"
            />
        </x-admin.index-toolbar>
    </x-admin.card>

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th class="w-14" title="{{ __('Lower numbers appear first. Edit an item to change sort order.') }}">{{ __('Order') }}</th>
                        <th class="w-24">{{ __('Preview') }}</th>
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('Category') }}</th>
                        <th>{{ __('Featured') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="erp-table-actions-col w-16">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        @php
                            $position = array_search($item->id, $orderedIds, true);
                            $canMoveUp = $position !== false && $position > 0;
                            $canMoveDown = $position !== false && $position < count($orderedIds) - 1;
                        @endphp
                        <tr class="align-middle">
                            <td class="whitespace-nowrap tabular-nums text-slate-500">
                                <div>{{ $item->sort_order }}</div>
                                @can('update', $item)
                                    <div class="mt-1 flex gap-1">
                                        @if ($canMoveUp)
                                            <form method="POST" action="{{ route('admin.website.gallery.move', $item) }}">
                                                @csrf
                                                <input type="hidden" name="direction" value="up">
                                                @foreach ($filters as $filterKey => $filterValue)
                                                    @if ($filterValue !== null && $filterValue !== '')
                                                        <input type="hidden" name="{{ $filterKey }}" value="{{ $filterValue }}">
                                                    @endif
                                                @endforeach
                                                <button type="submit" class="text-[10px] text-slate-500 hover:text-slate-800" title="{{ __('Move up') }}">↑</button>
                                            </form>
                                        @endif
                                        @if ($canMoveDown)
                                            <form method="POST" action="{{ route('admin.website.gallery.move', $item) }}">
                                                @csrf
                                                <input type="hidden" name="direction" value="down">
                                                @foreach ($filters as $filterKey => $filterValue)
                                                    @if ($filterValue !== null && $filterValue !== '')
                                                        <input type="hidden" name="{{ $filterKey }}" value="{{ $filterValue }}">
                                                    @endif
                                                @endforeach
                                                <button type="submit" class="text-[10px] text-slate-500 hover:text-slate-800" title="{{ __('Move down') }}">↓</button>
                                            </form>
                                        @endif
                                    </div>
                                @endcan
                            </td>
                            <td>
                                <a
                                    href="{{ route('admin.website.gallery.edit', $item) }}"
                                    class="group block overflow-hidden rounded-lg border border-slate-200 bg-slate-50"
                                    title="{{ __('Edit gallery item') }}"
                                >
                                    <img
                                        src="{{ $item->publicImageUrl() }}"
                                        alt="{{ $item->alt_text }}"
                                        class="h-14 w-20 object-cover transition-transform duration-200 group-hover:scale-105"
                                        loading="lazy"
                                        onerror="this.onerror=null;this.src='{{ asset('images/storefront/gallery/brochures.jpg') }}';"
                                    >
                                </a>
                            </td>
                            <td>
                                <div class="font-medium text-slate-900">{{ $item->title }}</div>
                                @if ($item->location)
                                    <div class="mt-0.5 text-xs text-slate-500">{{ $item->location }}</div>
                                @endif
                                @if ($item->quantity_label)
                                    <div class="mt-0.5 text-xs text-slate-400">{{ $item->quantity_label }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700">
                                    {{ $item->categoryLabel() }}
                                </span>
                            </td>
                            <td>
                                @if ($item->is_featured)
                                    <x-admin.status-badge variant="success">{{ __('Featured') }}</x-admin.status-badge>
                                @else
                                    <span class="text-xs text-slate-400">{{ __('Standard') }}</span>
                                @endif
                            </td>
                            <td>
                                <x-admin.status-badge :variant="$item->is_published ? 'success' : 'neutral'">
                                    {{ $item->is_published ? __('Published') : __('Hidden') }}
                                </x-admin.status-badge>
                            </td>
                            <td class="erp-table-actions-col">
                                <x-admin.table-row-actions>
                                    @can('update', $item)
                                        <x-admin.table-row-action :href="route('admin.website.gallery.edit', $item)">
                                            <x-admin.icon name="cog" class="h-4 w-4" />
                                            {{ __('Edit') }}
                                        </x-admin.table-row-action>
                                    @endcan
                                    @if ($item->is_published)
                                        <x-admin.table-row-action :href="route('storefront.gallery')" target="_blank">
                                            <x-admin.icon name="external-link" class="h-4 w-4" />
                                            {{ __('View on site') }}
                                        </x-admin.table-row-action>
                                    @endif
                                    @can('delete', $item)
                                        <x-admin.table-row-action
                                            :action="route('admin.website.gallery.destroy', $item)"
                                            method="DELETE"
                                            variant="danger"
                                            :confirm="__('Delete this gallery item? This cannot be undone.')"
                                        >
                                            <x-admin.icon name="x-circle" class="h-4 w-4" />
                                            {{ __('Delete') }}
                                        </x-admin.table-row-action>
                                    @endcan
                                </x-admin.table-row-actions>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12">
                                <x-admin.empty-state
                                    icon="photograph"
                                    :title="__('No gallery items yet')"
                                    :description="__('Add your first project to populate the public gallery and homepage portfolio.')"
                                >
                                    @can('create', App\Models\WebsiteGalleryItem::class)
                                        <a href="{{ route('admin.website.gallery.create') }}" class="erp-btn-primary mt-4">{{ __('Add Gallery Item') }}</a>
                                    @endcan
                                </x-admin.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($items->hasPages())
            <div class="border-t border-slate-200 p-4">
                {{ $items->links() }}
            </div>
        @endif
    </x-admin.card>
</x-admin-layout>
