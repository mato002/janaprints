@php
    use App\Support\Navigation\WorkspaceEmbed;

    $resolver = app(\App\Services\Documents\DocumentSettingsService::class);
    $companyId = $company->id;
    $embedded = WorkspaceEmbed::inWorkspaceContext();
@endphp

<x-admin-layout
    :title="$title"
    :breadcrumbs="$embedded ? [] : [
        ['label' => __('Administration'), 'url' => route('admin.workspaces.administration')],
        ['label' => __('Commercial Documents'), 'url' => route('admin.workspaces.administration.section', 'commercial-documents')],
        ['label' => $title],
    ]"
    :compact-workspace="$embedded"
>
    @if (! $embedded)
        <x-admin.page-header :title="$title" :description="$description">
            <x-slot:actions>
                <span class="text-xs text-slate-500">{{ __('Values fall back to config until saved here.') }}</span>
            </x-slot:actions>
        </x-admin.page-header>
    @else
        <div class="mb-4">
            <h2 class="text-lg font-semibold text-erp-primary">{{ $title }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
        </div>
    @endif

<x-admin.card>
        <form
            method="POST"
            action="{{ $updateRoute }}"
            class="p-6"
            data-document-settings-form
            data-settings-tabs
            data-turbo-frame="{{ WorkspaceEmbed::turboFrame() }}"
        >
            @csrf
            @method('PUT')

            <div class="mb-6 rounded-lg border border-erp-border bg-erp-page/60 px-4 py-3 text-sm text-slate-600">
                {{ __('Editing document branding for :company.', ['company' => $company->name]) }}
                @if ($embedded)
                    <span class="mt-1 block text-xs text-slate-500">{{ __('Values fall back to config until saved here.') }}</span>
                @endif
            </div>

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
                            @include('admin.documents.settings.field', [
                                'key' => $key,
                                'meta' => $meta,
                                'record' => $records->get($key),
                                'resolver' => $resolver,
                                'companyId' => $companyId,
                            ])
                        @endforeach
                    </div>
                </section>
            @endforeach

            @can('update', App\Models\DocumentSetting::class)
                <div class="mt-8 flex flex-wrap gap-3 border-t border-slate-200 pt-5">
                    <button type="submit" class="erp-btn-primary">{{ __('Save Settings') }}</button>
                    @unless ($embedded)
                        <a
                            href="{{ WorkspaceEmbed::url(route('admin.workspaces.administration.section', 'commercial-documents')) }}"
                            class="erp-btn-secondary"
                            data-turbo-frame="{{ WorkspaceEmbed::turboFrame() }}"
                        >{{ __('Back to Commercial Documents') }}</a>
                    @endunless
                </div>
            @endcan
        </form>
    </x-admin.card>
</x-admin-layout>
