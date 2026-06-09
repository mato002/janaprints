@foreach ([
    'contracts' => __('Contracts'),
    'certificates' => __('Certificates'),
    'id_documents' => __('ID Documents'),
    'hr_files' => __('HR Files'),
] as $key => $label)
    <x-admin.card class="mb-4">
        <h3 class="mb-3 font-semibold text-erp-primary">{{ $label }}</h3>
        <x-admin.data-table>
            <x-slot name="head"><tr><th>{{ __('Title') }}</th><th>{{ __('Category') }}</th><th>{{ __('Expiry') }}</th></tr></x-slot>
            <x-slot name="body">
                @forelse ($documents[$key] as $doc)
                    <tr>
                        <td><a href="{{ route('admin.hr.documents.show', $doc) }}" class="text-erp-primary hover:underline">{{ $doc->title }}</a></td>
                        <td>{{ $doc->category?->label() ?? $doc->category }}</td>
                        <td>{{ $doc->expires_at?->format('M j, Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3"><x-admin.empty-state :title="__('None on file')" /></td></tr>
                @endforelse
            </x-slot>
        </x-admin.data-table>
    </x-admin.card>
@endforeach
