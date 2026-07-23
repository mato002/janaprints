@php($files = $tabData['files'])

<x-admin.card class="mb-4" id="upload-file">
    <h3 class="mb-3 font-medium">{{ __('Upload file') }}</h3>
    @can('update', $customer)
        <form method="POST" action="{{ route('admin.crm.customers.files.store', $customer) }}" enctype="multipart/form-data" data-turbo-frame="erp-main" class="flex flex-wrap items-end gap-3">
            @csrf
            <input type="file" name="file" class="text-sm" required>
            <button type="submit" class="erp-btn-primary text-sm">{{ __('Upload') }}</button>
        </form>
    @else
        <p class="text-sm text-slate-500">{{ __('You do not have permission to upload files.') }}</p>
    @endcan
</x-admin.card>

<x-admin.data-table :searchable="false" :exportable="false" :filterable="false">
    <x-slot:head>
        <tr>
            <th>{{ __('File') }}</th>
            <th>{{ __('Uploaded by') }}</th>
            <th>{{ __('Size') }}</th>
            <th>{{ __('Date') }}</th>
            <th></th>
        </tr>
    </x-slot:head>
    <x-slot:body>
        @forelse ($files as $file)
            <tr>
                <td>{{ $file->original_name }}</td>
                <td>{{ $file->uploader?->name ?? '—' }}</td>
                <td class="tabular-nums">{{ number_format($file->size / 1024, 1) }} KB</td>
                <td>{{ $file->created_at?->format('Y-m-d H:i') }}</td>
                <td class="text-end">
                    @can('update', $customer)
                        <form method="POST" action="{{ route('admin.crm.customers.files.destroy', [$customer, $file]) }}" class="inline" data-turbo-confirm="{{ __('Remove this file?') }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-600 hover:text-red-700">{{ __('Remove') }}</button>
                        </form>
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="py-6 text-center text-slate-500">{{ __('No files uploaded yet.') }}</td></tr>
        @endforelse
    </x-slot:body>
    @if ($files->hasPages())
        <x-slot:footer>
            <x-admin.table-pagination :paginator="$files" />
        </x-slot:footer>
    @endif
</x-admin.data-table>
