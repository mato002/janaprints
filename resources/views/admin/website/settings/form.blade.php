@php
    use App\Support\Navigation\WorkspaceEmbed;

    $resolver = app(\App\Services\Website\WebsiteSettingsService::class);
    $useTabs = ! empty($adminTabs) && $pageKey === 'footer-contact';
    $tabKeys = $useTabs ? array_keys($adminTabs) : [];
@endphp

<x-admin-layout
    :title="$title"
    :breadcrumbs="[
        ['label' => __('Administration'), 'url' => route('admin.workspaces.administration')],
        ['label' => __('Website Content'), 'url' => route('admin.workspaces.administration.section', 'website-content')],
        ['label' => $title],
    ]"
>
    @include('admin.website.partials.role-guidance', ['context' => 'settings'])

    <x-admin.page-header :title="$title" :description="$description">
        <x-slot:actions>
            <span class="text-xs text-slate-500">{{ __('Values fall back to config until saved in CMS.') }}</span>
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-4">{{ session('status') }}</x-admin.alert>
    @endif

    <x-admin.card>
        <form
            method="POST"
            action="{{ $updateRoute }}"
            class="p-6"
            data-website-settings-form
            @if ($useTabs) data-settings-tabs @endif
            data-turbo-frame="{{ WorkspaceEmbed::turboFrame() }}"
        >
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label class="erp-label" for="settings-search">{{ __('Search settings') }}</label>
                <input
                    id="settings-search"
                    type="search"
                    class="erp-input max-w-md"
                    placeholder="{{ __('Search by label or setting key…') }}"
                    data-settings-search
                    autocomplete="off"
                >
            </div>

            @if ($useTabs)
                <div class="mb-6 flex flex-wrap gap-2 border-b border-slate-200 pb-4" role="tablist" aria-label="{{ __('Settings sections') }}">
                    @foreach ($adminTabs as $tabKey => $tabLabel)
                        <button
                            type="button"
                            class="erp-filter-pill"
                            data-settings-tab-trigger="{{ $tabKey }}"
                            @if ($loop->first) data-settings-tab-active @endif
                        >
                            {{ $tabLabel }}
                        </button>
                    @endforeach
                </div>
            @endif

            @if ($useTabs)
                @foreach ($adminTabs as $tabKey => $tabLabel)
                    <section
                        class="space-y-5"
                        data-settings-tab-panel="{{ $tabKey }}"
                        @if (! $loop->first) hidden @endif
                    >
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ $tabLabel }}</h2>

                        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                            @foreach ($schema as $key => $meta)
                                @continue(($meta['admin_tab'] ?? '') !== $tabKey)
                                @include('admin.website.settings.field', [
                                    'key' => $key,
                                    'meta' => $meta,
                                    'record' => $records->get($key),
                                    'resolver' => $resolver,
                                ])
                            @endforeach
                        </div>
                    </section>
                @endforeach
            @else
                @foreach ($groups as $group)
                    <section class="mb-8">
                        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">
                            {{ $groupLabels[$group] ?? $group }}
                        </h2>

                        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                            @foreach ($schema as $key => $meta)
                                @continue($meta['group'] !== $group)
                                @include('admin.website.settings.field', [
                                    'key' => $key,
                                    'meta' => $meta,
                                    'record' => $records->get($key),
                                    'resolver' => $resolver,
                                ])
                            @endforeach
                        </div>
                    </section>
                @endforeach
            @endif

            @can('update', App\Models\WebsiteSetting::class)
                <div class="mt-8 flex flex-wrap gap-3 border-t border-slate-200 pt-5">
                    <button type="submit" class="erp-btn-primary">{{ __('Save Settings') }}</button>
                    <a
                        href="{{ WorkspaceEmbed::url(route('admin.workspaces.administration.section', 'website-content')) }}"
                        class="erp-btn-secondary"
                        data-turbo-frame="{{ WorkspaceEmbed::turboFrame() }}"
                    >{{ __('Back to Website Content') }}</a>
                </div>
            @endcan
        </form>
    </x-admin.card>
</x-admin-layout>
