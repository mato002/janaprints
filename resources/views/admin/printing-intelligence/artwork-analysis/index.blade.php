<x-admin-layout :title="__('Artwork Analysis')" :breadcrumbs="[
    ['label' => __('Printing Intelligence'), 'url' => route('admin.printing-intelligence.overview')],
    ['label' => __('Artwork Analysis')],
]">
    <x-admin.page-header
        :title="__('Artwork Analysis')"
        :description="__('Upload artwork, extract metadata (PI1), and analyse colour coverage (PI2). No cost estimation or AI.')"
    />

    @include('admin.printing-intelligence.partials.nav')

@include('admin.printing-intelligence.partials.environment-warnings', ['environment' => $environment ?? []])

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        @can('printing.artwork.analyze')
            <x-admin.card class="lg:col-span-1">
                <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Upload artwork') }}</h3>
                <form method="POST" action="{{ route('admin.printing-intelligence.artwork-analysis.upload') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600" for="file">{{ __('Artwork file') }}</label>
                        <input type="file" name="file" id="file" required
                               accept="{{ implode(',', array_map(fn ($ext) => '.'.$ext, $config['allowed_artwork_extensions'] ?? [])) }}"
                               class="block w-full text-sm text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-slate-900 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-white">
                        @error('file')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <p class="text-xs text-slate-500">
                        {{ __('Accepted: :types. Max :size MB.', [
                            'types' => strtoupper(implode(', ', $config['allowed_artwork_extensions'] ?? [])),
                            'size' => $config['max_artwork_upload_mb'] ?? 50,
                        ]) }}
                    </p>
                    <button type="submit" class="erp-btn-primary">{{ __('Analyze artwork') }}</button>
                </form>
            </x-admin.card>
        @endcan

        <x-admin.card @class(['lg:col-span-2' => auth()->user()?->can('printing.artwork.analyze'), 'lg:col-span-3' => ! auth()->user()?->can('printing.artwork.analyze')])>
            <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Recent analyses') }}</h3>

            @if ($analyses->isEmpty())
                <p class="text-sm text-slate-500">{{ __('No artwork analyses yet. Upload a file to begin.') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                                <th class="py-2 pr-3">{{ __('File') }}</th>
                                <th class="py-2 pr-3">{{ __('Metadata') }}</th>
                                <th class="py-2 pr-3">{{ __('Colour') }}</th>
                                <th class="py-2 pr-3">{{ __('Coverage') }}</th>
                                <th class="py-2 pr-3">{{ __('CMYK') }}</th>
                                <th class="py-2 pr-3">{{ __('White') }}</th>
                                <th class="py-2 pr-3">{{ __('Class') }}</th>
                                <th class="py-2 pr-3">{{ __('Warn') }}</th>
                                <th class="py-2">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($analyses as $item)
                                <tr>
                                    <td class="py-2 pr-3">
                                        <div class="font-medium text-slate-900">{{ $item->original_filename }}</div>
                                        <div class="text-xs uppercase text-slate-500">{{ $item->file_extension }}</div>
                                    </td>
                                    <td class="py-2 pr-3">
                                        @if ($item->analysis_status)
                                            <span @class(['inline-flex rounded-full px-2 py-0.5 text-xs font-medium', $item->analysis_status->badgeClass()])>
                                                {{ $item->analysis_status->label() }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-2 pr-3">
                                        @if ($item->colour_analysis_status)
                                            <span @class(['inline-flex rounded-full px-2 py-0.5 text-xs font-medium', $item->colour_analysis_status->badgeClass()])>
                                                {{ $item->colour_analysis_status->label() }}
                                            </span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="py-2 pr-3">{{ $item->cmyk_coverage_percent !== null ? number_format((float) $item->cmyk_coverage_percent, 1).'%' : '—' }}</td>
                                    <td class="py-2 pr-3 text-xs">
                                        @if ($item->cyan_coverage_percent !== null)
                                            C{{ number_format((float) $item->cyan_coverage_percent, 0) }}
                                            M{{ number_format((float) $item->magenta_coverage_percent, 0) }}
                                            Y{{ number_format((float) $item->yellow_coverage_percent, 0) }}
                                            K{{ number_format((float) $item->black_coverage_percent, 0) }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="py-2 pr-3">{{ $item->white_area_percent !== null ? number_format((float) $item->white_area_percent, 1).'%' : '—' }}</td>
                                    <td class="py-2 pr-3">
                                        @if ($item->coverage_class)
                                            <span @class(['inline-flex rounded-full px-2 py-0.5 text-xs font-medium', $item->coverage_class->badgeClass()])>
                                                {{ $item->coverage_class->label() }}
                                            </span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="py-2 pr-3">{{ count($item->colour_analysis_warnings ?? []) }}</td>
                                    <td class="py-2">
                                        <a href="{{ route('admin.printing-intelligence.artwork-analysis.show', $item) }}"
                                           class="text-xs font-medium text-slate-900 hover:underline">{{ __('View') }}</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-admin.card>
    </div>
</x-admin-layout>
