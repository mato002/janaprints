@php
    use App\Support\Navigation\WorkspaceEmbed;

    $tabFilters = collect(request()->query())->except(['tab'])->all();
    $hubUrl = WorkspaceEmbed::url($hubUrl);
    $turboFrame = WorkspaceEmbed::turboFrame();
@endphp

<x-admin.card class="mb-4">
    <nav class="flex flex-wrap gap-2" aria-label="{{ __('Intelligence sections') }}">
        @foreach ($tabs as $key => $label)
            <a
                href="{{ $hubUrl }}?{{ http_build_query(array_merge($tabFilters, ['tab' => $key])) }}"
                data-turbo-frame="{{ $turboFrame }}"
                @class([
                    'rounded-md px-3 py-1.5 text-xs font-medium',
                    'bg-slate-900 text-white' => $activeTab === $key,
                    'bg-slate-100 text-slate-700 hover:bg-slate-200' => $activeTab !== $key,
                ])
            >
                {{ $label }}
            </a>
        @endforeach
    </nav>
</x-admin.card>
