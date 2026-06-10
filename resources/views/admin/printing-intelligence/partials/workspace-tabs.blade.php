@props(['tabs' => [], 'activeTab' => 'overview', 'filters' => []])

<x-admin.card class="mb-4">
    <nav class="flex flex-wrap gap-2">
        @foreach ($tabs as $key => $label)
            <a href="{{ request()->url() }}?{{ http_build_query(array_merge($filters ?? [], ['tab' => $key])) }}"
               @class([
                   'rounded-md px-3 py-1.5 text-xs font-medium',
                   'bg-slate-900 text-white' => $activeTab === $key,
                   'bg-slate-100 text-slate-700 hover:bg-slate-200' => $activeTab !== $key,
               ])>
                {{ $label }}
            </a>
        @endforeach
    </nav>
</x-admin.card>
